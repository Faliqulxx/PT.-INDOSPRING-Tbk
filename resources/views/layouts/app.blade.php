<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Transaksi Barang') - PT Indospring Tbk</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%232563eb'><path d='M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z'/></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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
    <div class="fw-bold text-primary d-inline-flex align-items-center gap-2">
        <i class="bi bi-building-gear fs-5"></i>
        <span>PT. INDOSPRING TBK <span class="text-muted fw-normal">| Test Skill IT Software</span></span>
    </div>
    <div class="btn-group btn-group-sm">
        <a href="{{ route('transaksi.index') }}" class="btn btn-outline-primary {{ request()->routeIs('transaksi.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right me-1"></i> Transaksi
        </a>
        <a href="{{ route('laporan.index') }}" class="btn btn-outline-primary {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan Stok
        </a>
    </div>
</nav>
<main class="container pb-5">
    @yield('content')
</main>
@stack('scripts')
</body>
</html>
