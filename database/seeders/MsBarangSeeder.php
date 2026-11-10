<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MsBarangSeeder extends Seeder
{
    /** Hanya MSBARANG yang diisi dari database (sesuai catatan soal). */
    public function run(): void
    {
        DB::table('MSBARANG')->upsert([
            ['KODE_BARANG' => 'S001', 'NAMA_BARANG' => 'KLIP',       'SATUAN' => 'BOX', 'KATEGORI' => 'SUBMAT'],
            ['KODE_BARANG' => 'S002', 'NAMA_BARANG' => 'RIVET',      'SATUAN' => 'PCS', 'KATEGORI' => 'SUBMAT'],
            ['KODE_BARANG' => 'S003', 'NAMA_BARANG' => 'PALET',      'SATUAN' => 'PCS', 'KATEGORI' => 'SUBMAT'],
            ['KODE_BARANG' => 'R001', 'NAMA_BARANG' => 'FLAT BAR',   'SATUAN' => 'KG',  'KATEGORI' => 'RAWMAT'],
            ['KODE_BARANG' => 'R002', 'NAMA_BARANG' => 'STEEL WIRE', 'SATUAN' => 'KG',  'KATEGORI' => 'RAWMAT'],
        ], ['KODE_BARANG'], ['NAMA_BARANG', 'SATUAN', 'KATEGORI']);
    }
}
