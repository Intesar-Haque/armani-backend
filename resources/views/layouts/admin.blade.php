<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · Armani Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f5f6f8; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .sidebar { min-height: 100vh; background: #101215; color: #d6d8dc; }
        .sidebar .brand { padding: 20px 24px; border-bottom: 1px solid #1e2226; color: #fff; font-weight: 600; letter-spacing: 0.5px; text-align: center; }
        .sidebar .brand img { max-width: 140px; height: auto; }
        .sidebar .nav-link { color: #b7bac0; padding: 10px 24px; display: flex; align-items: center; gap: 10px; border-left: 3px solid transparent; }
        .sidebar .nav-link:hover { background: #17191d; color: #fff; }
        .sidebar .nav-link.active { background: #17191d; color: #fff; border-left-color: #c9a04a; }
        .topbar { background: #fff; border-bottom: 1px solid #e6e8eb; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; }
        .main { padding: 24px; }
        .stat-card { background: #fff; border-radius: 10px; padding: 20px; border: 1px solid #e6e8eb; }
        .stat-card .label { color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 0.6px; }
        .stat-card .value { font-size: 28px; font-weight: 600; color: #101215; margin-top: 4px; }
        .stat-card .sub { color: #6b7280; font-size: 12px; margin-top: 6px; }
        .card-panel { background: #fff; border: 1px solid #e6e8eb; border-radius: 10px; }
        .card-panel .panel-header { padding: 14px 20px; border-bottom: 1px solid #e6e8eb; font-weight: 600; }
        .status-badge { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 12px; }
        .status-new { background: #eef2ff; color: #3730a3; }
        .status-contacted, .status-confirmed, .status-responded { background: #ecfdf5; color: #065f46; }
        .status-completed, .status-closed { background: #f3f4f6; color: #374151; }
        .status-cancelled { background: #fef2f2; color: #991b1b; }
        .table thead th { text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: #6b7280; border-bottom: 1px solid #e6e8eb; }
        .table tbody td { vertical-align: middle; }
    </style>
</head>
<body>
<div class="d-flex">
    <aside class="sidebar" style="width: 240px;">
        <div class="brand">
            <a href="{{ route('admin.dashboard') }}"><img src="{{ asset('logo.png') }}" alt="Armani Properties"></a>
        </div>
        <nav class="nav flex-column mt-2">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}" href="{{ route('admin.projects.index') }}">
                <i class="bi bi-buildings"></i> Projects
            </a>
            <a class="nav-link {{ request()->routeIs('admin.apartments.*') ? 'active' : '' }}" href="{{ route('admin.apartments.index') }}">
                <i class="bi bi-building"></i> Apartments
            </a>
            <a class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}" href="{{ route('admin.clients.index') }}">
                <i class="bi bi-people"></i> Clients
            </a>
            <a class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}" href="{{ route('admin.bookings.index') }}">
                <i class="bi bi-journal-check"></i> Bookings
            </a>
            <a class="nav-link {{ request()->routeIs('admin.enquiries.*') ? 'active' : '' }}" href="{{ route('admin.enquiries.index') }}">
                <i class="bi bi-envelope"></i> Enquiries
            </a>
            <a class="nav-link {{ request()->routeIs('admin.landowners.*') ? 'active' : '' }}" href="{{ route('admin.landowners.index') }}">
                <i class="bi bi-map"></i> Landowners
            </a>
            <a class="nav-link {{ request()->routeIs('admin.site-visits.*') ? 'active' : '' }}" href="{{ route('admin.site-visits.index') }}">
                <i class="bi bi-calendar-check"></i> Site Visits
            </a>
            <a class="nav-link {{ request()->routeIs('admin.queries.*') ? 'active' : '' }}" href="{{ route('admin.queries.index') }}">
                <i class="bi bi-chat-left-text"></i> Contact Queries
            </a>
            <a class="nav-link {{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}" href="{{ route('admin.jobs.index') }}">
                <i class="bi bi-briefcase"></i> Jobs
            </a>
            <a class="nav-link {{ request()->routeIs('admin.job-applications.*') ? 'active' : '' }}" href="{{ route('admin.job-applications.index') }}">
                <i class="bi bi-person-badge"></i> Applications
            </a>
            <a class="nav-link {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}" href="{{ route('admin.posts.index') }}">
                <i class="bi bi-newspaper"></i> Posts
            </a>
            <a class="nav-link {{ request()->routeIs('admin.share-assignments.*') ? 'active' : '' }}" href="{{ route('admin.share-assignments.index') }}">
                <i class="bi bi-pie-chart"></i> Shares
            </a>
            <a class="nav-link {{ request()->routeIs('admin.project-directors.*') ? 'active' : '' }}" href="{{ route('admin.project-directors.index') }}">
                <i class="bi bi-person-vcard"></i> Directors
            </a>
        </nav>
    </aside>
    <div class="flex-grow-1">
        <header class="topbar">
            <div><strong>@yield('title', 'Admin')</strong></div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small">{{ auth()->user()->name ?? 'Admin' }}</span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary" type="submit">
                        <i class="bi bi-box-arrow-right"></i> Log out
                    </button>
                </form>
            </div>
        </header>
        <main class="main">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
