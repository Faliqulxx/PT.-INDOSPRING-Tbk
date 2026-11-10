import os
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, Image, PageBreak, HRFlowable
)
from reportlab.pdfgen import canvas

class NumberedCanvas(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        super(NumberedCanvas, self).__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_decorations(num_pages)
            super(NumberedCanvas, self).showPage()
        super(NumberedCanvas, self).save()

    def draw_page_decorations(self, page_count):
        self.saveState()
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#64748b"))
        
        # Header (halaman > 1)
        if self._pageNumber > 1:
            self.drawString(45, 802, "PT Indospring Tbk - Test Skill IT Software | Faliqul Ishbah")
            self.drawRightString(550, 802, "Dokumentasi Teknis & Laporan Hasil")
            self.setStrokeColor(colors.HexColor("#cbd5e1"))
            self.setLineWidth(0.5)
            self.line(45, 794, 550, 794)

        # Footer
        footer_text = f"Halaman {self._pageNumber} dari {page_count}"
        self.drawRightString(550, 32, footer_text)
        self.drawString(45, 32, "Dokumen Pengumpulan Seleksi Magang Developer Support IT")
        self.setStrokeColor(colors.HexColor("#cbd5e1"))
        self.setLineWidth(0.5)
        self.line(45, 42, 550, 42)
        self.restoreState()

def build_pdf(output_paths):
    for filename in output_paths:
        doc = SimpleDocTemplate(
            filename,
            pagesize=A4,
            leftMargin=45,
            rightMargin=45,
            topMargin=46,
            bottomMargin=46
        )

        styles = getSampleStyleSheet()

        # Skema Warna Korporat
        primary_color = colors.HexColor("#1e40af")   # Deep Blue Indospring
        dark_color = colors.HexColor("#0f172a")      # Slate 900
        text_color = colors.HexColor("#334155")      # Slate 700
        bg_light = colors.HexColor("#f8fafc")        # Slate 50
        border_color = colors.HexColor("#cbd5e1")    # Slate 300
        accent_green = colors.HexColor("#16a34a")

        title_style = ParagraphStyle(
            'DocTitle',
            parent=styles['Normal'],
            fontName='Helvetica-Bold',
            fontSize=15,
            leading=19,
            textColor=dark_color,
            alignment=1,
            spaceAfter=8
        )

        h1_style = ParagraphStyle(
            'Heading1_Custom',
            parent=styles['Heading1'],
            fontName='Helvetica-Bold',
            fontSize=13,
            leading=17,
            textColor=dark_color,
            spaceBefore=4,
            spaceAfter=6,
            keepWithNext=True
        )

        h2_style = ParagraphStyle(
            'Heading2_Custom',
            parent=styles['Heading2'],
            fontName='Helvetica-Bold',
            fontSize=10,
            leading=13.5,
            textColor=dark_color,
            spaceBefore=7,
            spaceAfter=4,
            keepWithNext=True
        )

        body_style = ParagraphStyle(
            'Body_Custom',
            parent=styles['Normal'],
            fontName='Helvetica',
            fontSize=8.5,
            leading=12,
            textColor=text_color,
            spaceAfter=4
        )

        bullet_style = ParagraphStyle(
            'Bullet_Custom',
            parent=body_style,
            leftIndent=12,
            spaceAfter=3.5
        )

        caption_style = ParagraphStyle(
            'Caption_Custom',
            parent=styles['Normal'],
            fontName='Helvetica-BoldOblique',
            fontSize=7.8,
            leading=10.5,
            textColor=dark_color,
            alignment=0,
            spaceBefore=3,
            spaceAfter=6
        )

        code_style = ParagraphStyle(
            'Code_Custom',
            parent=styles['Normal'],
            fontName='Courier',
            fontSize=7,
            leading=9,
            textColor=colors.HexColor("#0f172a"),
        )

        table_header_style = ParagraphStyle(
            'TH',
            parent=styles['Normal'],
            fontName='Helvetica-Bold',
            fontSize=7.6,
            leading=10,
            textColor=colors.white,
            alignment=0
        )

        table_cell_style = ParagraphStyle(
            'TD',
            parent=styles['Normal'],
            fontName='Helvetica',
            fontSize=7.8,
            leading=10.5,
            textColor=text_color,
            alignment=0
        )

        table_cell_right = ParagraphStyle(
            'TDR',
            parent=table_cell_style,
            alignment=2
        )

        table_cell_center = ParagraphStyle(
            'TDC',
            parent=table_cell_style,
            alignment=1
        )

        story = []

        # ==================== HALAMAN 1: IDENTITAS & PEMETAAN ARSITEKTUR ====================
        story.append(Paragraph("TEST SKILL IT SOFTWARE - PT INDOSPRING TBK", title_style))
        story.append(Spacer(1, 4))

        # Kartu Identitas
        meta_data = [
            [
                Paragraph("<b>Nama Peserta</b>", body_style), Paragraph("Faliqul Ishbah", body_style),
                Paragraph("<b>Posisi Dilamar</b>", body_style), Paragraph("Developer Support IT", body_style)
            ],
            [
                Paragraph("<b>Framework Backend</b>", body_style), Paragraph("Laravel 10 (PHP 8.1+)", body_style),
                Paragraph("<b>Target Database</b>", body_style), Paragraph("Microsoft SQL Server (T-SQL)", body_style)
            ]
        ]
        t_meta = Table(meta_data, colWidths=[100, 150, 100, 155])
        t_meta.setStyle(TableStyle([
            ('PADDING', (0,0), (-1,-1), 4),
            ('BOX', (0,0), (-1,-1), 1, colors.black),
            ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor("#94a3b8")),
        ]))
        story.append(t_meta)
        story.append(Spacer(1, 7))

        # Source code repositori
        story.append(Paragraph(
            '<b>Source code repositori:</b> <a href="https://github.com/Faliqulxx/PT.-INDOSPRING-Tbk"><font color="#1e40af"><u>https://github.com/Faliqulxx/PT.-INDOSPRING-Tbk</u></font></a>',
            body_style
        ))
        story.append(Spacer(1, 5))

        # Catatan Lingkungan Pengujian & Status SQL Server
        catatan_box = [
            [
                Paragraph(
                    "<b>Status Verifikasi &amp; Lingkungan Database:</b><br/>"
                    "Aplikasi web telah dikonfigurasi, dimigrasikan, dan diuji penuh pada <b>Microsoft SQL Server 2025 Express</b> "
                    "(instance <code>SQLEXPRESS</code>, database <code>db_indospring_test</code>) dengan <b>PHP 8.1.25</b>, driver "
                    "<code>pdo_sqlsrv</code> 5.12.0, dan <b>Microsoft ODBC Driver 18 for SQL Server</b> (<code>DB_TRUST_SERVER_CERTIFICATE=true</code>). "
                    "Seluruh alur (input form, validasi aturan bisnis, relasi data, aksi Koreksi/Hapus, kalkulasi stok, dan laporan 3.1–3.3) "
                    "telah diverifikasi <b>lulus 100% di SQL Server</b>. Dukungan SQLite tetap tersedia sebagai alternatif via <code>.env</code>.",
                    body_style
                )
            ]
        ]
        t_catatan = Table(catatan_box, colWidths=[505])
        t_catatan.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#f8fafc")),
            ('PADDING', (0,0), (-1,-1), 5),
            ('BOX', (0,0), (-1,-1), 0.75, colors.HexColor("#cbd5e1")),
        ]))
        story.append(t_catatan)
        story.append(Spacer(1, 8))

        # Pemetaan File & Letak Code/Query
        story.append(Paragraph("<b>Daftar Lokasi File Kode, Logika Bisnis &amp; Query (Architecture Map)</b>", h2_style))
        file_map_data = [
            [Paragraph("<b>Komponen / Modul</b>", table_header_style), Paragraph("<b>Lokasi File</b>", table_header_style), Paragraph("<b>Fungsi &amp; Peran Utama</b>", table_header_style)],
            [Paragraph("<b>Model Database</b>", table_cell_style), Paragraph("app/Models/{MsBarang, TrMutasiHd, TrMutasiDt}.php", table_cell_style), Paragraph("Pemetaan tabel Eloquent ORM (non-incrementing, string PK).", table_cell_style)],
            [Paragraph("<b>Controller Transaksi</b>", table_cell_style), Paragraph("app/Http/Controllers/TransaksiController.php", table_cell_style), Paragraph("Logika CRUD mutasi, auto-numbering aman concurrency, validasi stok &amp; aturan bisnis.", table_cell_style)],
            [Paragraph("<b>Controller Laporan</b>", table_cell_style), Paragraph("app/Http/Controllers/LaporanController.php", table_cell_style), Paragraph("Pengambilan data laporan periode Jan–Feb 2021 via LaporanSql.", table_cell_style)],
            [Paragraph("<b>Query SQL Laporan</b>", table_cell_style), Paragraph("app/Support/LaporanSql.php &amp; sql/03_laporan.sql", table_cell_style), Paragraph("Single Source of Truth query T-SQL laporan (3.1, 3.2, 3.3).", table_cell_style)],
            [Paragraph("<b>Skema DDL &amp; Seed</b>", table_cell_style), Paragraph("database/migrations/ &amp; sql/01_schema.sql", table_cell_style), Paragraph("Penciptaan 3 tabel relasional dan seeding data MSBARANG via upsert().", table_cell_style)],
            [Paragraph("<b>Antarmuka Web</b>", table_cell_style), Paragraph("resources/views/{transaksi, laporan}/index.blade.php", table_cell_style), Paragraph("UI interaktif Bootstrap: shortcut F7, mode Koreksi/Hapus, AJAX fetch.", table_cell_style)],
        ]
        t_file_map = Table(file_map_data, colWidths=[105, 195, 205])
        t_file_map.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,0), colors.black),
            ('PADDING', (0,0), (-1,-1), 4),
            ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#94a3b8")),
        ]))
        story.append(t_file_map)

        # ==================== HALAMAN 2: SOAL 1 (SKEMA DATABASE & MASTER) ====================
        story.append(PageBreak())
        story.append(Paragraph("1. Skema Database &amp; Master Barang (Soal 1)", h1_style))
        story.append(Paragraph(
            "Struktur tabel dibuat persis sesuai spesifikasi soal dengan tipe data VARCHAR dan panjang karakter yang tepat. "
            "Relasi antar tabel dijaga menggunakan foreign key constraint, dengan composite primary key pada detail untuk mencegah duplikasi barang.",
            body_style
        ))
        story.append(Paragraph("<b>Letak file:</b> sql/01_schema.sql dan database/migrations/2026_11_10_000001_create_tabel_mutasi_barang.php", body_style))
        story.append(Spacer(1, 4))

        # Skrip SQL DDL
        story.append(Paragraph("<b>DDL T-SQL SQL Server (sql/01_schema.sql)</b>", h2_style))
        sql_schema_code = (
            "CREATE TABLE MSBARANG (\n"
            "    KODE_BARANG VARCHAR(5)  NOT NULL PRIMARY KEY,\n"
            "    NAMA_BARANG VARCHAR(50) NOT NULL,\n"
            "    SATUAN      VARCHAR(5)  NOT NULL,\n"
            "    KATEGORI    VARCHAR(10) NOT NULL\n"
            ");\n\n"
            "CREATE TABLE TRMUTASIHD (\n"
            "    NO_TRANSAKSI    VARCHAR(5)  NOT NULL PRIMARY KEY,\n"
            "    TANGGAL         DATE        NOT NULL,\n"
            "    JENIS_TRANSAKSI VARCHAR(10) NOT NULL,   -- 'MASUK' / 'KELUAR'\n"
            "    KATEGORI        VARCHAR(10) NOT NULL\n"
            ");\n\n"
            "CREATE TABLE TRMUTASIDT (\n"
            "    NO_TRANSAKSI VARCHAR(5) NOT NULL,\n"
            "    KODE_BARANG  VARCHAR(5) NOT NULL,\n"
            "    QTY          INT        NOT NULL,\n"
            "    PRIMARY KEY (NO_TRANSAKSI, KODE_BARANG),\n"
            "    FOREIGN KEY (NO_TRANSAKSI) REFERENCES TRMUTASIHD (NO_TRANSAKSI),\n"
            "    FOREIGN KEY (KODE_BARANG)  REFERENCES MSBARANG (KODE_BARANG)\n"
            ");"
        )
        t_schema = Table([[Paragraph(f"<pre>{sql_schema_code}</pre>", code_style)]], colWidths=[505])
        t_schema.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#f8fafc")),
            ('PADDING', (0,0), (-1,-1), 5),
            ('BOX', (0,0), (-1,-1), 0.75, colors.HexColor("#cbd5e1")),
        ]))
        story.append(t_schema)
        story.append(Spacer(1, 6))

        story.append(Paragraph("<b>Data Master MSBARANG (diisi langsung dari seeder database)</b>", h2_style))
        story.append(Paragraph("<b>Letak file:</b> database/seeders/MsBarangSeeder.php dan sql/02_seed_msbarang.sql", body_style))
        msbarang_rows = [
            [Paragraph("<b>KODE_BARANG</b>", table_header_style), Paragraph("<b>NAMA_BARANG</b>", table_header_style), Paragraph("<b>SATUAN</b>", table_header_style), Paragraph("<b>KATEGORI</b>", table_header_style)],
            [Paragraph("S001", table_cell_style), Paragraph("KLIP", table_cell_style), Paragraph("BOX", table_cell_style), Paragraph("SUBMAT", table_cell_style)],
            [Paragraph("S002", table_cell_style), Paragraph("RIVET", table_cell_style), Paragraph("PCS", table_cell_style), Paragraph("SUBMAT", table_cell_style)],
            [Paragraph("S003", table_cell_style), Paragraph("PALET", table_cell_style), Paragraph("PCS", table_cell_style), Paragraph("SUBMAT", table_cell_style)],
            [Paragraph("R001", table_cell_style), Paragraph("FLAT BAR", table_cell_style), Paragraph("KG", table_cell_style), Paragraph("RAWMAT", table_cell_style)],
            [Paragraph("R002", table_cell_style), Paragraph("STEEL WIRE", table_cell_style), Paragraph("KG", table_cell_style), Paragraph("RAWMAT", table_cell_style)],
        ]
        t_msb = Table(msbarang_rows, colWidths=[100, 185, 100, 120])
        t_msb.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,0), colors.black),
            ('PADDING', (0,0), (-1,-1), 4),
            ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#94a3b8")),
        ]))
        story.append(t_msb)
        story.append(Spacer(1, 6))

        story.append(Paragraph(
            "<b>Catatan integritas &amp; kompatibilitas seeder:</b> sesuai petunjuk soal (<i>Notes</i>), hanya tabel MSBARANG yang diisi data awal via "
            "seeder. Seeder menggunakan <code>upsert()</code> agar kompatibel dengan statement <code>MERGE</code> di SQL Server maupun <code>ON CONFLICT</code> di SQLite. "
            "Tabel TRMUTASIHD dan TRMUTASIDT dibiarkan kosong, dan seluruh data transaksi dimasukkan melalui antarmuka form web.",
            body_style
        ))

        # ==================== HALAMAN 3: SOAL 2 (FORM TRANSAKSI & FITUR TAMBAHAN) ====================
        story.append(PageBreak())
        story.append(Paragraph("2. Form Input Transaksi &amp; Fitur Tambahan (Soal 2)", h1_style))
        story.append(Paragraph("<b>Letak file:</b> app/Http/Controllers/TransaksiController.php dan resources/views/transaksi/index.blade.php", body_style))
        story.append(Paragraph(
            "Form transaksi dirancang dengan arsitektur Header–Detail dan eksekusi atomic (<code>DB::transaction</code>). "
            "Untuk memenuhi kebutuhan operasional gudang secara nyata, sistem dilengkapi sejumlah fitur tambahan berikut:",
            body_style
        ))

        # Fitur-fitur tambahan
        fitur_tambahan = [
            "<b>Tombol \"Koreksi\" (update transaksi lama).</b> Merevisi data nota yang pernah disimpan (misalnya salah Qty atau tanggal) tanpa mengubah nomor transaksi. Operator mengklik tombol \"Pilih\" pada tabel riwayat di bawah form; data lama termuat, No. Transaksi otomatis dikunci (read-only), tombol Tambah dinonaktifkan, dan tombol Koreksi menjadi aktif.",
            "<b>Tombol \"Hapus\" (delete transaksi dengan proteksi).</b> Menghapus seluruh nota beserta baris item detailnya. Dilengkapi dialog konfirmasi dan proteksi otomatis: jika penghapusan transaksi MASUK membuat stok fisik barang menjadi minus (karena barang sudah terpakai di transaksi KELUAR lain), penghapusan ditolak.",
            "<b>Tabel \"Daftar Transaksi\" di bawah form.</b> Soal asli tidak merinci letak daftar transaksi. Tabel riwayat ditambahkan langsung di bawah form, lengkap dengan badge jenis dan tombol \"Pilih\" agar operator dapat mengaktifkan mode Koreksi / Hapus dengan mudah.",
            "<b>Shortcut F7 &amp; Hapus Item.</b> Tombol F7 menambahkan baris barang secara instan tanpa mouse. Hapus Item bekerja dengan mencentang checkbox baris barang yang ingin dibuang.",
            "<b>Auto-generate nomor transaksi aman concurrency.</b> Jika kolom No. Transaksi dikosongkan, sistem membuat nomor urut alfanumerik (T001, T002, dst). Nomor transaksi ganda ditolak oleh validator. Pembuatan nomor diamankan di dalam transaksi dengan row locking (<code>lockForUpdate()</code>) di SQL Server.",
            "<b>Penyaringan otomatis kategori barang.</b> Dropdown barang pada baris detail hanya menampilkan barang yang kategorinya cocok dengan Kategori yang dipilih pada Header (mencegah salah pilih barang antar-divisi).",
            "<b>Proteksi stok minus pada transaksi KELUAR.</b> Transaksi KELUAR divalidasi real-time terhadap total saldo stok mutasi sebelumnya; jika kuantitas diminta lebih besar dari stok tersedia, transaksi ditolak."
        ]
        for ft in fitur_tambahan:
            story.append(Paragraph(f"• {ft}", bullet_style))
        story.append(Spacer(1, 5))

        # Data 5 Transaksi Contoh
        story.append(Paragraph("<b>Data 5 transaksi contoh yang diinputkan melalui form web</b>", h2_style))
        sample_tx_data = [
            [Paragraph("<b>No. Transaksi</b>", table_header_style), Paragraph("<b>Tanggal</b>", table_header_style), Paragraph("<b>Jenis</b>", table_header_style), Paragraph("<b>Kategori</b>", table_header_style), Paragraph("<b>Detail Barang &amp; Qty</b>", table_header_style)],
            [Paragraph("T001", table_cell_style), Paragraph("01/01/2021", table_cell_style), Paragraph("MASUK", table_cell_style), Paragraph("SUBMAT", table_cell_style), Paragraph("S001 = 10, S002 = 4, S003 = 6", table_cell_style)],
            [Paragraph("T003", table_cell_style), Paragraph("10/02/2021", table_cell_style), Paragraph("MASUK", table_cell_style), Paragraph("SUBMAT", table_cell_style), Paragraph("S002 = 6, S003 = 2", table_cell_style)],
            [Paragraph("T004", table_cell_style), Paragraph("10/01/2021", table_cell_style), Paragraph("MASUK", table_cell_style), Paragraph("RAWMAT", table_cell_style), Paragraph("R001 = 10, R002 = 5", table_cell_style)],
            [Paragraph("T002", table_cell_style), Paragraph("05/01/2021", table_cell_style), Paragraph("KELUAR", table_cell_style), Paragraph("SUBMAT", table_cell_style), Paragraph("S001 = 4, S002 = 1, S003 = 2", table_cell_style)],
            [Paragraph("T005", table_cell_style), Paragraph("25/01/2021", table_cell_style), Paragraph("KELUAR", table_cell_style), Paragraph("RAWMAT", table_cell_style), Paragraph("R001 = 5", table_cell_style)],
        ]
        t_stx = Table(sample_tx_data, colWidths=[75, 75, 65, 75, 215])
        t_stx.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,0), colors.black),
            ('PADDING', (0,0), (-1,-1), 3.5),
            ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#94a3b8")),
        ]))
        story.append(t_stx)

        # ==================== HALAMAN 4: SCREENSHOT GAMBAR 1 & GAMBAR 2 ====================
        story.append(PageBreak())
        story.append(Paragraph("Dokumentasi Visual Antarmuka Form &amp; Fitur Tambahan", h1_style))
        story.append(Spacer(1, 4))

        img1_path = "docs/screenshots/01_form_transaksi_terisi.png"
        img2_path = "docs/screenshots/04_mode_koreksi_transaksi.png"
        img3_path = "docs/screenshots/02_daftar_transaksi.png"
        img4_path = "docs/screenshots/03_halaman_laporan.png"

        if os.path.exists(img1_path):
            img1 = Image(img1_path, width=490, height=225)
            story.append(img1)
            story.append(Paragraph("<b>Gambar 1:</b> Form input baru saat input transaksi T001 (Header &amp; detail terisi lengkap).", caption_style))
        story.append(Spacer(1, 8))

        if os.path.exists(img2_path):
            img2 = Image(img2_path, width=490, height=225)
            story.append(img2)
            story.append(Paragraph("<b>Gambar 2:</b> Fitur mode Koreksi – No. Transaksi terkunci (read-only), tombol Koreksi &amp; Hapus aktif.", caption_style))

        # ==================== HALAMAN 5: SCREENSHOT GAMBAR 3 ====================
        story.append(PageBreak())
        if os.path.exists(img3_path):
            img3 = Image(img3_path, width=490, height=330)
            story.append(img3)
            story.append(Spacer(1, 4))
            story.append(Paragraph("<b>Gambar 3:</b> Fitur tambahan tabel riwayat transaksi di bawah form (menampilkan daftar 5 transaksi dan tombol Pilih untuk aksi Koreksi/Hapus).", caption_style))

        # ==================== HALAMAN 6: SOAL 3.1 ====================
        story.append(PageBreak())
        story.append(Paragraph("3. Laporan Mutasi &amp; Stok Periode Jan – Feb 2021 (Soal 3)", h1_style))
        story.append(Paragraph("<b>Letak file:</b> sql/03_laporan.sql dan kelas utilitas app/Support/LaporanSql.php", body_style))
        story.append(Spacer(1, 4))

        story.append(Paragraph("<b>3.1 Laporan Stok Barang (per item)</b>", h2_style))
        sql_31 = (
            "DECLARE @dari DATE = '2021-01-01';\n"
            "DECLARE @sampai DATE = '2021-02-28';\n\n"
            "WITH STOK AS (\n"
            "    SELECT b.KODE_BARANG, b.NAMA_BARANG, b.SATUAN, b.KATEGORI,\n"
            "           COALESCE(SUM(CASE WHEN h.JENIS_TRANSAKSI = 'MASUK'  THEN d.QTY END), 0) AS TRANS_MASUK,\n"
            "           COALESCE(SUM(CASE WHEN h.JENIS_TRANSAKSI = 'KELUAR' THEN d.QTY END), 0) AS TRANS_KELUAR\n"
            "    FROM MSBARANG b\n"
            "    LEFT JOIN TRMUTASIDT d ON d.KODE_BARANG = b.KODE_BARANG\n"
            "    LEFT JOIN TRMUTASIHD h ON h.NO_TRANSAKSI = d.NO_TRANSAKSI\n"
            "                          AND h.TANGGAL >= @dari AND h.TANGGAL < DATEADD(DAY, 1, @sampai)\n"
            "    GROUP BY b.KODE_BARANG, b.NAMA_BARANG, b.SATUAN, b.KATEGORI\n"
            ")\n"
            "SELECT KODE_BARANG, NAMA_BARANG, SATUAN, KATEGORI,\n"
            "       TRANS_MASUK, TRANS_KELUAR, TRANS_MASUK - TRANS_KELUAR AS STOK_AKHIR\n"
            "FROM STOK\n"
            "ORDER BY KATEGORI DESC, KODE_BARANG;"
        )
        t_sql31 = Table([[Paragraph(f"<pre>{sql_31}</pre>", code_style)]], colWidths=[505])
        t_sql31.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#f8fafc")),
            ('PADDING', (0,0), (-1,-1), 4.5),
            ('BOX', (0,0), (-1,-1), 0.75, colors.HexColor("#cbd5e1")),
        ]))
        story.append(t_sql31)
        story.append(Spacer(1, 6))

        res_31_data = [
            [Paragraph("<b>KODE_BARANG</b>", table_header_style), Paragraph("<b>NAMA_BARANG</b>", table_header_style), Paragraph("<b>SATUAN</b>", table_header_style), Paragraph("<b>KATEGORI</b>", table_header_style), Paragraph("<b>TRANS_MASUK</b>", table_header_style), Paragraph("<b>TRANS_KELUAR</b>", table_header_style), Paragraph("<b>STOK_AKHIR</b>", table_header_style)],
            [Paragraph("S001", table_cell_style), Paragraph("KLIP", table_cell_style), Paragraph("BOX", table_cell_style), Paragraph("SUBMAT", table_cell_style), Paragraph("10", table_cell_style), Paragraph("4", table_cell_style), Paragraph("6", table_cell_style)],
            [Paragraph("S002", table_cell_style), Paragraph("RIVET", table_cell_style), Paragraph("PCS", table_cell_style), Paragraph("SUBMAT", table_cell_style), Paragraph("10", table_cell_style), Paragraph("1", table_cell_style), Paragraph("9", table_cell_style)],
            [Paragraph("S003", table_cell_style), Paragraph("PALET", table_cell_style), Paragraph("PCS", table_cell_style), Paragraph("SUBMAT", table_cell_style), Paragraph("8", table_cell_style), Paragraph("2", table_cell_style), Paragraph("6", table_cell_style)],
            [Paragraph("R001", table_cell_style), Paragraph("FLAT BAR", table_cell_style), Paragraph("KG", table_cell_style), Paragraph("RAWMAT", table_cell_style), Paragraph("10", table_cell_style), Paragraph("5", table_cell_style), Paragraph("5", table_cell_style)],
            [Paragraph("R002", table_cell_style), Paragraph("STEEL WIRE", table_cell_style), Paragraph("KG", table_cell_style), Paragraph("RAWMAT", table_cell_style), Paragraph("5", table_cell_style), Paragraph("0", table_cell_style), Paragraph("5", table_cell_style)],
        ]
        t_res31 = Table(res_31_data, colWidths=[75, 95, 60, 75, 70, 70, 60])
        t_res31.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,0), colors.black),
            ('PADDING', (0,0), (-1,-1), 3.5),
            ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#94a3b8")),
        ]))
        story.append(t_res31)

        # ==================== HALAMAN 7: SOAL 3.2 & 3.3 ====================
        story.append(PageBreak())
        story.append(Paragraph("<b>3.2 Laporan Stok per Kelompok (Kategori &amp; Satuan)</b>", h2_style))
        story.append(Paragraph("Memakai CTE STOK dari query 3.1.", body_style))
        sql_32 = (
            "-- (CTE STOK sama seperti pada 3.1)\n"
            "SELECT KATEGORI, SATUAN,\n"
            "       SUM(TRANS_MASUK)  AS TRANS_MASUK,\n"
            "       SUM(TRANS_KELUAR) AS TRANS_KELUAR,\n"
            "       SUM(TRANS_MASUK) - SUM(TRANS_KELUAR) AS STOK_AKHIR\n"
            "FROM STOK\n"
            "GROUP BY KATEGORI, SATUAN\n"
            "ORDER BY KATEGORI DESC, SATUAN;"
        )
        t_sql32 = Table([[Paragraph(f"<pre>{sql_32}</pre>", code_style)]], colWidths=[505])
        t_sql32.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#f8fafc")),
            ('PADDING', (0,0), (-1,-1), 4),
            ('BOX', (0,0), (-1,-1), 0.75, colors.HexColor("#cbd5e1")),
        ]))
        story.append(t_sql32)
        story.append(Spacer(1, 4))

        res_32_data = [
            [Paragraph("<b>KATEGORI</b>", table_header_style), Paragraph("<b>SATUAN</b>", table_header_style), Paragraph("<b>TRANS_MASUK</b>", table_header_style), Paragraph("<b>TRANS_KELUAR</b>", table_header_style), Paragraph("<b>STOK_AKHIR</b>", table_header_style)],
            [Paragraph("SUBMAT", table_cell_style), Paragraph("BOX", table_cell_style), Paragraph("10", table_cell_style), Paragraph("4", table_cell_style), Paragraph("6", table_cell_style)],
            [Paragraph("SUBMAT", table_cell_style), Paragraph("PCS", table_cell_style), Paragraph("18", table_cell_style), Paragraph("3", table_cell_style), Paragraph("15", table_cell_style)],
            [Paragraph("RAWMAT", table_cell_style), Paragraph("KG", table_cell_style), Paragraph("15", table_cell_style), Paragraph("5", table_cell_style), Paragraph("10", table_cell_style)],
        ]
        t_res32 = Table(res_32_data, colWidths=[110, 85, 105, 105, 100])
        t_res32.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,0), colors.black),
            ('PADDING', (0,0), (-1,-1), 3.5),
            ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#94a3b8")),
        ]))
        story.append(t_res32)
        story.append(Spacer(1, 10))

        story.append(Paragraph("<b>3.3 Laporan Transaksi Keluar Tertinggi per Kelompok (Kategori &amp; Satuan)</b>", h2_style))
        story.append(Paragraph("Melanjutkan CTE STOK dari query 3.1 dengan CTE tambahan RANKED.", body_style))
        sql_33 = (
            "-- (CTE STOK sama seperti pada 3.1)\n"
            ", RANKED AS (\n"
            "    SELECT KATEGORI, SATUAN, KODE_BARANG, NAMA_BARANG, TRANS_KELUAR,\n"
            "           RANK() OVER (PARTITION BY KATEGORI, SATUAN ORDER BY TRANS_KELUAR DESC) AS RNK\n"
            "    FROM STOK\n"
            ")\n"
            "SELECT KATEGORI, SATUAN, KODE_BARANG, NAMA_BARANG, TRANS_KELUAR\n"
            "FROM RANKED\n"
            "WHERE RNK = 1\n"
            "ORDER BY KATEGORI DESC, SATUAN;"
        )
        t_sql33 = Table([[Paragraph(f"<pre>{sql_33}</pre>", code_style)]], colWidths=[505])
        t_sql33.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,-1), colors.HexColor("#f8fafc")),
            ('PADDING', (0,0), (-1,-1), 4),
            ('BOX', (0,0), (-1,-1), 0.75, colors.HexColor("#cbd5e1")),
        ]))
        story.append(t_sql33)
        story.append(Spacer(1, 4))

        res_33_data = [
            [Paragraph("<b>KATEGORI</b>", table_header_style), Paragraph("<b>SATUAN</b>", table_header_style), Paragraph("<b>KODE_BARANG</b>", table_header_style), Paragraph("<b>NAMA_BARANG</b>", table_header_style), Paragraph("<b>TRANS_KELUAR</b>", table_header_style)],
            [Paragraph("SUBMAT", table_cell_style), Paragraph("BOX", table_cell_style), Paragraph("S001", table_cell_style), Paragraph("KLIP", table_cell_style), Paragraph("4", table_cell_style)],
            [Paragraph("SUBMAT", table_cell_style), Paragraph("PCS", table_cell_style), Paragraph("S003", table_cell_style), Paragraph("PALET", table_cell_style), Paragraph("2", table_cell_style)],
            [Paragraph("RAWMAT", table_cell_style), Paragraph("KG", table_cell_style), Paragraph("R001", table_cell_style), Paragraph("FLAT BAR", table_cell_style), Paragraph("5", table_cell_style)],
        ]
        t_res33 = Table(res_33_data, colWidths=[90, 75, 90, 140, 110])
        t_res33.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,0), colors.black),
            ('PADDING', (0,0), (-1,-1), 3.5),
            ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor("#94a3b8")),
        ]))
        story.append(t_res33)

        # ==================== HALAMAN 8: SCREENSHOT LAPORAN & KESIMPULAN ====================
        story.append(PageBreak())
        story.append(Paragraph("Dokumentasi Fitur Tambahan: Halaman Web Laporan", h1_style))
        story.append(Paragraph("<b>Letak file:</b> app/Http/Controllers/LaporanController.php dan resources/views/laporan/index.blade.php", body_style))
        story.append(Paragraph(
            "Meskipun soal asli hanya meminta skrip query SQL, dibangun pula halaman web laporan interaktif "
            "di rute /laporan. Halaman ini memungkinkan manajemen atau staf operasional memeriksa stok "
            "secara dinamis dengan memasukkan tanggal awal dan tanggal akhir pada filter form.",
            body_style
        ))
        story.append(Spacer(1, 4))

        if os.path.exists(img4_path):
            img4 = Image(img4_path, width=490, height=275)
            story.append(img4)
            story.append(Paragraph("<b>Gambar 4:</b> Halaman web laporan stok barang (3.1, 3.2, 3.3) lengkap dengan filter periode tanggal (default 01/01/2021 s/d 28/02/2021).", caption_style))
        story.append(Spacer(1, 8))

        # Kesimpulan & Pernyataan Kesiapan Teknis
        story.append(Paragraph("<b>Kesimpulan &amp; Pernyataan Kesiapan Teknis</b>", h2_style))
        story.append(Paragraph(
            "1. Seluruh butir soal (1. Pembuatan Tabel, 2. Form Transaksi Masuk/Keluar, dan 3. Query Laporan 3.1–3.3) telah dipenuhi dan menghasilkan data yang sama dengan soal pengujian.<br/>"
            "2. Fitur tambahan yang dibangun (mode Koreksi, Hapus dengan proteksi stok minus, tabel daftar transaksi, auto-numbering aman concurrency, dan GUI web laporan) telah tersedia dalam aplikasi.<br/>"
            "3. Seluruh source code tersusun rapi, bersih dari kredensial/file sementara, dan dapat diakses publik pada repositori GitHub yang tertera.",
            body_style
        ))

        doc.build(story, canvasmaker=NumberedCanvas)
        print(f"PDF successfully built: {filename}")

if __name__ == "__main__":
    targets = [
        r"c:\Users\Faliq\Downloads\Tugas_Indospring_Faliqul_Ishbah.pdf",
        r"c:\Users\Faliq\Downloads\Tugas_Seleksi_Indospring_Faliqul_Ishbah.pdf",
        r"e:\maganghub\PT.-INDOSPRING-Tbk\Tugas_Indospring_Faliqul_Ishbah.pdf"
    ]
    build_pdf(targets)
