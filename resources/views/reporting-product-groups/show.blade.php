@extends('layouts.reporting', ['title' => $productGroup->name])

@section('content')
    <section class="hero">
        <div class="hero-label">Product Group</div>
        <h1>{{ $productGroup->name }}</h1>
        <p>{{ $productGroup->description ?: 'Manage matching rules and preview WooCommerce products included in this reporting group.' }}</p>
    </section>

    <section class="grid">
        <div class="card">
            <h2 style="margin-top:0;">Edit Group</h2>

            <form method="POST" action="{{ route('reports.product-groups.update', $productGroup) }}">
                @csrf
                @method('PUT')

                <label>Group Name</label>
                <input type="text" name="name" value="{{ $productGroup->name }}" required>

                <label>Description</label>
                <textarea name="description" rows="4">{{ $productGroup->description }}</textarea>

                <label class="checkbox-label">
                    <input type="checkbox" name="active" value="1" {{ $productGroup->active ? 'checked' : '' }}>
                    Active
                </label>

                <button type="submit" class="button">Save Group</button>
            </form>

            <form method="POST" action="{{ route('reports.product-groups.destroy', $productGroup) }}" onsubmit="return confirm('Delete this product group?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="button danger">Delete Group</button>
            </form>
        </div>

        <div class="card">
            <h2 style="margin-top:0;">Add Matching Rule</h2>

            <form method="POST" action="{{ route('reports.product-groups.rules.store', $productGroup) }}">
                @csrf

                <label>Match Field</label>
                <select name="match_field" required>
                    <option value="name">Product Name</option>
                    <option value="sku">SKU</option>
                    <option value="product_id">Product ID</option>
                    <option value="variation_id">Variation ID</option>
                </select>

                <label>Match Operator</label>
                <select name="match_operator" required>
                    <option value="contains">Contains</option>
                    <option value="equals">Equals</option>
                    <option value="starts_with">Starts With</option>
                    <option value="ends_with">Ends With</option>
                </select>

                <label>Match Value</label>
                <input type="text" name="match_value" placeholder="Sheila Kay Adams" required>

                <label>Level Name</label>
                <input type="text" name="level_name" placeholder="Supporter Edition, Hardcover Preorder, etc.">

                <label>Priority</label>
                <input type="number" name="priority" value="0">

                <button type="submit" class="button">Add Rule</button>
            </form>
        </div>
    </section>

    <section class="card table-card">
        <h2 style="margin-top:0;">Rules</h2>

        <table>
            <thead>
            <tr>
                <th>Field</th>
                <th>Operator</th>
                <th>Value</th>
                <th>Level</th>
                <th>Priority</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($productGroup->rules as $rule)
                <tr>
                    <td>{{ $rule->match_field }}</td>
                    <td>{{ $rule->match_operator }}</td>
                    <td>{{ $rule->match_value }}</td>
                    <td>{{ $rule->level_name ?: '—' }}</td>
                    <td>{{ $rule->priority }}</td>
                    <td>
                        <form class="inline-form" method="POST" action="{{ route('reports.product-groups.rules.destroy', [$productGroup, $rule]) }}" onsubmit="return confirm('Delete this rule?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="button danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No rules yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </section>

    <section class="card table-card">
        <h2 style="margin-top:0;">Matching Preview</h2>
        <p class="muted">These WooCommerce order items currently match this group’s active rules.</p>

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
            @forelse($previewItems as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->sku ?: '—' }}</td>
                    <td>{{ $item->product_id ?: '—' }}</td>
                    <td>{{ $item->variation_id ?: '—' }}</td>
                    <td class="money">{{ number_format($item->quantity_sold) }}</td>
                    <td class="money">${{ number_format($item->revenue, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No matching items yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </section>
@endsection