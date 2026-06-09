@extends('layouts.reporting', ['title' => 'Sales Map'])

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
@endpush



@section('content')
    <section class="hero">
        <div class="hero-label">Million Memory Project Reporting</div>
        <h1>Sales Map</h1>
        <p>Order geography based on WooCommerce billing state data.</p>
    </section>

    <section class="kpis">
        <div class="card kpi">
            <span>Mapped Revenue</span>
            <strong>${{ number_format($totals['revenue'], 2) }}</strong>
        </div>

        <div class="card kpi">
            <span>Mapped Orders</span>
            <strong>{{ number_format($totals['orders']) }}</strong>
        </div>

        <div class="card kpi">
            <span>States</span>
            <strong>{{ number_format($totals['states']) }}</strong>
        </div>

        <div class="card kpi">
            <span>Top State</span>
            <strong>{{ $totals['top_state'] }}</strong>
        </div>
    </section>

    <section class="layout">
        <div class="card">
            <div id="salesMap"></div>
        </div>

        <div class="card">
            <h2 style="margin-top:0;">Top States</h2>

            @forelse($states as $state)
                <div class="leader-row">
                    <div>
                        <div class="leader-name">{{ $state['state'] }}</div>
                        <div class="leader-meta">{{ number_format($state['orders']) }} orders</div>
                    </div>
                    <div class="leader-money">${{ number_format($state['revenue'], 2) }}</div>
                </div>
            @empty
                <p>No mapped sales found.</p>
            @endforelse
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const states = @json($states);

        const map = L.map('salesMap').setView([39.5, -98.35], 4);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 8,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const maxRevenue = Math.max(...states.map(state => state.revenue), 1);

        states.forEach(state => {
            const radius = 8 + ((state.revenue / maxRevenue) * 34);

            L.circleMarker([state.lat, state.lng], {
                radius: radius,
                fillOpacity: 0.65,
                weight: 2
            })
                .bindPopup(`
                    <strong>${state.state}</strong><br>
                    Orders: ${state.orders.toLocaleString()}<br>
                    Revenue: $${Number(state.revenue).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                `)
                .addTo(map);
        });
    </script>
@endpush