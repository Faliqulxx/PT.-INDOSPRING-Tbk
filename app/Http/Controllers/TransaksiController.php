<?php

namespace App\Http\Controllers;

use App\Models\MsBarang;
use App\Models\TrMutasiDt;
use App\Models\TrMutasiHd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransaksiController extends Controller
{
    public function index()
    {
        $barang = MsBarang::orderBy('KODE_BARANG')->get(['KODE_BARANG', 'NAMA_BARANG', 'SATUAN', 'KATEGORI']);

        $daftar = DB::table('TRMUTASIHD as h')
            ->leftJoin('TRMUTASIDT as d', 'd.NO_TRANSAKSI', '=', 'h.NO_TRANSAKSI')
            ->groupBy('h.NO_TRANSAKSI', 'h.TANGGAL', 'h.JENIS_TRANSAKSI', 'h.KATEGORI')
            ->orderBy('h.NO_TRANSAKSI')
            ->get(['h.NO_TRANSAKSI', 'h.TANGGAL', 'h.JENIS_TRANSAKSI', 'h.KATEGORI', DB::raw('COUNT(d.KODE_BARANG) as JML_ITEM')])
            ->map(function ($r) {
                $r->TANGGAL = substr((string) $r->TANGGAL, 0, 10);
                return $r;
            });

        return view('transaksi.index', [
            'barang'   => $barang,
            'kategori' => $barang->pluck('KATEGORI')->unique()->values(),
            'daftar'   => $daftar,
            'nextNo'   => $this->generateNo(),
        ]);
    }

    /** Muat satu transaksi (header + detail) untuk Koreksi / Hapus. */
    public function show(string $no)
    {
        $hd = TrMutasiHd::findOrFail($no);
        $items = TrMutasiDt::where('NO_TRANSAKSI', $no)->orderBy('KODE_BARANG')->get(['KODE_BARANG', 'QTY']);

        return response()->json([
            'NO_TRANSAKSI'    => $hd->NO_TRANSAKSI,
            'TANGGAL'         => substr((string) $hd->TANGGAL, 0, 10),
            'JENIS_TRANSAKSI' => $hd->JENIS_TRANSAKSI,
            'KATEGORI'        => $hd->KATEGORI,
            'items'           => $items,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $no = strtoupper(trim((string) ($data['NO_TRANSAKSI'] ?? '')));
        if ($no === '') {
            $no = $this->generateNo();
        }
        if (TrMutasiHd::whereKey($no)->exists()) {
            throw ValidationException::withMessages(['NO_TRANSAKSI' => "No. Transaksi $no sudah dipakai."]);
        }

        $this->cekAturanBisnis($data, null);

        DB::transaction(function () use ($data, $no) {
            TrMutasiHd::create([
                'NO_TRANSAKSI'    => $no,
                'TANGGAL'         => $data['TANGGAL'],
                'JENIS_TRANSAKSI' => $data['JENIS_TRANSAKSI'],
                'KATEGORI'        => $data['KATEGORI'],
            ]);
            $this->simpanDetail($no, $data['items']);
        });

        return response()->json(['message' => "Transaksi $no berhasil disimpan.", 'NO_TRANSAKSI' => $no]);
    }

    public function update(Request $request, string $no)
    {
        $hd = TrMutasiHd::findOrFail($no);
        $data = $this->validated($request);
        $this->cekAturanBisnis($data, $no);

        DB::transaction(function () use ($hd, $data, $no) {
            $hd->update([
                'TANGGAL'         => $data['TANGGAL'],
                'JENIS_TRANSAKSI' => $data['JENIS_TRANSAKSI'],
                'KATEGORI'        => $data['KATEGORI'],
            ]);
            TrMutasiDt::where('NO_TRANSAKSI', $no)->delete();
            $this->simpanDetail($no, $data['items']);
        });

        return response()->json(['message' => "Transaksi $no berhasil dikoreksi."]);
    }

    public function destroy(string $no)
    {
        $hd = TrMutasiHd::findOrFail($no);

        // Menghapus transaksi MASUK tidak boleh membuat stok barang menjadi minus.
        if ($hd->JENIS_TRANSAKSI === 'MASUK') {
            foreach (TrMutasiDt::where('NO_TRANSAKSI', $no)->get() as $row) {
                $stokTanpaIni = $this->stok($row->KODE_BARANG, $no);
                if ($stokTanpaIni < 0) {
                    throw ValidationException::withMessages([
                        'items' => "Transaksi $no tidak dapat dihapus: stok {$row->KODE_BARANG} akan menjadi minus ($stokTanpaIni).",
                    ]);
                }
            }
        }

        DB::transaction(function () use ($hd, $no) {
            TrMutasiDt::where('NO_TRANSAKSI', $no)->delete();
            $hd->delete();
        });

        return response()->json(['message' => "Transaksi $no berhasil dihapus."]);
    }

    // ---------------------------------------------------------------- helper

    private function validated(Request $request): array
    {
        $kategori = MsBarang::distinct()->pluck('KATEGORI')->all();

        return $request->validate([
            'NO_TRANSAKSI'          => ['nullable', 'string', 'max:5', 'regex:/^[A-Za-z0-9]*$/'],
            'TANGGAL'               => ['required', 'date'],
            'JENIS_TRANSAKSI'       => ['required', Rule::in(['MASUK', 'KELUAR'])],
            'KATEGORI'              => ['required', Rule::in($kategori)],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.KODE_BARANG'   => ['required', 'string', Rule::exists('MSBARANG', 'KODE_BARANG')],
            'items.*.QTY'           => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Minimal harus ada 1 item barang.',
            'items.min'      => 'Minimal harus ada 1 item barang.',
            'items.*.KODE_BARANG.required' => 'Kode barang pada item belum dipilih.',
            'items.*.QTY.min'      => 'Qty harus lebih dari 0.',
            'items.*.QTY.integer'  => 'Qty harus berupa bilangan bulat.',
            'items.*.QTY.required' => 'Qty belum diisi.',
        ]);
    }

    /** Aturan: tidak ada barang ganda, kategori barang sesuai header, stok KELUAR tidak minus. */
    private function cekAturanBisnis(array $data, ?string $abaikanNo): void
    {
        $kodes = array_column($data['items'], 'KODE_BARANG');
        if (count($kodes) !== count(array_unique($kodes))) {
            throw ValidationException::withMessages(['items' => 'Kode barang tidak boleh ganda dalam satu transaksi.']);
        }

        $master = MsBarang::whereIn('KODE_BARANG', $kodes)->get()->keyBy('KODE_BARANG');
        foreach ($data['items'] as $it) {
            $b = $master[$it['KODE_BARANG']];
            if ($b->KATEGORI !== $data['KATEGORI']) {
                throw ValidationException::withMessages([
                    'items' => "Barang {$b->KODE_BARANG} berkategori {$b->KATEGORI}, tidak sesuai kategori transaksi {$data['KATEGORI']}.",
                ]);
            }
            if ($data['JENIS_TRANSAKSI'] === 'KELUAR' && $abaikanNo === null) {
                $stok = $this->stok($b->KODE_BARANG, null);
                if ($it['QTY'] > $stok) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$b->KODE_BARANG} ({$b->NAMA_BARANG}) tidak cukup. Stok tersedia: $stok {$b->SATUAN}, diminta: {$it['QTY']}.",
                    ]);
                }
            }
        }

        // Jika mode koreksi/update, periksa stok akhir seluruh barang terkait agar tidak minus
        if ($abaikanNo !== null) {
            $oldKodes = TrMutasiDt::where('NO_TRANSAKSI', $abaikanNo)->pluck('KODE_BARANG')->all();
            $allKodes = array_unique(array_merge($oldKodes, $kodes));
            $newItemMap = collect($data['items'])->keyBy('KODE_BARANG');

            foreach ($allKodes as $k) {
                $stokLuar = $this->stok($k, $abaikanNo);
                $qtyBaru = isset($newItemMap[$k]) ? (int) $newItemMap[$k]['QTY'] : 0;
                $delta = ($data['JENIS_TRANSAKSI'] === 'MASUK') ? $qtyBaru : -$qtyBaru;
                if ($stokLuar + $delta < 0) {
                    $mb = MsBarang::find($k);
                    $nama = $mb ? " ({$mb->NAMA_BARANG})" : '';
                    throw ValidationException::withMessages([
                        'items' => "Koreksi tidak dapat disimpan: stok barang $k$nama akan menjadi minus (" . ($stokLuar + $delta) . ").",
                    ]);
                }
            }
        }
    }

    /** Stok seluruh waktu (masuk - keluar), opsional mengabaikan satu transaksi (untuk koreksi/hapus). */
    private function stok(string $kode, ?string $abaikanNo = null): int
    {
        $q = DB::table('TRMUTASIDT as d')
            ->join('TRMUTASIHD as h', 'h.NO_TRANSAKSI', '=', 'd.NO_TRANSAKSI')
            ->where('d.KODE_BARANG', $kode);

        if ($abaikanNo !== null) {
            $q->where('h.NO_TRANSAKSI', '<>', $abaikanNo);
        }

        return (int) $q->selectRaw("COALESCE(SUM(CASE WHEN h.JENIS_TRANSAKSI = 'MASUK' THEN d.QTY ELSE -d.QTY END), 0) AS stok")
            ->value('stok');
    }

    private function simpanDetail(string $no, array $items): void
    {
        TrMutasiDt::insert(array_map(fn ($it) => [
            'NO_TRANSAKSI' => $no,
            'KODE_BARANG'  => $it['KODE_BARANG'],
            'QTY'          => (int) $it['QTY'],
        ], $items));
    }

    private function generateNo(): string
    {
        $max = TrMutasiHd::pluck('NO_TRANSAKSI')
            ->map(fn ($n) => (int) preg_replace('/\D/', '', $n))
            ->max() ?? 0;

        return 'T' . str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }
}
