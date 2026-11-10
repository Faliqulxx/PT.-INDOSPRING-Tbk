<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Transaksi Barang') - PT Indospring Tbk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6fb; }
        .app-head { background: linear-gradient(90deg, #1d4ed8, #2563eb 60%, #3b82f6); color: #fff; border-radius: 12px; }
        .card { border-radius: 12px; border-color: #e3e8f2; }
        .empty-box { color: #94a3b8; padding: 2rem 0; }
        tr.picked { background: #eff6ff; }
        .table-detail th { background: #eef2fb; font-weight: 600; font-size: .85rem; }
    </style>
</head>
<body>
<nav class="container py-3 d-flex justify-content-between align-items-center">
    <div class="fw-bold text-primary">PT. INDOSPRING TBK <span class="text-muted fw-normal">| Test Skill IT Software</span></div>
    <div class="btn-group btn-group-sm">
        <a href="{{ route('transaksi.index') }}" class="btn btn-outline-primary {{ request()->routeIs('transaksi.*') ? 'active' : '' }}">Transaksi</a>
        <a href="{{ route('laporan.index') }}" class="btn btn-outline-primary {{ request()->routeIs('laporan.*') ? 'active' : '' }}">Laporan Stok</a>
    </div>
</nav>
<main class="container pb-5">
    @yield('content')
</main>
@stack('scripts')
</body>
</html>
