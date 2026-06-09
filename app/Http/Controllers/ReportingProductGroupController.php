<?php

namespace App\Http\Controllers;

use App\Models\ReportingProductGroup;
use App\Models\ReportingProductGroupRule;
use App\Models\WooCommerceOrderItem;
use Illuminate\Http\Request;

class ReportingProductGroupController extends Controller
{
    public function index()
    {
        $groups = ReportingProductGroup::withCount('rules')
            ->orderBy('name')
            ->get();

        $ungroupedProducts = WooCommerceOrderItem::query()
            ->selectRaw('name, sku, product_id, variation_id, SUM(quantity) as quantity_sold, SUM(total) as revenue')
            ->groupBy('name', 'sku', 'product_id', 'variation_id')
            ->orderByDesc('revenue')
            ->limit(50)
            ->get();

        return view('reporting-product-groups.index', compact('groups', 'ungroupedProducts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        ReportingProductGroup::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'active' => true,
        ]);

        return redirect()
            ->route('reports.product-groups.index')
            ->with('status', 'Product group created.');
    }

    public function show(ReportingProductGroup $productGroup)
    {
        $productGroup->load('rules');

        $previewItems = $this->matchedItemsForGroup($productGroup)
            ->selectRaw('name, sku, product_id, variation_id, SUM(quantity) as quantity_sold, SUM(total) as revenue')
            ->groupBy('name', 'sku', 'product_id', 'variation_id')
            ->orderByDesc('revenue')
            ->limit(100)
            ->get();

        return view('reporting-product-groups.show', compact('productGroup', 'previewItems'));
    }

