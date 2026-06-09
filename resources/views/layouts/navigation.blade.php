<header class="report-topbar">
    <a href="{{ route('reports.sales.index') }}" class="report-brand">
        Million Memory Project Reporting
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

        @auth
            <span class="nav-divider" aria-hidden="true"></span>

            <form method="POST" action="{{ route('reports.resync') }}" class="inline-form">
                @csrf
                <button type="submit" class="nav-action"
                        onclick="return confirm('Re-sync Stripe and WooCommerce data?');">
                    Re-Sync
                </button>
            </form>

            <div class="nav-menu" data-nav-menu>
                <button type="button" class="nav-menu-trigger {{ request()->routeIs('profile.*', 'users.*') ? 'is-active' : '' }}"
                        data-nav-menu-trigger aria-haspopup="true" aria-expanded="false">
                    <span>{{ auth()->user()->name }}</span>
                    <svg class="nav-menu-chevron" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <div class="nav-menu-panel" data-nav-menu-panel>
                    <a href="{{ route('profile.edit') }}"
                       class="nav-menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        Profile
                    </a>

                    @if(auth()->user()->isAdministrator())
                        <a href="{{ route('users.index') }}"
                           class="nav-menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            Users
                        </a>
                    @endif

                    <div class="nav-menu-sep" aria-hidden="true"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-menu-item nav-menu-item-danger">Log out</button>
                    </form>
                </div>
            </div>
        @endauth
    </nav>
</header>
