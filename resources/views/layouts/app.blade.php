<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Franchise Management System') }}</title>

    <!-- Offline Internal Network Bundled Assets (Zero External CDN Dependency) -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">

    <style>
        :root {
            --brand-primary: #0f172a;
            --brand-accent: #2563eb;
            --brand-surface: #f8fafc;
            --brand-sidebar-width: 260px;
        }

        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        #sidebar-wrapper {
            width: var(--brand-sidebar-width);
            background: #0f172a;
            color: #94a3b8;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            transition: all 0.25s ease-in-out;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            font-size: 1.15rem;
            font-weight: 700;
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            border-bottom: 1px solid #1e293b;
        }

        .sidebar-nav {
            padding: 1rem 0;
            list-style: none;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-section-title {
            padding: 0.75rem 1.5rem 0.25rem;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 1.5rem;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.15s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar-link.active {
            color: #ffffff;
            background: #1e293b;
            border-left-color: #38bdf8;
            font-weight: 600;
        }

        .sidebar-link i {
            font-size: 1.15rem;
            width: 1.25rem;
            text-align: center;
        }

        /* Page Content Wrapper */
        #page-content-wrapper {
            margin-left: var(--brand-sidebar-width);
            width: calc(100% - var(--brand-sidebar-width));
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.25s ease-in-out;
        }

        /* Top Navbar */
        .top-navbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        /* Cards & Components */
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            background: #ffffff;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .stat-card {
            border-radius: 0.75rem;
            padding: 1.25rem;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .table > :not(caption) > * > * {
            padding: 0.85rem 1rem;
            border-bottom-color: #f1f5f9;
        }

        .badge {
            font-weight: 500;
            padding: 0.4em 0.65em;
            border-radius: 0.35rem;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                margin-left: calc(-1 * var(--brand-sidebar-width));
            }
            #sidebar-wrapper.show {
                margin-left: 0;
            }
            #page-content-wrapper {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <div id="sidebar-wrapper">
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <i class="bi bi-shop-window text-info fs-4"></i>
            <div>
                <span class="d-block lh-1 text-white fw-bold">Franchise HQ</span>
                <small class="text-secondary" style="font-size: 0.7rem; font-weight: 400;">Multi-Outlet Network</small>
            </div>
        </a>

        <ul class="sidebar-nav">
            <li class="sidebar-section-title">Operations Core</li>
            <li>
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('outlets.index') }}" class="sidebar-link {{ request()->routeIs('outlets.*') ? 'active' : '' }}">
                    <i class="bi bi-geo-alt"></i>
                    <span>Outlets & Stores</span>
                </a>
            </li>
            <li>
                <a href="{{ route('sales.index') }}" class="sidebar-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    <span>Sales & POS</span>
                </a>
            </li>

            <li class="sidebar-section-title">Catalog & Inventory</li>
            <li>
                <a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="bi bi-cup-hot"></i>
                    <span>Products & Menu</span>
                </a>
            </li>
            <li>
                <a href="{{ route('inventory.index') }}" class="sidebar-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <i class="bi bi-boxes"></i>
                    <span>Stock & Inventory</span>
                </a>
            </li>
            <li>
                <a href="{{ route('supply-orders.index') }}" class="sidebar-link {{ request()->routeIs('supply-orders.*') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i>
                    <span>Supply Orders</span>
                </a>
            </li>

            <li class="sidebar-section-title">Franchise & Quality</li>
            <li>
                <a href="{{ route('royalties.index') }}" class="sidebar-link {{ request()->routeIs('royalties.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>Royalties & Billing</span>
                </a>
            </li>
            <li>
                <a href="{{ route('audits.index') }}" class="sidebar-link {{ request()->routeIs('audits.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-check"></i>
                    <span>Standards & Audits</span>
                </a>
            </li>

            @if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isFranchiseOwner() || auth()->user()->isOutletManager()))
            <li class="sidebar-section-title">Administration</li>
            <li>
                <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Staff & Users</span>
                </a>
            </li>
            @endif

            @if(auth()->check() && auth()->user()->isSuperAdmin())
            <li>
                <a href="{{ route('admin.bulk-imports.index') }}" class="sidebar-link {{ request()->routeIs('admin.bulk-imports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-arrow-up"></i>
                    <span>Bulk Import Hub</span>
                </a>
            </li>
            @endif
        </ul>

        <!-- Bottom User Snapshot -->
        <div class="p-3 border-top border-secondary border-opacity-25 bg-dark bg-opacity-25">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="overflow-hidden flex-grow-1">
                    <div class="text-white text-truncate fw-semibold small">{{ auth()->user()->name ?? 'User' }}</div>
                    <span class="badge {{ auth()->user()->role_badge_class ?? 'bg-secondary' }}" style="font-size: 0.65rem;">
                        {{ auth()->user()->role_label ?? 'Staff' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <!-- Top Navbar -->
        <nav class="top-navbar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list fs-5"></i>
                </button>

                <!-- Active Outlet Context Switcher (for Admins & Owners) -->
                @if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isFranchiseOwner()))
                <form action="{{ route('dashboard.switch-outlet') }}" method="POST" class="d-flex align-items-center gap-2 m-0">
                    @csrf
                    <label for="outletSelect" class="small text-muted text-nowrap d-none d-md-inline">
                        <i class="bi bi-geo-alt-fill text-danger"></i> Outlet Context:
                    </label>
                    <select name="outlet_id" id="outletSelect" class="form-select form-select-sm" style="min-width: 200px;" onchange="this.form.submit()">
                        <option value="">-- All Brand Outlets (Consolidated) --</option>
                        @foreach(\App\Models\Outlet::orderBy('name')->get() as $out)
                            <option value="{{ $out->id }}" {{ session('active_outlet_id') == $out->id ? 'selected' : '' }}>
                                {{ $out->name }} ({{ $out->outlet_code }})
                            </option>
                        @endforeach
                    </select>
                </form>
                @elseif(auth()->check() && auth()->user()->outlet)
                <div class="d-flex align-items-center gap-2 text-dark small fw-medium">
                    <i class="bi bi-geo-alt-fill text-danger"></i>
                    <span>Store: <strong>{{ auth()->user()->outlet->name }}</strong> ({{ auth()->user()->outlet->outlet_code }})</span>
                </div>
                @endif
            </div>

            <!-- Right Controls -->
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-dark border d-none d-sm-inline-flex align-items-center gap-1">
                    <i class="bi bi-database text-success"></i> PostgreSQL 18 Ready
                </span>
                <span class="badge bg-light text-dark border d-none d-md-inline-flex align-items-center gap-1">
                    <i class="bi bi-wifi-off text-muted"></i> 100% Offline / Local Assets
                </span>

                <!-- Quick New Transaction Button -->
                <a href="{{ route('sales.create') }}" class="btn btn-sm btn-success d-flex align-items-center gap-1">
                    <i class="bi bi-plus-circle"></i> <span class="d-none d-sm-inline">New Sale / POS</span>
                </a>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-6"></i>
                        <span class="d-none d-md-inline">{{ auth()->user()->name ?? 'Account' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="dropdown-header">Signed in as: {{ auth()->user()->email ?? '' }}</li>
                        <li><hr class="dropdown-divider"></li>
                        @if(auth()->check() && auth()->user()->isSuperAdmin())
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.bulk-imports.index') }}">
                                <i class="bi bi-file-earmark-arrow-up me-2"></i> Bulk Import Hub
                            </a>
                        </li>
                        @endif
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Body Container -->
        <main class="p-3 p-md-4 flex-grow-1">
            <!-- Flash Alerts -->
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('status') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <strong>Please correct the following:</strong>
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-top py-3 px-4 text-center text-muted small mt-auto">
            <div class="container-fluid d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <div>
                    &copy; {{ date('Y') }} <strong>Franchise Management System</strong> &bull; Multi-Outlet Franchise Architecture
                </div>
                <div>
                    Laravel 13 &bull; PostgreSQL 18 &bull; Bootstrap 5.3 &bull; Standalone Air-Gapped Network Ready
                </div>
            </div>
        </footer>
    </div>

    <!-- Offline Bundled JS Assets -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>

    <script>
        // Toggle mobile sidebar
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                document.getElementById('sidebar-wrapper').classList.toggle('show');
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
