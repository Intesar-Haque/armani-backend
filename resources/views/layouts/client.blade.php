<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal') · Armani Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f5f6f8; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .topbar { background: #101215; color: #fff; padding: 14px 28px; display: flex; justify-content: space-between; align-items: center; }
        .topbar .brand { font-weight: 600; color: #c9a04a; letter-spacing: 0.6px; display:flex; align-items:center; gap:12px; }
        .topbar .brand img { max-height: 44px; width: auto; display:block; }
        .topbar .brand small { color:#8b8f96; font-weight: 400; text-transform: uppercase; letter-spacing: 1px; font-size: 11px; border-left:1px solid #2a2d31; padding-left:12px; }
        .main { padding: 28px; max-width: 1180px; margin: 0 auto; }
        .card-panel { background: #fff; border: 1px solid #e6e8eb; border-radius: 10px; }
        .card-panel .panel-header { padding: 14px 20px; border-bottom: 1px solid #e6e8eb; font-weight: 600; }
        .stat-card { background: #fff; border-radius: 10px; padding: 20px; border: 1px solid #e6e8eb; }
        .stat-card .label { color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 0.6px; }
        .stat-card .value { font-size: 24px; font-weight: 600; color: #101215; margin-top: 4px; }
        .status-badge { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 8px; border-radius: 12px; }
        .status-paid { background: #ecfdf5; color: #065f46; }
        .status-upcoming { background: #eff6ff; color: #1e40af; }
        .status-overdue { background: #fef2f2; color: #991b1b; }
        .status-active { background: #eef2ff; color: #3730a3; }
        .status-completed { background: #ecfdf5; color: #065f46; }
        .status-cancelled { background: #fef2f2; color: #991b1b; }
        .table thead th { text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: #6b7280; border-bottom: 1px solid #e6e8eb; }
        .table tbody td { vertical-align: middle; }
    </style>
</head>
<body>
<header class="topbar">
    <div class="brand">
        <a href="{{ route('client.dashboard') }}" style="color:inherit;text-decoration:none;display:flex;align-items:center;gap:12px;">
            <img src="{{ asset('logo.png') }}" alt="Armani Properties">
            <small>Client portal</small>
        </a>
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="text-white-50 small">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button class="btn btn-sm btn-outline-light" type="submit">
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
</body>
</html>
