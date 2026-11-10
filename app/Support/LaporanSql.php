<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Query laporan 3.1 - 3.3. Setiap query memakai 2 placeholder (?) berurutan:
 * tanggal awal dan tanggal akhir (inklusif). Sintaks kompatibel dengan SQL Server.
 */
class LaporanSql
{
    private static function cte(): string
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $dateFilter = $isSqlite
            ? "h.TANGGAL >= ? AND h.TANGGAL < date(?, '+1 day')"
            : "h.TANGGAL >= ? AND h.TANGGAL < DATEADD(DAY, 1, ?)";

        return "WITH STOK AS (
    SELECT b.KODE_BARANG, b.NAMA_BARANG, b.SATUAN, b.KATEGORI,
           COALESCE(SUM(CASE WHEN h.JENIS_TRANSAKSI = 'MASUK'  THEN d.QTY END), 0) AS TRANS_MASUK,
           COALESCE(SUM(CASE WHEN h.JENIS_TRANSAKSI = 'KELUAR' THEN d.QTY END), 0) AS TRANS_KELUAR
    FROM MSBARANG b
    LEFT JOIN TRMUTASIDT d ON d.KODE_BARANG = b.KODE_BARANG
    LEFT JOIN TRMUTASIHD h ON h.NO_TRANSAKSI = d.NO_TRANSAKSI
                          AND $dateFilter
    GROUP BY b.KODE_BARANG, b.NAMA_BARANG, b.SATUAN, b.KATEGORI
)\n";
    }

    /** 3.1 Stok barang */
    public static function stokBarang(): string
    {
        return self::cte() . "SELECT KODE_BARANG, NAMA_BARANG, SATUAN, KATEGORI,
       TRANS_MASUK, TRANS_KELUAR, TRANS_MASUK - TRANS_KELUAR AS STOK_AKHIR
FROM STOK
ORDER BY KATEGORI DESC, KODE_BARANG";
    }

    /** 3.2 Stok per kelompok (Kategori & Satuan) */
    public static function stokKelompok(): string
    {
        return self::cte() . "SELECT KATEGORI, SATUAN,
       SUM(TRANS_MASUK)  AS TRANS_MASUK,
       SUM(TRANS_KELUAR) AS TRANS_KELUAR,
       SUM(TRANS_MASUK) - SUM(TRANS_KELUAR) AS STOK_AKHIR
FROM STOK
GROUP BY KATEGORI, SATUAN
ORDER BY KATEGORI DESC, SATUAN";
    }

    /** 3.3 Transaksi keluar tertinggi per kelompok (Kategori & Satuan) */
    public static function keluarTertinggi(): string
    {
        return self::cte() . ", RANKED AS (
    SELECT KATEGORI, SATUAN, KODE_BARANG, NAMA_BARANG, TRANS_KELUAR,
           RANK() OVER (PARTITION BY KATEGORI, SATUAN ORDER BY TRANS_KELUAR DESC) AS RNK
    FROM STOK
)
SELECT KATEGORI, SATUAN, KODE_BARANG, NAMA_BARANG, TRANS_KELUAR
FROM RANKED
WHERE RNK = 1
ORDER BY KATEGORI DESC, SATUAN";
    }
}
