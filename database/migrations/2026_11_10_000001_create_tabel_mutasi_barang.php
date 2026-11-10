<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('MSBARANG', function (Blueprint $t) {
            $t->string('KODE_BARANG', 5)->primary();
            $t->string('NAMA_BARANG', 50);
            $t->string('SATUAN', 5);
            $t->string('KATEGORI', 10);
        });

        Schema::create('TRMUTASIHD', function (Blueprint $t) {
            $t->string('NO_TRANSAKSI', 5)->primary();
            $t->date('TANGGAL');
            $t->string('JENIS_TRANSAKSI', 10); // MASUK / KELUAR
            $t->string('KATEGORI', 10);
        });

        Schema::create('TRMUTASIDT', function (Blueprint $t) {
            $t->string('NO_TRANSAKSI', 5);
            $t->string('KODE_BARANG', 5);
            $t->integer('QTY');
            $t->primary(['NO_TRANSAKSI', 'KODE_BARANG']);
            $t->foreign('NO_TRANSAKSI')->references('NO_TRANSAKSI')->on('TRMUTASIHD');
            $t->foreign('KODE_BARANG')->references('KODE_BARANG')->on('MSBARANG');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TRMUTASIDT');
        Schema::dropIfExists('TRMUTASIHD');
        Schema::dropIfExists('MSBARANG');
    }
};
