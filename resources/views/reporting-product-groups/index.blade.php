@extends('layouts.reporting', ['title' => 'Product Groups'])

@section('content')
    <section class="hero">
        <div class="hero-label">Reporting Setup</div>
        <h1>Product Groups</h1>
        <p>Create reporting groups that roll WooCommerce products and reward levels into larger campaigns, books, or projects.</p>
    </section>

    <section class="grid">
        <div class="card">
            <h2 style="margin-top:0;">Create Product Group</h2>

            <form method="POST" action="{{ route('reports.product-groups.store') }}">
                @csrf

                <label>Group Name</label>
                <input type="text" name="name" placeholder="Sheila Kay Adams: Carrying the Song" required>

                <label>Description</label>
                <textarea name="description" rows="4" placeholder="Optional reporting notes"></textarea>

                <button type="submit" class="button">Create Group</button>
            </form>
        </div>

        <div class="card">
            <h2 style="margin-top:0;">Existing Groups</h2>

            <table>
                <thead>
                <tr>
                    <th>Group</th>
                    <th>Rules</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($groups as $group)
                    <tr>
                        <td>
                            <strong>{{ $group->name }}</strong>
                            @if($group->description)
                                <div class="muted">{{ $group->description }}</div>
                            @endif
                        </td>
                        <td>{{ $group->rules_count }}</td>
                        <td>{{ $group->active ? 'Active' : 'Inactive' }}</td>
                        <td>
                            <a class="button" href="{{ route('reports.product-groups.show', $group) }}">Manage</a>
                            <a class="button" href="{{ route('reports.product-groups.report', $group) }}">Report</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No groups yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card table-card">
        <h2 style="margin-top:0;">Top Raw WooCommerce Products</h2>
        <p class="muted">Use these product names, SKUs, product IDs, or variation IDs when creating matching rules.</p>

        <table>
            <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Product ID</th>
                <th>Variation ID</th>
                <th class="money">Qty</th>
                <th class="money">Revenue</th>
            </tr>
            </thead>
            <tbody>
            @forelse($ungroupedProducts as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku ?: '—' }}</td>
                    <td>{{ $product->product_id ?: '—' }}</td>
                    <td>{{ $product->variation_id ?: '—' }}</td>
                    <td class="money">{{ number_format($product->quantity_sold) }}</td>
                    <td class="money">${{ number_format($product->revenue, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No WooCommerce order items found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </section>
@endsection