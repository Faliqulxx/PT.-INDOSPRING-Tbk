<?php

namespace App\Http\Controllers;

use App\Support\LaporanSql;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $valid = fn ($v) => is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) !== false;

        $from = $valid($request->query('from')) ? $request->query('from') : '2021-01-01';
        $to   = $valid($request->query('to'))   ? $request->query('to')   : '2021-02-28';
        $p    = [$from, $to];

        return view('laporan.index', [
            'from'     => $from,
            'to'       => $to,
            'stok'     => DB::select(LaporanSql::stokBarang(), $p),
            'kelompok' => DB::select(LaporanSql::stokKelompok(), $p),
            'tertinggi' => DB::select(LaporanSql::keluarTertinggi(), $p),
        ]);
    }
}
