@extends('layouts.app')
@section('title', 'Transaksi Barang')

@section('content')
<div class="app-head p-3 px-4 mb-3 d-flex align-items-center gap-3">
    <span class="fs-3">&#128230;</span>
    <h1 class="h4 m-0">Transaksi Barang</h1>
</div>

<div id="alertBox"></div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-semibold" for="NO_TRANSAKSI">No. Transaksi <span class="text-danger">*</span></label>
                <input id="NO_TRANSAKSI" class="form-control" maxlength="5" placeholder="Auto Generate / Input Manual" autocomplete="off">
                <div class="form-text">Kosongkan untuk auto (berikutnya: <b>{{ $nextNo }}</b>)</div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold" for="TANGGAL">Tanggal <span class="text-danger">*</span></label>
                <input id="TANGGAL" type="date" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold" for="JENIS_TRANSAKSI">Jenis Transaksi <span class="text-danger">*</span></label>
                <select id="JENIS_TRANSAKSI" class="form-select">
                    <option value="MASUK">MASUK</option>
                    <option value="KELUAR">KELUAR</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold" for="KATEGORI">Kategori <span class="text-danger">*</span></label>
                <select id="KATEGORI" class="form-select">
                    @foreach ($kategori as $k)
                        <option value="{{ $k }}">{{ $k }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <table class="table table-detail align-middle mb-2">
            <thead>
                <tr><th style="width:50px">#</th><th style="width:22%">Kode Barang</th><th>Nama Barang</th><th style="width:15%">Satuan</th><th style="width:15%">Qty</th></tr>
            </thead>
            <tbody id="detailBody"></tbody>
        </table>
        <div id="emptyBox" class="empty-box text-center">
            <div class="fs-2">&#128230;</div>Belum ada item
        </div>
        <div class="d-flex gap-2 pt-2 border-top">
            <button type="button" class="btn btn-primary btn-sm" id="btnAddItem">+ Tambah Item (F7)</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDelItem">Hapus Item</button>
            <span class="text-muted small ms-2 align-self-center">Centang baris lalu klik Hapus Item.</span>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mb-4">
    <button type="button" class="btn btn-primary px-4" id="btnTambah">Tambah</button>
    <button type="button" class="btn btn-outline-primary px-4" id="btnKoreksi" disabled>Koreksi</button>
    <button type="button" class="btn btn-outline-danger px-4" id="btnHapus" disabled>Hapus</button>
    <button type="button" class="btn btn-light ms-auto" id="btnBaru">Form Baru</button>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">Daftar Transaksi <span class="text-muted fw-normal small">(klik Pilih untuk Koreksi / Hapus)</span></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>No. Transaksi</th><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th class="text-end">Jml Item</th><th></th></tr></thead>
            <tbody>
            @forelse ($daftar as $d)
                <tr data-no="{{ $d->NO_TRANSAKSI }}">
                    <td>{{ $d->NO_TRANSAKSI }}</td>
                    <td>{{ \Carbon\Carbon::parse($d->TANGGAL)->format('d/m/Y') }}</td>
                    <td><span class="badge {{ $d->JENIS_TRANSAKSI === 'MASUK' ? 'text-bg-success' : 'text-bg-warning' }}">{{ $d->JENIS_TRANSAKSI }}</span></td>
                    <td>{{ $d->KATEGORI }}</td>
                    <td class="text-end">{{ $d->JML_ITEM }}</td>
                    <td class="text-end"><button class="btn btn-sm btn-outline-primary btn-pilih" data-no="{{ $d->NO_TRANSAKSI }}">Pilih</button></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const BARANG = @json($barang);
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const $ = id => document.getElementById(id);
let loadedNo = null; // terisi saat transaksi lama dimuat (mode Koreksi/Hapus)
let currentKategori = $('KATEGORI').value;

const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

function showAlert(msg, type = 'danger') {
    $('alertBox').innerHTML = `<div class="alert alert-${type} alert-dismissible" style="white-space:pre-line">${esc(msg)}<button type="button" class="btn-close" data-bs-dismiss="alert" onclick="this.parentElement.remove()"></button></div>`;
    window.scrollTo({top: 0, behavior: 'smooth'});
}

async function api(url, method, body) {
    const res = await fetch(url, {
        method,
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF},
        body: body ? JSON.stringify(body) : undefined,
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
        const errs = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'Terjadi kesalahan.');
        throw new Error(errs);
    }
    return data;
}

function barangByKategori() { return BARANG.filter(b => b.KATEGORI === $('KATEGORI').value); }

function refreshEmpty() { $('emptyBox').style.display = $('detailBody').children.length ? 'none' : 'block'; }

function renumber() {
    [...$('detailBody').children].forEach((tr, i) => tr.querySelector('.no').textContent = i + 1);
}

