<header class="report-topbar">
    <a href="{{ route('reports.sales.index') }}" class="report-brand">
        Sound Archive Books Reporting
    </a>

    <nav class="report-nav">
        <a href="{{ route('reports.sales.index') }}"
           class="{{ request()->routeIs('reports.sales.*') ? 'active' : '' }}">
            Sales
        </a>

        <a href="{{ route('reports.map.index') }}"
           class="{{ request()->routeIs('reports.map.*') ? 'active' : '' }}">
            Map
        </a>

        <a href="{{ route('reports.product-groups.index') }}"
           class="{{ request()->routeIs('reports.product-groups.*') ? 'active' : '' }}">
            Product Groups
        </a>

        <form method="POST" action="{{ route('reports.resync') }}" style="display:inline;">
            @csrf

            <button type="submit"
                    onclick="return confirm('Re-sync Stripe and WooCommerce data?');">
                Re-Sync
            </button>
        </form>
    </nav>
</header>
