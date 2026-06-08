<?php

namespace App\Http\Controllers;

use App\Models\ReportingProductGroup;
use App\Models\WooCommerceOrder;
use App\Models\WooCommerceOrderItem;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $query = WooCommerceOrder::with(['items', 'stripeTransaction']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('billing_email', 'like', "%{$search}%")
                    ->orWhere('billing_first_name', 'like', "%{$search}%")
                    ->orWhere('billing_last_name', 'like', "%{$search}%")
                    ->orWhere('billing_city', 'like', "%{$search}%")
                    ->orWhere('billing_state', 'like', "%{$search}%")
                    ->orWhereHas('items', function ($itemQuery) use ($search) {
                        $itemQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('match_status')) {
            $query->where('match_status', $request->match_status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date_created', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date_created', '<=', $request->end_date);
        }

        $filteredOrders = (clone $query)->get();

        $ordersCount = $filteredOrders->count();
        $wooTotal = $filteredOrders->sum('total');
        $stripeFees = $filteredOrders->sum(fn ($order) => optional($order->stripeTransaction)->fee ?? 0);
        $stripeNet = $filteredOrders->sum(fn ($order) => optional($order->stripeTransaction)->net ?? 0);
        $matchedCount = $filteredOrders->where('match_status', 'matched')->count();

        $totals = [
            'orders' => $ordersCount,
            'woo_total' => $wooTotal,
            'stripe_fees' => $stripeFees,
            'stripe_net' => $stripeNet,
            'average_order_value' => $ordersCount ? $wooTotal / $ordersCount : 0,
            'customers' => $filteredOrders->pluck('billing_email')->filter()->unique()->count(),
            'match_rate' => $ordersCount ? ($matchedCount / $ordersCount) * 100 : 0,
            'matched' => $matchedCount,
            'unmatched' => $filteredOrders->where('match_status', 'unmatched')->count(),
        ];

        $orderIds = $filteredOrders->pluck('id');

        $products = $this->groupedProducts($orderIds);

        $states = $filteredOrders
            ->filter(fn ($order) => $order->billing_state)
            ->groupBy('billing_state')
            ->map(function ($orders, $state) {
                return [
                    'state' => $state,
                    'orders' => $orders->count(),
                    'revenue' => $orders->sum('total'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(8)
            ->values();

        $monthlyRevenue = $filteredOrders
            ->filter(fn ($order) => $order->date_created)
            ->groupBy(fn ($order) => $order->date_created->format('Y-m'))
            ->map(function ($orders, $month) {
                return [
                    'month' => $month,
                    'revenue' => round($orders->sum('total'), 2),
                    'net' => round($orders->sum(fn ($order) => optional($order->stripeTransaction)->net ?? 0), 2),
                ];
            })
            ->sortBy('month')
            ->values();

        $orders = $query
            ->orderByDesc('date_created')
            ->paginate(50)
            ->withQueryString();

        return view('sales-reports.index', compact(
            'orders',
            'totals',
            'products',
            'states',
            'monthlyRevenue'
        ));
    }

    private function groupedProducts($orderIds)
    {
        $items = WooCommerceOrderItem::query()
            ->whereIn('woo_commerce_order_id', $orderIds)
            ->get();

        $groups = ReportingProductGroup::with(['rules' => function ($query) {
                $query->where('active', true)
                    ->orderBy('priority')
                    ->orderBy('id');
            }])
            ->where('active', true)
            ->get();

        return $items
            ->map(function ($item) use ($groups) {
                $matchedGroup = $this->matchingGroupForItem($item, $groups);

                return [
                    'group_id' => $matchedGroup?->id,
                    'reporting_name' => $matchedGroup?->name ?? $item->name,
                    'raw_name' => $item->name,
                    'quantity' => (int) $item->quantity,
                    'revenue' => (float) $item->total,
                ];
            })
            ->groupBy(function ($item) {
                return $item['group_id']
                    ? 'group_' . $item['group_id']
                    : 'raw_' . $item['reporting_name'];
            })
            ->map(function ($items) {
                $first = $items->first();

                return (object) [
                    'group_id' => $first['group_id'],
                    'name' => $first['reporting_name'],
                    'quantity_sold' => $items->sum('quantity'),
                    'revenue' => $items->sum('revenue'),
                    'raw_product_count' => $items->pluck('raw_name')->unique()->count(),
                ];
            })
            ->sortByDesc('revenue')
            ->take(8)
            ->values();
    }

    private function matchingGroupForItem(WooCommerceOrderItem $item, $groups)
    {
        foreach ($groups as $group) {
            foreach ($group->rules as $rule) {
                if ($this->itemMatchesRule($item, $rule)) {
                    return $group;
                }
            }
        }

        return null;
    }

    private function itemMatchesRule(WooCommerceOrderItem $item, $rule): bool
    {
        $field = $rule->match_field;
        $itemValue = (string) ($item->{$field} ?? '');
        $matchValue = (string) $rule->match_value;

        return match ($rule->match_operator) {
            'equals' => strcasecmp($itemValue, $matchValue) === 0,
            'starts_with' => str_starts_with(strtolower($itemValue), strtolower($matchValue)),
            'ends_with' => str_ends_with(strtolower($itemValue), strtolower($matchValue)),
            default => str_contains(strtolower($itemValue), strtolower($matchValue)),
        };
    }
}