function addRow(kode = '', qty = 1) {
    const tr = document.createElement('tr');
    const opts = '<option value="">-- Pilih Barang --</option>' + barangByKategori()
        .map(b => `<option value="${esc(b.KODE_BARANG)}">${esc(b.KODE_BARANG)} - ${esc(b.NAMA_BARANG)} (${esc(b.SATUAN)})</option>`).join('');
    tr.innerHTML = `
        <td><div class="form-check"><input type="checkbox" class="form-check-input chk"> <span class="no"></span></div></td>
        <td><select class="form-select form-select-sm kode">${opts}</select></td>
        <td class="nama text-muted">-</td>
        <td class="satuan text-muted">-</td>
        <td><input type="number" min="1" step="1" class="form-control form-control-sm qty" value="${esc(qty)}"></td>`;
    const sel = tr.querySelector('.kode');
    sel.addEventListener('change', () => fillRow(tr));
    $('detailBody').appendChild(tr);
    sel.value = kode;
    fillRow(tr);
    renumber(); refreshEmpty();
}

function fillRow(tr) {
    const b = BARANG.find(x => x.KODE_BARANG === tr.querySelector('.kode').value);
    tr.querySelector('.nama').textContent = b ? b.NAMA_BARANG : '-';
    tr.querySelector('.satuan').textContent = b ? b.SATUAN : '-';
}

function collect() {
    return {
        NO_TRANSAKSI: $('NO_TRANSAKSI').value.trim().toUpperCase(),
        TANGGAL: $('TANGGAL').value,
        JENIS_TRANSAKSI: $('JENIS_TRANSAKSI').value,
        KATEGORI: $('KATEGORI').value,
        items: [...$('detailBody').children].map(tr => {
            const rawQty = tr.querySelector('.qty').value;
            const parsedQty = parseInt(rawQty, 10);
            return {
                KODE_BARANG: tr.querySelector('.kode').value,
                QTY: isNaN(parsedQty) ? '' : parsedQty,
            };
        }),
    };
}

function setMode(no) {
    loadedNo = no;
    $('btnTambah').disabled = !!no;
    $('btnKoreksi').disabled = !no;
    $('btnHapus').disabled = !no;
    $('NO_TRANSAKSI').readOnly = !!no;
    document.querySelectorAll('tr.picked').forEach(r => r.classList.remove('picked'));
}

function resetForm() {
    $('NO_TRANSAKSI').value = '';
    $('TANGGAL').value = new Date().toISOString().slice(0, 10);
    $('JENIS_TRANSAKSI').value = 'MASUK';
    $('detailBody').innerHTML = '';
    currentKategori = $('KATEGORI').value;
    setMode(null); refreshEmpty();
}

function done(msg) {
    sessionStorage.setItem('flash_success', msg);
    location.reload();
}

// Cek apakah ada flash message setelah reload
const flash = sessionStorage.getItem('flash_success');
if (flash) {
    showAlert(flash, 'success');
    sessionStorage.removeItem('flash_success');
}

$('btnAddItem').onclick = () => addRow();
document.addEventListener('keydown', e => { if (e.key === 'F7') { e.preventDefault(); addRow(); } });

$('btnDelItem').onclick = () => {
    const picked = [...$('detailBody').querySelectorAll('.chk:checked')].map(c => c.closest('tr'));
    if (!picked.length) return showAlert('Centang dulu baris item yang ingin dihapus.', 'warning');
    picked.forEach(tr => tr.remove());
    renumber(); refreshEmpty();
};

$('KATEGORI').onchange = () => {
    if ($('detailBody').children.length) {
        if (!confirm('Mengganti kategori akan mengosongkan daftar item. Lanjutkan?')) {
            $('KATEGORI').value = currentKategori;
            return;
        }
    }
    currentKategori = $('KATEGORI').value;
    $('detailBody').innerHTML = '';
    refreshEmpty();
};

$('btnTambah').onclick = async () => {
    try { const r = await api('{{ route('transaksi.store') }}', 'POST', collect()); done(r.message); }
    catch (e) { showAlert(e.message); }
};

$('btnKoreksi').onclick = async () => {
    if (!loadedNo) return;
    try { const r = await api(`/transaksi/${encodeURIComponent(loadedNo)}`, 'PUT', collect()); done(r.message); }
    catch (e) { showAlert(e.message); }
};

$('btnHapus').onclick = async () => {
    if (!loadedNo || !confirm(`Hapus transaksi ${loadedNo} beserta seluruh itemnya?`)) return;
    try { const r = await api(`/transaksi/${encodeURIComponent(loadedNo)}`, 'DELETE'); done(r.message); }
    catch (e) { showAlert(e.message); }
};

$('btnBaru').onclick = resetForm;

document.querySelectorAll('.btn-pilih').forEach(btn => btn.onclick = async () => {
    try {
        const d = await api(`/transaksi/${encodeURIComponent(btn.dataset.no)}`, 'GET');
        $('NO_TRANSAKSI').value = d.NO_TRANSAKSI;
        $('TANGGAL').value = d.TANGGAL;
        $('JENIS_TRANSAKSI').value = d.JENIS_TRANSAKSI;
        $('KATEGORI').value = d.KATEGORI;
        currentKategori = d.KATEGORI;
        $('detailBody').innerHTML = '';
        d.items.forEach(it => addRow(it.KODE_BARANG, it.QTY));
        setMode(d.NO_TRANSAKSI);
        btn.closest('tr').classList.add('picked');
        window.scrollTo({top: 0, behavior: 'smooth'});
    } catch (e) { showAlert(e.message); }
});

refreshEmpty();
</script>
@endpush
