@extends('layouts.app')
@section('title', 'Laporan Stok')

@section('content')
<div class="app-head p-3 px-4 mb-3 d-flex align-items-center gap-3">
    <span class="d-inline-flex align-items-center justify-content-center bg-white bg-opacity-25 rounded-3 p-2 text-white shadow-sm" style="width: 44px; height: 44px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
            <path d="M11 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7h1z"/>
        </svg>
    </span>
    <div>
        <h1 class="h4 m-0 fw-bold">Laporan Mutasi &amp; Stok Barang</h1>
        <div class="small text-white-50">Monitoring pergerakan stok berbasis T-SQL query</div>
    </div>
</div>

<form method="get" class="card shadow-sm mb-4">
    <div class="card-body row g-2 align-items-end">
        <div class="col-auto"><label class="form-label small fw-semibold">Dari tanggal</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control"></div>
        <div class="col-auto"><label class="form-label small fw-semibold">Sampai tanggal</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control"></div>
        <div class="col-auto"><button class="btn btn-primary"><i class="bi bi-funnel-fill me-1"></i> Tampilkan</button></div>
        <div class="col text-muted small">Default periode soal: Januari - Februari 2021.</div>
    </div>
</form>

@php
    $tabel = function (array $kolom, $rows, array $angka = []) {
        echo '<div class="table-responsive"><table class="table table-bordered table-sm align-middle bg-white"><thead class="table-light"><tr>';
        foreach ($kolom as $k) echo '<th>' . e($k) . '</th>';
        echo '</tr></thead><tbody>';
        if (!count($rows)) echo '<tr><td colspan="' . count($kolom) . '" class="text-center text-muted">Tidak ada data.</td></tr>';
        foreach ($rows as $r) {
            echo '<tr>';
            foreach ($kolom as $k) echo '<td class="' . (in_array($k, $angka) ? 'text-end' : '') . '">' . e($r->$k) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    };
@endphp

<h2 class="h6 fw-bold"><i class="bi bi-box-seam me-1 text-primary"></i> 3.1 Stok barang</h2>
@php $tabel(['KODE_BARANG','NAMA_BARANG','SATUAN','KATEGORI','TRANS_MASUK','TRANS_KELUAR','STOK_AKHIR'], $stok, ['TRANS_MASUK','TRANS_KELUAR','STOK_AKHIR']); @endphp

<h2 class="h6 fw-bold mt-4"><i class="bi bi-tags me-1 text-primary"></i> 3.2 Stok berdasarkan kelompok (Kategori &amp; Satuan)</h2>
@php $tabel(['KATEGORI','SATUAN','TRANS_MASUK','TRANS_KELUAR','STOK_AKHIR'], $kelompok, ['TRANS_MASUK','TRANS_KELUAR','STOK_AKHIR']); @endphp

<h2 class="h6 fw-bold mt-4"><i class="bi bi-graph-up-arrow me-1 text-primary"></i> 3.3 Transaksi keluar tertinggi per kelompok (Kategori &amp; Satuan)</h2>
@php $tabel(['KATEGORI','SATUAN','KODE_BARANG','NAMA_BARANG','TRANS_KELUAR'], $tertinggi, ['TRANS_KELUAR']); @endphp
@endsection
