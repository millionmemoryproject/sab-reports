@extends('layouts.reporting', ['title' => $productGroup->name . ' Report'])

@section('content')
    <section class="hero">
        <div class="hero-label">Product Group Report</div>
        <h1>{{ $productGroup->name }}</h1>
        <p>{{ $productGroup->description ?: 'Revenue, order, and reward-level breakdown for this grouped product.' }}</p>
    </section>

    <section class="kpis">
        <div class="card kpi is-positive">
            <span>Revenue</span>
            <strong>${{ number_format($totals['revenue'], 2) }}</strong>
            <small>WooCommerce item revenue</small>
        </div>

        <div class="card kpi">
            <span>Quantity Sold</span>
            <strong>{{ number_format($totals['quantity']) }}</strong>
            <small>Total grouped items</small>
        </div>

        <div class="card kpi">
            <span>Orders</span>
            <strong>{{ number_format($totals['orders']) }}</strong>
            <small>Orders containing this group</small>
        </div>

        <div class="card kpi">
            <span>Customers</span>
            <strong>{{ number_format($totals['customers']) }}</strong>
            <small>Unique buyers of this group</small>
        </div>

        <div class="card kpi is-positive">
            <span>Stripe Net</span>
            <strong>${{ number_format($totals['stripe_net'], 2) }}</strong>
            <small>From matched orders</small>
        </div>
    </section>

    <section class="grid-two">
        <div class="card">
            <h2 class="card-title">Level Breakdown</h2>

            <div class="leaderboard">
                @forelse($levels as $level)
                    <div class="leader-row">
                        <div>
                            <div class="leader-name">{{ $level['name'] }}</div>
                            <div class="leader-meta">
                                {{ number_format($level['quantity']) }} sold
                                · {{ $level['products']->count() }} raw product{{ $level['products']->count() === 1 ? '' : 's' }}
                            </div>
                        </div>
                        <div class="leader-money">${{ number_format($level['revenue'], 2) }}</div>
                    </div>
                @empty
                    <p class="muted">No levels found.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">Stripe Summary</h2>

            <div class="leaderboard">
                <div class="leader-row">
                    <div>
                        <div class="leader-name">Stripe Fees</div>
                        <div class="leader-meta">From matched orders containing this group</div>
                    </div>
                    <div class="leader-money">${{ number_format($totals['stripe_fees'], 2) }}</div>
                </div>

                <div class="leader-row">
                    <div>
                        <div class="leader-name">Stripe Net</div>
                        <div class="leader-meta">Net payment after Stripe fees</div>
                    </div>
                    <div class="leader-money">${{ number_format($totals['stripe_net'], 2) }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="card table-card">
        <h2 class="card-title">Raw Products</h2>
        <p class="muted">Individual WooCommerce products grouped into this reporting group.</p>

        <table>
            <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Level</th>
                <th class="money">Qty</th>
                <th class="money">Revenue</th>
            </tr>
            </thead>
            <tbody>
            @forelse($rawProducts as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku ?: '—' }}</td>
                    <td>{{ $product->level ?: '—' }}</td>
                    <td class="money">{{ number_format($product->quantity) }}</td>
                    <td class="money">${{ number_format($product->revenue, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No products found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </section>

    <section class="card table-card">
        <h2 class="card-title">Customers</h2>
        <p class="muted">Customers who purchased products in this group, by revenue.</p>

        <table>
            <thead>
            <tr>
                <th>Customer</th>
                <th>Email</th>
                <th class="money">Orders</th>
                <th class="money">Qty</th>
                <th class="money">Revenue</th>
            </tr>
            </thead>
            <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->name ?: '—' }}</td>
                    <td>{{ $customer->email }}</td>
                    <td class="money">{{ number_format($customer->orders) }}</td>
                    <td class="money">{{ number_format($customer->quantity) }}</td>
                    <td class="money">${{ number_format($customer->revenue, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No customers found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </section>

    <section class="card table-card">
        <div class="card-header">
            <h2 class="card-title">Orders in This Group</h2>
            <a href="{{ route('reports.product-groups.report.export', $productGroup) }}" class="button small">Export CSV</a>
        </div>

        <table>
            <thead>
            <tr>
                <th>Order</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Location</th>
                <th class="money">Order Total</th>
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
                    <td colspan="9">No orders found for this group.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </section>
@endsection
