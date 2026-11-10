# Test Skill IT Software - PT Indospring Tbk
**Aplikasi Pengelolaan Mutasi & Laporan Stok Barang (Laravel + SQL Server)**

Repositori ini berisi kode sumber aplikasi web pengelolaan mutasi barang masuk & keluar serta pelaporan stok bertingkat yang disusun untuk memenuhi tugas seleksi magang posisi **Developer Support IT** di PT Indospring Tbk.

---

## 1. Ikhtisar & Arsitektur
- **Framework**: Laravel 10 (PHP 8.1+)
- **Basis Data Target**: Microsoft SQL Server (T-SQL) via driver `sqlsrv` / `pdo_sqlsrv` & Microsoft ODBC Driver 17/18.
- **Frontend**: Blade Templating, Bootstrap 5.3 CDN, Vanilla JavaScript (AJAX Fetch API).
- **Skema Database**:
  - `MSBARANG`: Katalog master barang (`KODE_BARANG`, `NAMA_BARANG`, `SATUAN`, `KATEGORI`).
  - `TRMUTASIHD`: Header transaksi mutasi (`NO_TRANSAKSI`, `TANGGAL`, `JENIS_TRANSAKSI`, `KATEGORI`).
  - `TRMUTASIDT`: Detail item mutasi (`NO_TRANSAKSI`, `KODE_BARANG`, `QTY`) dengan composite primary key `(NO_TRANSAKSI, KODE_BARANG)`.

---

## 2. Catatan Transparansi Status Pengujian
- **Status Pengujian**: Aplikasi kini sudah diuji penuh di **Microsoft SQL Server 2025 Express** (instance `SQLEXPRESS`) dengan **PHP 8.1.25** + driver **`pdo_sqlsrv` 5.12.0** + **Microsoft ODBC Driver 18 for SQL Server**. Seluruh alur (form input, validasi bisnis, relasi data, aksi Koreksi/Hapus, kalkulasi stok, dan laporan 3.1–3.3) telah diuji dan **lulus di SQL Server**.
- **Dukungan Multi-Database**: Basis data utama berjalan di SQL Server, namun **SQLite tetap bisa dipakai sebagai alternatif lewat `.env`** (`DB_CONNECTION=sqlite`).
- **Catatan Perubahan & Kompatibilitas**:
  - **Seeder (`MsBarangSeeder`)**: Memakai `upsert()` (bukan `insertOrIgnore()`, karena `insertOrIgnore` tidak didukung oleh driver SQL Server).
  - **Sertifikat ODBC Driver 18**: `DB_TRUST_SERVER_CERTIFICATE=true` wajib disetel untuk ODBC Driver 18 di server lokal tanpa sertifikat tepercaya.
  - **Kompatibilitas T-SQL**: Seluruh query laporan (`sql/03_laporan.sql` / `app/Support/LaporanSql.php`) valid di SQL Server menggunakan Common Table Expression `WITH STOK AS (...)` dan Window Function `RANK() OVER (PARTITION BY ...)`.
  - **Penomoran Otomatis**: Dilengkapi *locking* (`lockForUpdate()`) di dalam `DB::transaction()` untuk menjamin keamanan penomoran otomatis di SQL Server.

---

## 3. Cara Menjalankan Aplikasi

1. **Clone repositori dan pasang dependensi**:
   ```bash
   git clone https://github.com/Faliqulxx/PT.-INDOSPRING-Tbk.git
   cd PT.-INDOSPRING-Tbk
   composer install
   ```

2. **Konfigurasi Environment**:
   Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   Secara default, `.env.example` sudah disiapkan untuk Microsoft SQL Server:
   ```env
   DB_CONNECTION=sqlsrv
   DB_HOST=127.0.0.1
   DB_PORT=1433
   DB_DATABASE=db_indospring_test
   DB_USERNAME=sa
   DB_PASSWORD=isi_password_anda
   DB_TRUST_SERVER_CERTIFICATE=true
   ```
   *(Opsional: Jika ingin menjalankan dengan SQLite lokal, ubah `DB_CONNECTION=sqlite` dan `DB_DATABASE=database/database.sqlite`)*.

3. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

4. **Jalankan Migrasi dan Seeder**:
   ```bash
   php artisan migrate --seed
   ```
   *Catatan: Perintah ini akan membuat 3 tabel dan hanya mengisi data master `MSBARANG`. Sesuai instruksi soal, data transaksi (`TRMUTASIHD` dan `TRMUTASIDT`) hanya boleh masuk melalui form input web.*

5. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```
   Buka peramban di alamat: [http://127.0.0.1:8000](http://127.0.0.1:8000) (otomatis redirect ke `/transaksi`).

---

## 4. Daftar Halaman

1. **`/transaksi` — Transaksi Barang Masuk & Keluar**:
   - Form input Header (No. Transaksi auto/manual, Tanggal, Jenis Transaksi, Kategori).
   - Form input Detail barang dinamis dengan tombol **Tambah Item** (Shortcut **F7**) dan tombol **Hapus Item** (via centang checkbox baris).
   - Tombol **Tambah** (simpan baru), **Koreksi** (update), **Hapus** (delete), dan **Form Baru** (reset).
   - Tabel **Daftar Transaksi** dengan tombol **Pilih** di setiap baris untuk memuat data ke form guna melakukan Koreksi atau Hapus.
2. **`/laporan` — Laporan Mutasi & Stok Barang**:
   - Filter rentang periode tanggal (default: `2021-01-01` s/d `2021-02-28`).
   - **Tabel 3.1**: Stok barang per item (`TRANS_MASUK`, `TRANS_KELUAR`, `STOK_AKHIR`).
   - **Tabel 3.2**: Stok berdasarkan kelompok (`KATEGORI`, `SATUAN`).
   - **Tabel 3.3**: Transaksi keluar tertinggi per kelompok (`KATEGORI`, `SATUAN`, `KODE_BARANG`, `NAMA_BARANG`, `TRANS_KELUAR`).

---

## 5. Aturan Validasi & Integritas Bisnis

- **Nomor Transaksi**: Wajib unik. Jika dikosongkan, sistem otomatis membuat nomor urut berformat `T001`, `T002`, dst.
- **Jumlah Item Detail**: Minimal 1 baris item (transaksi kosong ditolak).
- **Kuantitas (Qty)**: Harus bilangan bulat positif > 0.
- **Keunikan Item**: Kode barang tidak boleh ganda dalam satu nota transaksi.
- **Kesesuaian Kategori**: Kategori barang pada item detail harus sama dengan kategori header.
- **Proteksi Stok Keluar**: Transaksi jenis `KELUAR` ditolak jika kuantitas melebihi stok yang tersedia saat itu.
- **Proteksi Stok Minus saat Hapus/Koreksi**: Menghapus atau mengoreksi transaksi `MASUK` ditolak apabila menyebabkan saldo stok akhir barang menjadi minus (karena sudah dikonsumsi oleh transaksi keluar lain).
- **Atomisitas Database**: Header dan seluruh baris detail disimpan dalam blok `DB::transaction(...)` untuk menjamin integritas ACID.

---

## 6. Lampiran Skrip SQL (Folder `sql/`)
- `sql/01_schema.sql` : DDL pembuatan tabel `MSBARANG`, `TRMUTASIHD`, dan `TRMUTASIDT` (T-SQL).
- `sql/02_seed_msbarang.sql` : DML pengisian 5 data master barang awal (T-SQL).
- `sql/03_laporan.sql` : Query T-SQL laporan 3.1, 3.2, dan 3.3.
