@extends('layouts.reporting', ['title' => $productGroup->name . ' Report'])

@section('content')
    <section class="hero">
        <div class="hero-label">Product Group Report</div>
        <h1>{{ $productGroup->name }}</h1>
        <p>{{ $productGroup->description ?: 'Revenue, order, and reward-level breakdown for this grouped product.' }}</p>
    </section>

    <section class="kpis">
        <div class="card kpi">
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
            <span>Stripe Net</span>
            <strong>${{ number_format($totals['stripe_net'], 2) }}</strong>
            <small>From matched orders</small>
        </div>
    </section>

    <section class="grid-two">
        <div class="card">
            <h2 class="section-title">Level Breakdown</h2>

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
            <h2 class="section-title">Stripe Summary</h2>

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
        <h2 class="section-title">Orders in This Group</h2>

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
