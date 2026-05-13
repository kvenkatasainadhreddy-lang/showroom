<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Showroom ERP')</title>
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #0d6efd;
            --sidebar-bg: #12171e;
            --sidebar-text: #a0aab4;
            --sidebar-hover: rgba(255,255,255,0.07);
            --sidebar-active: rgba(13,110,253,0.15);
        }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; min-height: 100vh; }
        /* ── Sidebar ──────────────────────────────────────── */
        #sidebar {
            position: fixed; top: 0; left: 0; height: 100vh;
            width: var(--sidebar-width); background: var(--sidebar-bg);
            overflow-y: auto; z-index: 1040; transition: transform .3s ease;
        }
        #sidebar .sidebar-brand {
            padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: .75rem;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        #sidebar .brand-icon {
            width: 38px; height: 38px; background: var(--primary); border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        #sidebar .brand-text { color: #fff; font-weight: 700; font-size: 1rem; line-height: 1.1; }
        #sidebar .brand-sub { color: var(--sidebar-text); font-size: .72rem; }
        #sidebar .nav-section {
            padding: .5rem 1rem .2rem; font-size: .68rem; font-weight: 700;
            letter-spacing: .08em; color: rgba(255,255,255,.3); text-transform: uppercase; margin-top: .5rem;
        }
        #sidebar .nav-link {
            display: flex; align-items: center; gap: .65rem; padding: .57rem 1.25rem;
            color: var(--sidebar-text); font-size: .875rem; border-radius: 8px; margin: 1px 8px;
            transition: background .15s, color .15s;
        }
        #sidebar .nav-link:hover { background: var(--sidebar-hover); color: #fff; }
        #sidebar .nav-link.active { background: var(--sidebar-active); color: #3d8af7; font-weight: 600; }
        #sidebar .nav-link i { font-size: 1rem; width: 20px; }
        /* ── Main Content ─────────────────────────────────── */
        #main { margin-left: var(--sidebar-width); min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            background: #fff; border-bottom: 1px solid #e8eaed; padding: .65rem 1.5rem;
            display: flex; align-items: center; gap: 1rem; position: sticky; top: 0; z-index: 1020;
        }
        .topbar .page-title { font-weight: 600; font-size: 1rem; color: #1a1d23; }
        .content-area { padding: 1.5rem; flex: 1; }
        /* ── Cards ────────────────────────────────────────── */
        .card { border: 0; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
        .card-header { background: transparent; border-bottom: 1px solid #f0f2f5; font-weight: 600; padding: 1rem 1.25rem; }
        /* ── Stat Cards ───────────────────────────────────── */
        .stat-card { border-radius: 12px; padding: 1.25rem; color: #fff; position: relative; overflow: hidden; }
        .stat-card .stat-icon { font-size: 2rem; opacity: .25; position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); }
        .stat-card .stat-label { font-size: .78rem; font-weight: 500; opacity: .85; }
        .stat-card .stat-value { font-size: 1.65rem; font-weight: 700; }
        /* ── Badges ───────────────────────────────────────── */
        .badge-status-paid { background: #d1fae5; color: #065f46; }
        .badge-status-partial { background: #fef3c7; color: #92400e; }
        .badge-status-unpaid { background: #fee2e2; color: #991b1b; }
        /* ── Table ────────────────────────────────────────── */
        .table-responsive { border-radius: 8px; }
        .table th { font-size: .8rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; }
        /* ── Forms ────────────────────────────────────────── */
        .form-label { font-size: .85rem; font-weight: 500; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 .2rem rgba(13,110,253,.15); }
        /* ── Responsive ───────────────────────────────────── */
        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main { margin-left: 0; }
            .sidebar-overlay { display: block !important; }
        }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1039; }
        /* ── Utility ──────────────────────────────────────── */
        .btn-xs { padding:.2rem .45rem; font-size:.75rem; line-height:1.3; border-radius:.3rem; }
        .fw-500 { font-weight: 500 !important; }
        .fw-600 { font-weight: 600 !important; }
        .fw-700 { font-weight: 700 !important; }
        /* ── Misc ─────────────────────────────────────────── */
        .list-group-item.disabled { cursor:default; opacity:.7; }
        code { background:#f0f2f5; padding:.1rem .35rem; border-radius:4px; font-size:.8rem; }
        .alert-sm { font-size:.82rem; }
    </style>
    @stack('styles')
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-truck text-white fs-5"></i></div>
        <div>
            <div class="brand-text">Showroom ERP</div>
            <div class="brand-sub">Management System</div>
        </div>
    </div>

    <div class="mt-2">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        @can('sales.view')
        <div class="nav-section">Sales</div>
        <a href="{{ route('admin.vehicle-sales.create') }}" class="nav-link {{ request()->routeIs('admin.vehicle-sales.*') ? 'active' : '' }}">
            <i class="bi bi-car-front"></i> New Vehicle Sale
        </a>
        <a href="{{ route('admin.sales.index') }}" class="nav-link {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> Sales History
        </a>
        @endcan

        @can('customers.view')
        <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Customers
        </a>
        @endcan

        @can('payments.view')
        <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
            <i class="bi bi-cash-stack"></i> Payments
        </a>
        @endcan

        @can('products.view')
        <div class="nav-section">Inventory</div>
        <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Products
        </a>
        @endcan

        @can('inventory.view')
        <a href="{{ route('admin.inventory.index') }}" class="nav-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
            <i class="bi bi-archive"></i> Inventory
        </a>
        @endcan

        @can('stock_movements.view')
        <a href="{{ route('admin.stock-movements.index') }}" class="nav-link {{ request()->routeIs('admin.stock-movements.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i> Stock Movements
        </a>
        <a href="{{ route('admin.pdf-imports.index') }}" class="nav-link {{ request()->routeIs('admin.pdf-imports.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-pdf"></i> PDF Import
        </a>
        <a href="{{ route('admin.vehicle-import.index') }}" class="nav-link {{ request()->routeIs('admin.vehicle-import.*') ? 'active' : '' }}">
            <i class="bi bi-cloud-upload"></i> Vehicle Import
        </a>
        <a href="{{ route('admin.price-management.index') }}" class="nav-link {{ request()->routeIs('admin.price-management.index') ? 'active' : '' }}">
            <i class="bi bi-tag"></i> Price Management
        </a>
        <a href="{{ route('admin.price-management.analytics') }}" class="nav-link {{ request()->routeIs('admin.price-management.analytics') ? 'active' : '' }}">
            <i class="bi bi-graph-up"></i> Price Analytics
        </a>
        @endcan

        @can('purchases.view')
        <div class="nav-section">Purchasing</div>
        <a href="{{ route('admin.purchases.index') }}" class="nav-link {{ request()->routeIs('admin.purchases.*') ? 'active' : '' }}">
            <i class="bi bi-bag-check"></i> Purchases
        </a>
        @endcan

        @can('suppliers.view')
        <a href="{{ route('admin.suppliers.index') }}" class="nav-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Suppliers
        </a>
        @endcan

        @can('expenses.view')
        <div class="nav-section">Finance</div>
        <a href="{{ route('admin.expenses.index') }}" class="nav-link {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i> Expenses
        </a>
        @endcan

        @can('reports.view')
        <a href="{{ route('admin.reports.profit-loss') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> P&amp;L Report
        </a>
        @endcan

        @can('brands.view')
        <div class="nav-section">Catalogue</div>
        <a href="{{ route('admin.brands.index') }}" class="nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
            <i class="bi bi-bookmark-star"></i> Brands
        </a>
        <a href="{{ route('admin.vehicle-models.index') }}" class="nav-link {{ request()->routeIs('admin.vehicle-models.*') ? 'active' : '' }}">
            <i class="bi bi-bicycle"></i> Models
        </a>
        <a href="{{ route('admin.vehicle-variants.index') }}" class="nav-link {{ request()->routeIs('admin.vehicle-variants.*') ? 'active' : '' }}">
            <i class="bi bi-palette"></i> Variants
        </a>
        <a href="{{ route('admin.vehicle-stock.index') }}" class="nav-link {{ request()->routeIs('admin.vehicle-stock.*') ? 'active' : '' }}">
            <i class="bi bi-car-front"></i> Vehicle Stock
        </a>
        <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> Categories
        </a>
        @endcan

        @can('brands.view')
        <div class="nav-section">Catalogue 2</div>
        <a href="{{ route('admin.catalogue.setup') }}" class="nav-link {{ request()->routeIs('admin.catalogue.setup') ? 'active' : '' }}">
            <i class="bi bi-grid-3x3-gap"></i> Brand / Model / Variant
        </a>
        <a href="{{ route('admin.catalogue.chassis') }}" class="nav-link {{ request()->routeIs('admin.catalogue.chassis') ? 'active' : '' }}">
            <i class="bi bi-car-front-fill"></i> Chassis Registry
        </a>
        @endcan

        @can('users.view')
        <div class="nav-section">System</div>
        <a href="{{ route('admin.branches.index') }}" class="nav-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt"></i> Branches
        </a>
        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i> Users
        </a>
        @role('admin')
        <a href="{{ route('admin.roles.index') }}" class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
            <i class="bi bi-shield-lock"></i> Roles & Permissions
        </a>
        @endrole
        @endcan


        <div class="mt-4 mb-3" style="border-top:1px solid rgba(255,255,255,.06); padding-top:.75rem;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent" style="color:#ef4444">
                    <i class="bi bi-box-arrow-left"></i> Logout
                </button>
            </form>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div id="main">
    <!-- Topbar -->
    <div class="topbar">
        <button class="btn btn-sm btn-light d-lg-none me-1" onclick="openSidebar()">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="page-title">@yield('title', 'Dashboard')</span>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-muted small d-none d-sm-block">{{ now()->format('d M Y') }}</span>
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:.75rem">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="d-none d-sm-inline small fw-500">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->getRoleNames()->first() }}</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-left me-1"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Page Content -->
    <div class="content-area">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<!-- Bootstrap 5.3 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery 3.7 -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    function openSidebar() {
        document.getElementById('sidebar').classList.add('show');
        document.getElementById('sidebarOverlay').style.display = 'block';
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('show');
        document.getElementById('sidebarOverlay').style.display = '';
    }
    // Auto-dismiss alerts after 5s
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            let alert = bootstrap.Alert.getOrCreateInstance(el);
            alert.close();
        });
    }, 5000);
</script>
@stack('scripts')
</body>
</html>
