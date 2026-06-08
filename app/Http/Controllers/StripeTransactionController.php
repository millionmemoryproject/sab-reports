<?php

namespace App\Http\Controllers;

use App\Models\StripeTransaction;
use Illuminate\Http\Request;

class StripeTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = StripeTransaction::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('stripe_id', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('reporting_category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $totalsQuery = clone $query;

        $totals = [
            'gross' => $totalsQuery->sum('gross'),
            'fee' => (clone $query)->sum('fee'),
            'net' => (clone $query)->sum('net'),
            'count' => (clone $query)->count(),
        ];

        $transactions = $query
            ->orderByDesc('transaction_date')
            ->paginate(50)
            ->withQueryString();

        $types = StripeTransaction::query()
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('stripe-transactions.index', compact(
            'transactions',
            'totals',
            'types'
        ));
    }
}