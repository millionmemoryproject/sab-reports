@extends('layouts.reporting', ['title' => 'Sales Intelligence Dashboard'])

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
    <section class="hero">
        <div class="hero-label">Sound Archive Books Reporting</div>
        <h1>Sales & Revenue Intelligence</h1>
        <p>Combined WooCommerce order data and Stripe payment data, reconciled into one reporting view.</p>
    </section>

    <section class="kpis">
        <div class="card kpi">
            <span>Gross Revenue</span>
            <strong>${{ number_format($totals['woo_total'], 2) }}</strong>
            <small>WooCommerce order total</small>
        </div>

        <div class="card kpi">
            <span>Net Revenue</span>
            <strong>${{ number_format($totals['stripe_net'], 2) }}</strong>
            <small>After Stripe fees</small>
        </div>

        <div class="card kpi">
            <span>Stripe Fees</span>
            <strong>${{ number_format($totals['stripe_fees'], 2) }}</strong>
            <small>{{ number_format($totals['match_rate'], 1) }}% reconciliation rate</small>
        </div>

        <div class="card kpi">
            <span>Orders</span>
            <strong>{{ number_format($totals['orders']) }}</strong>
            <small>${{ number_format($totals['average_order_value'], 2) }} average order value</small>
        </div>

        <div class="card kpi">
            <span>Customers</span>
            <strong>{{ number_format($totals['customers']) }}</strong>
            <small>Unique billing emails</small>
        </div>

        <div class="card kpi">
            <span>Matched</span>
            <strong>{{ number_format($totals['matched']) }}</strong>
            <small>Linked to Stripe transactions</small>
        </div>

        <div class="card kpi">
            <span>Unmatched</span>
            <strong>{{ number_format($totals['unmatched']) }}</strong>
            <small>Needs review if material</small>
        </div>

        <div class="card kpi">
            <span>Fees as %</span>
            <strong>
                {{ $totals['woo_total'] > 0 ? number_format(($totals['stripe_fees'] / $totals['woo_total']) * 100, 2) : '0.00' }}%
            </strong>
            <small>Stripe fee burden</small>
        </div>
    </section>

    <section class="card">
        <form method="GET" action="{{ route('reports.sales.index') }}" class="filters">
            <div>
                <label>Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Order, customer, email, city, state, product, SKU">
            </div>

            <div>
                <label>Match</label>
                <select name="match_status">
                    <option value="">All</option>
                    <option value="matched" @selected(request('match_status') === 'matched')>Matched</option>
                    <option value="unmatched" @selected(request('match_status') === 'unmatched')>Unmatched</option>
                </select>
            </div>

            <div>
                <label>Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}">
            </div>

            <div>
                <label>End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}">
            </div>

            <div>
                <button type="submit" class="button">Filter</button>
                <a href="{{ route('reports.sales.index') }}" class="button secondary">Reset</a>
            </div>
        </form>
    </section>

    <section class="grid-main">
        <div class="card">
            <h2 class="section-title">Revenue by Month</h2>
            <canvas id="revenueChart" height="115"></canvas>
        </div>

        <div class="card">
            <h2 class="section-title">Top States</h2>

            <div class="leaderboard">
                @forelse($states as $state)
                    <div class="leader-row">
                        <div>
                            <div class="leader-name">{{ $state['state'] }}</div>
                            <div class="leader-meta">{{ number_format($state['orders']) }} orders</div>
                        </div>
                        <div class="leader-money">${{ number_format($state['revenue'], 2) }}</div>
                    </div>
                @empty
                    <p class="muted">No state data found.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="grid-two">
        <div class="card">
            <h2 class="section-title">Top Products</h2>

            <div class="leaderboard">
                @forelse($products as $product)
                    <div class="leader-row">
                        <div>
                            <div class="leader-name">
                                @if(!empty($product->group_id))
                                    <a href="{{ route('reports.product-groups.report', $product->group_id) }}">
                                        {{ $product->name }}
                                    </a>
                                @else
                                    {{ $product->name }}
                                @endif
                            </div>

                            <div class="leader-meta">
                                {{ number_format($product->quantity_sold) }} sold

                                @if(!empty($product->raw_product_count) && $product->raw_product_count > 1)
                                    · {{ $product->raw_product_count }} products grouped
                                @endif
                            </div>
                        </div>

                        <div class="leader-money">${{ number_format($product->revenue, 2) }}</div>
                    </div>
                @empty
                    <p class="muted">No product data found.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <h2 class="section-title">Reconciliation Summary</h2>

            <div class="leaderboard">
                <div class="leader-row">
                    <div>
                        <div class="leader-name">Matched Orders</div>
                        <div class="leader-meta">WooCommerce orders linked to Stripe transactions</div>
                    </div>
                    <div class="leader-money">{{ number_format($totals['matched']) }}</div>
                </div>

                <div class="leader-row">
                    <div>
                        <div class="leader-name">Unmatched Orders</div>
                        <div class="leader-meta">Usually missing WooCommerce transaction IDs</div>
                    </div>
                    <div class="leader-money">{{ number_format($totals['unmatched']) }}</div>
                </div>

                <div class="leader-row">
                    <div>
                        <div class="leader-name">Match Rate</div>
                        <div class="leader-meta">Higher is better for fee/net reporting</div>
                    </div>
                    <div class="leader-money">{{ number_format($totals['match_rate'], 1) }}%</div>
                </div>
            </div>
        </div>
    </section>

    <section class="card table-card">
        <h2 class="section-title">Recent Orders</h2>

        <table>
            <thead>
            <tr>
                <th>Order</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Location</th>
                <th>Products</th>
                <th class="money">Woo Total</th>
                <th class="money">Stripe Fee</th>
                <th class="money">Stripe Net</th>
                <th>Match</th>
            </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>#{{ $order->order_number }}</td>
                    <td>{{ optional($order->date_created)->format('Y-m-d') }}</td>
                    <td>{{ trim($order->billing_first_name . ' ' . $order->billing_last_name) }}</td>
                    <td>{{ $order->billing_email }}</td>
                    <td>
                        {{ $order->billing_city }}
                        {{ $order->billing_state ? ', ' . $order->billing_state : '' }}
                    </td>
                    <td class="products-cell">
                        @foreach($order->items as $item)
                            <div>
                                {{ $item->name }}
                                <span class="muted">× {{ $item->quantity }}</span>
                                @if($item->sku)
                                    <span class="muted">({{ $item->sku }})</span>
                                @endif
                            </div>
                        @endforeach
                    </td>
                    <td class="money">${{ number_format($order->total, 2) }}</td>
                    <td class="money">
                        @if($order->stripeTransaction)
                            ${{ number_format($order->stripeTransaction->fee, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="money">
                        @if($order->stripeTransaction)
                            ${{ number_format($order->stripeTransaction->net, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $order->match_status }}">
                            {{ $order->match_status ?: 'unknown' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">No orders found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        @if ($orders->hasPages())
            <div class="sab-pagination">
                <div class="sab-pagination-meta">
                    Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }} orders
                </div>

                <div class="sab-pagination-links">
                    @if ($orders->onFirstPage())
                        <span class="sab-page disabled">Previous</span>
                    @else
                        <a class="sab-page" href="{{ $orders->previousPageUrl() }}">Previous</a>
                    @endif

                    <span class="sab-page current">
                        Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}
                    </span>

                    @if ($orders->hasMorePages())
                        <a class="sab-page" href="{{ $orders->nextPageUrl() }}">Next</a>
                    @else
                        <span class="sab-page disabled">Next</span>
                    @endif
                </div>
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script>
        const monthlyRevenue = @json($monthlyRevenue);

        const labels = monthlyRevenue.map(item => item.month);
        const revenue = monthlyRevenue.map(item => item.revenue);
        const net = monthlyRevenue.map(item => item.net);

        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Gross Revenue',
                        data: revenue,
                        tension: 0.35
                    },
                    {
                        label: 'Net Revenue',
                        data: net,
                        tension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endpush