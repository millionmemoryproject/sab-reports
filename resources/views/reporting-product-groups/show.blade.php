@extends('layouts.reporting', ['title' => $productGroup->name])

@section('content')
    <section class="hero">
        <div class="hero-label">Product Group</div>
        <h1>{{ $productGroup->name }}</h1>
        <p>{{ $productGroup->description ?: 'Manage matching rules and preview WooCommerce products included in this reporting group.' }}</p>
    </section>

    <section class="grid">
        <div class="card">
            <h2 class="card-title">Edit Group</h2>

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

            <div class="danger-zone">
                <form method="POST" action="{{ route('reports.product-groups.destroy', $productGroup) }}" onsubmit="return confirm('Delete this product group?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="button outline-danger">Delete Group</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">Add Matching Rule</h2>

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
        <h2 class="card-title">Rules</h2>
        <p class="muted">Click Edit to change a rule. Inactive rules are ignored when matching.</p>

        @php
            $fieldLabels = ['name' => 'Product Name', 'sku' => 'SKU', 'product_id' => 'Product ID', 'variation_id' => 'Variation ID'];
            $operatorLabels = ['contains' => 'Contains', 'equals' => 'Equals', 'starts_with' => 'Starts With', 'ends_with' => 'Ends With'];
        @endphp

        {{-- One edit form per rule; cells reference it via the form="" attribute. --}}
        @foreach($productGroup->rules as $rule)
            <form id="rule-edit-{{ $rule->id }}" method="POST"
                  action="{{ route('reports.product-groups.rules.update', [$productGroup, $rule]) }}">
                @csrf
                @method('PUT')
            </form>
        @endforeach

        <table>
            <thead>
            <tr>
                <th>Field</th>
                <th>Operator</th>
                <th>Value</th>
                <th>Level</th>
                <th>Priority</th>
                <th>Active</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($productGroup->rules as $rule)
                @php($f = 'rule-edit-'.$rule->id)
                <tr class="rule-row">
                    <td>
                        <span class="cell-view">{{ $fieldLabels[$rule->match_field] ?? $rule->match_field }}</span>
                        <select class="cell-edit" name="match_field" form="{{ $f }}">
                            <option value="name" @selected($rule->match_field === 'name')>Product Name</option>
                            <option value="sku" @selected($rule->match_field === 'sku')>SKU</option>
                            <option value="product_id" @selected($rule->match_field === 'product_id')>Product ID</option>
                            <option value="variation_id" @selected($rule->match_field === 'variation_id')>Variation ID</option>
                        </select>
                    </td>
                    <td>
                        <span class="cell-view">{{ $operatorLabels[$rule->match_operator] ?? $rule->match_operator }}</span>
                        <select class="cell-edit" name="match_operator" form="{{ $f }}">
                            <option value="contains" @selected($rule->match_operator === 'contains')>Contains</option>
                            <option value="equals" @selected($rule->match_operator === 'equals')>Equals</option>
                            <option value="starts_with" @selected($rule->match_operator === 'starts_with')>Starts With</option>
                            <option value="ends_with" @selected($rule->match_operator === 'ends_with')>Ends With</option>
                        </select>
                    </td>
                    <td>
                        <span class="cell-view">{{ $rule->match_value }}</span>
                        <input class="cell-edit" type="text" name="match_value" value="{{ $rule->match_value }}" form="{{ $f }}" required>
                    </td>
                    <td>
                        <span class="cell-view">{{ $rule->level_name ?: '—' }}</span>
                        <input class="cell-edit" type="text" name="level_name" value="{{ $rule->level_name }}" form="{{ $f }}">
                    </td>
                    <td>
                        <span class="cell-view">{{ $rule->priority }}</span>
                        <input class="cell-edit" type="number" name="priority" value="{{ $rule->priority }}" form="{{ $f }}">
                    </td>
                    <td>
                        <span class="cell-view">
                            <span class="badge {{ $rule->active ? 'matched' : '' }}">{{ $rule->active ? 'Active' : 'Inactive' }}</span>
                        </span>
                        <input class="cell-edit" type="checkbox" name="active" value="1" form="{{ $f }}" @checked($rule->active)>
                    </td>
                    <td>
                        <div class="rule-view-actions">
                            <button type="button" class="button small ghost" data-rule-edit>Edit</button>
                            <form class="inline-form" method="POST" action="{{ route('reports.product-groups.rules.destroy', [$productGroup, $rule]) }}" onsubmit="return confirm('Delete this rule?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button small outline-danger">Delete</button>
                            </form>
                        </div>
                        <div class="rule-edit-actions">
                            <button type="submit" class="button small" form="{{ $f }}">Save</button>
                            <button type="button" class="button small outline" data-rule-cancel="{{ $f }}">Cancel</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No rules yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </section>

    <section class="card table-card">
        <h2 class="card-title">Matching Preview</h2>
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

    @push('scripts')
        <script src="{{ asset('js/reports/rule-edit.js') }}"></script>
    @endpush
@endsection