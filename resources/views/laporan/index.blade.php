@extends('layouts.app')
@section('title', 'Laporan Stok')

@section('content')
<div class="app-head p-3 px-4 mb-3 d-flex align-items-center gap-3">
    <span class="fs-3">&#128202;</span>
    <h1 class="h4 m-0">Laporan Mutasi &amp; Stok Barang</h1>
</div>

<form method="get" class="card shadow-sm mb-4">
    <div class="card-body row g-2 align-items-end">
        <div class="col-auto"><label class="form-label small fw-semibold">Dari tanggal</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control"></div>
        <div class="col-auto"><label class="form-label small fw-semibold">Sampai tanggal</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control"></div>
        <div class="col-auto"><button class="btn btn-primary">Tampilkan</button></div>
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

<h2 class="h6 fw-bold">3.1 Stok barang</h2>
@php $tabel(['KODE_BARANG','NAMA_BARANG','SATUAN','KATEGORI','TRANS_MASUK','TRANS_KELUAR','STOK_AKHIR'], $stok, ['TRANS_MASUK','TRANS_KELUAR','STOK_AKHIR']); @endphp

<h2 class="h6 fw-bold mt-4">3.2 Stok berdasarkan kelompok (Kategori &amp; Satuan)</h2>
@php $tabel(['KATEGORI','SATUAN','TRANS_MASUK','TRANS_KELUAR','STOK_AKHIR'], $kelompok, ['TRANS_MASUK','TRANS_KELUAR','STOK_AKHIR']); @endphp

<h2 class="h6 fw-bold mt-4">3.3 Transaksi keluar tertinggi per kelompok (Kategori &amp; Satuan)</h2>
@php $tabel(['KATEGORI','SATUAN','KODE_BARANG','NAMA_BARANG','TRANS_KELUAR'], $tertinggi, ['TRANS_KELUAR']); @endphp
@endsection