    public function update(Request $request, ReportingProductGroup $productGroup)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);

        $productGroup->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'active' => $request->boolean('active'),
        ]);

        return redirect()
            ->route('reports.product-groups.show', $productGroup)
            ->with('status', 'Product group updated.');
    }

    public function destroy(ReportingProductGroup $productGroup)
    {
        $productGroup->delete();

        return redirect()
            ->route('reports.product-groups.index')
            ->with('status', 'Product group deleted.');
    }

    public function storeRule(Request $request, ReportingProductGroup $productGroup)
    {
        $validated = $request->validate([
            'match_field' => ['required', 'in:name,sku,product_id,variation_id'],
            'match_operator' => ['required', 'in:contains,equals,starts_with,ends_with'],
            'match_value' => ['required', 'string', 'max:255'],
            'level_name' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'integer'],
        ]);

        $productGroup->rules()->create([
            'match_field' => $validated['match_field'],
            'match_operator' => $validated['match_operator'],
            'match_value' => $validated['match_value'],
            'level_name' => $validated['level_name'] ?? null,
            'priority' => $validated['priority'] ?? 0,
            'active' => true,
        ]);

        return redirect()
            ->route('reports.product-groups.show', $productGroup)
            ->with('status', 'Rule added.');
    }

    public function updateRule(Request $request, ReportingProductGroup $productGroup, ReportingProductGroupRule $rule)
    {
        if ($rule->reporting_product_group_id !== $productGroup->id) {
            abort(404);
        }

        $validated = $request->validate([
            'match_field' => ['required', 'in:name,sku,product_id,variation_id'],
            'match_operator' => ['required', 'in:contains,equals,starts_with,ends_with'],
            'match_value' => ['required', 'string', 'max:255'],
            'level_name' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'integer'],
        ]);

        $rule->update([
            'match_field' => $validated['match_field'],
            'match_operator' => $validated['match_operator'],
            'match_value' => $validated['match_value'],
            'level_name' => $validated['level_name'] ?? null,
            'priority' => $validated['priority'] ?? 0,
            'active' => $request->boolean('active'),
        ]);

        return redirect()
            ->route('reports.product-groups.show', $productGroup)
            ->with('status', 'Rule updated.');
    }

    public function destroyRule(ReportingProductGroup $productGroup, ReportingProductGroupRule $rule)
    {
        if ($rule->reporting_product_group_id !== $productGroup->id) {
            abort(404);
        }

        $rule->delete();

        return redirect()
            ->route('reports.product-groups.show', $productGroup)
            ->with('status', 'Rule deleted.');
    }

    private function matchedItemsForGroup(ReportingProductGroup $group)
    {
        $rules = $group->rules()
            ->where('active', true)
            ->get();

        $query = WooCommerceOrderItem::query();

        if ($rules->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        $query->where(function ($outer) use ($rules) {
            foreach ($rules as $rule) {
                $outer->orWhere(function ($q) use ($rule) {
                    $field = $rule->match_field;
                    $value = $rule->match_value;

                    match ($rule->match_operator) {
                        'equals' => $q->where($field, $value),
                        'starts_with' => $q->where($field, 'like', $value . '%'),
                        'ends_with' => $q->where($field, 'like', '%' . $value),
                        default => $q->where($field, 'like', '%' . $value . '%'),
                    };
                });
            }
        });

        return $query;
    }

    public function report(ReportingProductGroup $productGroup)
{
    $productGroup->load(['rules' => function ($query) {
        $query->where('active', true)
            ->orderBy('priority')
            ->orderBy('id');
    }]);

    $items = $this->matchedItemsForGroup($productGroup)
        ->with(['order.stripeTransaction'])
        ->get();

    $orders = $items
        ->pluck('order')
        ->filter()
        ->unique('id')
        ->sortByDesc('date_created')
        ->values();

    $levels = $items
        ->groupBy(function ($item) use ($productGroup) {
            return $this->levelForItem($item, $productGroup) ?: $item->name;
        })
        ->map(function ($items, $levelName) {
            return [
                'name' => $levelName,
                'quantity' => $items->sum('quantity'),
                'revenue' => $items->sum('total'),
                'products' => $items->pluck('name')->unique()->values(),
            ];
        })
        ->sortByDesc('revenue')
        ->values();

    $rawProducts = $items
        ->groupBy(fn ($item) => $item->name . '||' . $item->sku)
        ->map(function ($group) use ($productGroup) {
            $first = $group->first();

            return (object) [
                'name' => $first->name,
                'sku' => $first->sku,
                'level' => $this->levelForItem($first, $productGroup),
                'quantity' => $group->sum('quantity'),
                'revenue' => $group->sum('total'),
            ];
        })
        ->sortByDesc('revenue')
        ->values();

    $customers = $items
        ->filter(fn ($item) => $item->order && $item->order->billing_email)
        ->groupBy(fn ($item) => $item->order->billing_email)
        ->map(function ($group) {
            $order = $group->first()->order;

            return (object) [
                'name' => trim($order->billing_first_name . ' ' . $order->billing_last_name),
                'email' => $order->billing_email,
                'orders' => $group->pluck('order.id')->unique()->count(),
                'quantity' => $group->sum('quantity'),
                'revenue' => $group->sum('total'),
            ];
        })
        ->sortByDesc('revenue')
        ->values();

    $totals = [
        'orders' => $orders->count(),
        'quantity' => $items->sum('quantity'),
        'revenue' => $items->sum('total'),
        'customers' => $customers->count(),
        'stripe_fees' => $orders->sum(fn ($order) => optional($order->stripeTransaction)->fee ?? 0),
        'stripe_net' => $orders->sum(fn ($order) => optional($order->stripeTransaction)->net ?? 0),
    ];

    return view('reporting-product-groups.report', compact(
        'productGroup',
        'items',
        'orders',
        'levels',
        'rawProducts',
        'customers',
        'totals'
    ));
}

public function exportOrders(ReportingProductGroup $productGroup)
{
    $orders = $this->matchedItemsForGroup($productGroup)
        ->with(['order.stripeTransaction'])
        ->get()
        ->pluck('order')
        ->filter()
        ->unique('id')
        ->sortByDesc('date_created')
        ->values();

    $slug = \Illuminate\Support\Str::slug($productGroup->name) ?: 'product-group';
    $filename = $slug . '-orders-' . now()->format('Y-m-d') . '.csv';

    return response()->streamDownload(function () use ($orders) {
        $out = fopen('php://output', 'w');

        fputcsv($out, [
            'Order', 'Date', 'Customer', 'Email', 'City', 'State',
            'Order Total', 'Stripe Fee', 'Stripe Net', 'Match',
        ]);

        foreach ($orders as $order) {
            fputcsv($out, [
                $order->order_number,
                optional($order->date_created)->format('Y-m-d'),
                trim($order->billing_first_name . ' ' . $order->billing_last_name),
                $order->billing_email,
                $order->billing_city,
                $order->billing_state,
                $order->total,
                optional($order->stripeTransaction)->fee,
                optional($order->stripeTransaction)->net,
                $order->match_status,
            ]);
        }

        fclose($out);
    }, $filename, [
        'Content-Type' => 'text/csv',
    ]);
}

private function levelForItem(WooCommerceOrderItem $item, ReportingProductGroup $group): ?string
{
    foreach ($group->rules as $rule) {
        if (! $rule->level_name) {
            continue;
        }

        $field = $rule->match_field;
        $itemValue = (string) ($item->{$field} ?? '');
        $matchValue = (string) $rule->match_value;

        $matches = match ($rule->match_operator) {
            'equals' => strcasecmp($itemValue, $matchValue) === 0,
            'starts_with' => str_starts_with(strtolower($itemValue), strtolower($matchValue)),
            'ends_with' => str_ends_with(strtolower($itemValue), strtolower($matchValue)),
            default => str_contains(strtolower($itemValue), strtolower($matchValue)),
        };

        if ($matches) {
            return $rule->level_name;
        }
    }

    return null;
}
}