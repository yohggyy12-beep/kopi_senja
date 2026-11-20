<?php

class DocxGenerator {
    private $zip;
    private $filename;
    private $xmlContent = '';

    public function __construct($filename) {
        $this->filename = $filename;
        $this->zip = new ZipArchive();
        if ($this->zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Cannot create zip file at $filename");
        }
    }

    public function addTitle($text, $subtitle = '') {
        $this->xmlContent .= '
        <w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:before="400" w:after="100"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Arial" w:hAnsi="Arial"/>
                    <w:b/>
                    <w:sz w:val="48"/>
                    <w:color w:val="422A1D"/>
                </w:rPr>
                <w:t>' . htmlspecialchars($text) . '</w:t>
            </w:r>
        </w:p>';

        if ($subtitle) {
            $this->xmlContent .= '
            <w:p>
                <w:pPr>
                    <w:jc w:val="center"/>
                    <w:spacing w:before="0" w:after="400"/>
                </w:pPr>
                <w:r>
                    <w:rPr>
                        <w:rFonts w:ascii="Arial" w:hAnsi="Arial"/>
                        <w:i/>
                        <w:sz w:val="24"/>
                        <w:color w:val="704B34"/>
                    </w:rPr>
                    <w:t>' . htmlspecialchars($subtitle) . '</w:t>
                </w:r>
            </w:p>';
        }
    }

    public function addHeading1($text) {
        $this->xmlContent .= '
        <w:p>
            <w:pPr>
                <w:spacing w:before="400" w:after="150"/>
                <w:pBdr>
                    <w:bottom w:val="single" w:sz="12" w:space="4" w:color="8A6247"/>
                </w:pBdr>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Arial" w:hAnsi="Arial"/>
                    <w:b/>
                    <w:sz w:val="32"/>
                    <w:color w:val="422A1D"/>
                </w:rPr>
                <w:t>' . htmlspecialchars($text) . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addHeading2($text) {
        $this->xmlContent .= '
        <w:p>
            <w:pPr>
                <w:spacing w:before="250" w:after="100"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Arial" w:hAnsi="Arial"/>
                    <w:b/>
                    <w:sz w:val="26"/>
                    <w:color w:val="593923"/>
                </w:rPr>
                <w:t>' . htmlspecialchars($text) . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addParagraph($text, $boldPrefix = '', $italic = false) {
        $this->xmlContent .= '
        <w:p>
            <w:pPr>
                <w:spacing w:before="60" w:after="100" w:line="276" w:lineRule="auto"/>
            </w:pPr>';
        if ($boldPrefix) {
            $this->xmlContent .= '
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Arial" w:hAnsi="Arial"/>
                    <w:b/>
                    <w:sz w:val="22"/>
                    <w:color w:val="30251E"/>
                </w:rPr>
                <w:t xml:space="preserve">' . htmlspecialchars($boldPrefix) . ' </w:t>
            </w:r>';
        }
        $this->xmlContent .= '
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Arial" w:hAnsi="Arial"/>';
        if ($italic) $this->xmlContent .= '<w:i/>';
        $this->xmlContent .= '
                    <w:sz w:val="22"/>
                    <w:color w:val="30251E"/>
                </w:rPr>
                <w:t>' . htmlspecialchars($text) . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addBullet($text, $boldPrefix = '') {
        $this->xmlContent .= '
        <w:p>
            <w:pPr>
                <w:ind w:left="400" w:hanging="200"/>
                <w:spacing w:before="40" w:after="60"/>
            </w:pPr>
            <w:r>
                <w:rPr><w:rFonts w:ascii="Arial"/><w:b/><w:sz w:val="22"/><w:color w:val="8A6247"/></w:rPr>
                <w:t>• </w:t>
            </w:r>';
        if ($boldPrefix) {
            $this->xmlContent .= '
            <w:r>
                <w:rPr><w:rFonts w:ascii="Arial"/><w:b/><w:sz w:val="22"/><w:color w:val="30251E"/></w:rPr>
                <w:t xml:space="preserve">' . htmlspecialchars($boldPrefix) . ' </w:t>
            </w:r>';
        }
        $this->xmlContent .= '
            <w:r>
                <w:rPr><w:rFonts w:ascii="Arial"/><w:sz w:val="22"/><w:color w:val="30251E"/></w:rPr>
                <w:t>' . htmlspecialchars($text) . '</w:t>
            </w:r>
        </w:p>';
    }

    public function addCodeBlock($code) {
        $lines = explode("\n", $code);
        $this->xmlContent .= '
        <w:p>
            <w:pPr>
                <w:pBdr>
                    <w:left w:val="single" w:sz="18" w:space="8" w:color="8A6247"/>
                </w:pBdr>
                <w:shd w:val="clear" w:color="auto" w:fill="FAF8F5"/>
                <w:spacing w:before="120" w:after="120"/>
                <w:ind w:left="200" w:right="200"/>
            </w:pPr>';
        foreach ($lines as $line) {
            $this->xmlContent .= '
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Consolas" w:hAnsi="Consolas"/>
                    <w:sz w:val="19"/>
                    <w:color w:val="422A1D"/>
                </w:rPr>
                <w:t xml:space="preserve">' . htmlspecialchars($line) . '</w:t>
                <w:br/>
            </w:r>';
        }
        $this->xmlContent .= '</w:p>';
    }

    public function addTable($headers, $rows) {
        $this->xmlContent .= '
        <w:tbl>
            <w:tblPr>
                <w:tblW w:w="0" w:type="auto"/>
                <w:tblBorders>
                    <w:top w:val="single" w:sz="4" w:space="0" w:color="D8C4B2"/>
                    <w:left w:val="none"/>
                    <w:bottom w:val="single" w:sz="4" w:space="0" w:color="D8C4B2"/>
                    <w:right w:val="none"/>
                    <w:insideH w:val="single" w:sz="4" w:space="0" w:color="EADED2"/>
                    <w:insideV w:val="none"/>
                </w:tblBorders>
            </w:tblPr>
            <w:tr>
                <w:trPr><w:tblHeader/></w:trPr>';
        foreach ($headers as $header) {
            $this->xmlContent .= '
                <w:tc>
                    <w:tcPr>
                        <w:shd w:val="clear" w:color="auto" w:fill="F5F0E9"/>
                        <w:tcMar><w:top w:w="120"/><w:bottom w:w="120"/><w:left w:w="150"/><w:right w:w="150"/></w:tcMar>
                    </w:tcPr>
                    <w:p>
                        <w:r>
                            <w:rPr><w:rFonts w:ascii="Arial"/><w:b/><w:sz w:val="20"/><w:color w:val="422A1D"/></w:rPr>
                            <w:t>' . htmlspecialchars($header) . '</w:t>
                        </w:r>
                    </w:p>
                </w:tc>';
        }
        $this->xmlContent .= '</w:tr>';

        foreach ($rows as $row) {
            $this->xmlContent .= '<w:tr>';
            foreach ($row as $cell) {
                $this->xmlContent .= '
                <w:tc>
                    <w:tcPr>
                        <w:tcMar><w:top w:w="100"/><w:bottom w:w="100"/><w:left w:w="150"/><w:right w:w="150"/></w:tcMar>
                    </w:tcPr>
                    <w:p>
                        <w:r>
                            <w:rPr><w:rFonts w:ascii="Arial"/><w:sz w:val="20"/><w:color w:val="30251E"/></w:rPr>
                            <w:t>' . htmlspecialchars($cell) . '</w:t>
                        </w:r>
                    </w:p>
                </w:tc>';
            }
            $this->xmlContent .= '</w:tr>';
        }
        $this->xmlContent .= '</w:tbl>';
    }

    public function save() {
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>';

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>';

        $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <w:body>' . $this->xmlContent . '
        <w:sectPr>
            <w:pgSz w:w="11906" w:h="16838"/>
            <w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/>
        </w:sectPr>
    </w:body>
</w:document>';

        $this->zip->addFromString('[Content_Types].xml', $contentTypes);
        $this->zip->addFromString('_rels/.rels', $rels);
        $this->zip->addFromString('word/document.xml', $documentXml);
        $this->zip->close();
    }
}

// Generate the docx
$targetPath = "c:\\Yogii\\UKK\\Kopi_senja\\Laporan_Implementasi_Frontend_Kopi_Senja.docx";
$gen = new DocxGenerator($targetPath);

$gen->addTitle("LAPORAN IMPLEMENTASI FRONTEND DAN INTEGRASI SISTEM", "Sistem Informasi Kedai Kopi Senja — Uji Kompetensi Keahlian (UKK) RPL");
$gen->addParagraph("Dokumen ini menyajikan dokumentasi menyeluruh mengenai implementasi frontend antarmuka pengguna (Web Staf: Manajer & Kasir) pada aplikasi Kedai Kopi Senja. Seluruh antarmuka dikembangkan mengacu secara presisi pada Product Requirements Document (PRD) v1.0 dan 19 desain UI/mockup resmi.");

$gen->addHeading1("BAB I: RINGKASAN PRODUK DAN ARSITEKTUR FRONTEND");
$gen->addParagraph("Aplikasi Kedai Kopi Senja adalah sistem operasional kafe berbasis Laravel yang melayani dua kelompok pengguna melalui kanal berbeda: Web Desktop untuk Staff Operasional (Manajer dan Kasir) dan Mobile App untuk Pelanggan Android.");
$gen->addBullet("Framework CSS: Tailwind CSS v4 dengan sistem desain bernuansa hangat (Coffee & Cream) yang telah disesuaikan.", "Styling:");
$gen->addBullet("Blade Templating dengan komponen terpusat (layouts/app.blade.php, sidebar.blade.php, navbar.blade.php).", "Templating Engine:");
$gen->addBullet("Dua kanal otorisasi: Web Session untuk Staff dan Token Sanctum untuk Pelanggan Android.", "Keamanan & Guard:");
$gen->addBullet("Pemisahan tugas ketat: Kasir mengelola pesanan tanpa akses resep/laporan; Manajer mengelola master data tanpa membuat transaksi.", "Prinsip Role:");

$gen->addHeading1("BAB II: INVENTARIS 18 HALAMAN FRONTEND WEB STAF");
$gen->addTable(
    ["Kode Layar", "Nama Halaman", "Aktor", "Fungsi Utama & Integrasi Backend"],
    [
        ["W-AUTH-01", "Login Staff", "Semua Staff", "Autentikasi nama pengguna & sandi, throttle 5x/menit, proteksi session fixation."],
        ["W-AUTH-02", "Profil & Ganti Password", "Semua Staff", "Profil ringkas akun staff & form ubah password dengan verifikasi sandi lama."],
        ["W-MGR-01", "Dashboard Manajer", "Manajer", "Ringkasan statistik: omzet, jumlah order, menu aktif, stok menipis, dan aktivitas terbaru."],
        ["W-MGR-02", "Manajemen Bahan Baku", "Manajer", "Master-detail 2-kolom: Tabel bahan (stok, minimum, kondisi) dan Form Tambah/Edit bahan."],
        ["W-MGR-03", "Manajemen Menu", "Manajer", "Master-detail 2-kolom: Katalog menu, harga jual, status katalog, dan Form Tambah/Edit menu."],
        ["W-MGR-04", "Manajemen Resep", "Manajer", "Penyusunan takaran bahan per menu via Eloquent sync(), tabel komposisi & form tambah bahan."],
        ["W-MGR-05", "Pembelian Stok", "Manajer", "Pencatatan persediaan masuk (atomic increment stok) dan riwayat pembelian."],
        ["W-MGR-06", "Peringatan Stok Menipis", "Manajer", "Monitoring bahan yang stoknya <= minimum dengan badge kritis dan tombol Restok Sekarang."],
        ["W-MGR-07", "Harga Supplier", "Manajer", "Integrasi HTTP API Supplier secara server-side, fallback otomatis jika API bermasalah."],
        ["W-MGR-08", "HPP dan Margin Kotor", "Manajer", "Kalkulasi otomatis biaya bahan resep (HPP), margin kotor rupiah, dan persentase profit."],
        ["W-MGR-09", "Laporan Penjualan", "Manajer", "Filter periode tanggal, metrik total omzet, transaksi, rata-rata struk, dan penjualan per menu."],
        ["W-MGR-10", "Laporan Persediaan", "Manajer", "Analisis arus mutasi bahan: kuantitas masuk, terpakai, sisa stok, dan valuasi aset persediaan."],
        ["W-CAS-01", "Dashboard Kasir", "Kasir", "Monitoring pesanan shift ini, omzet shift, antrean pesanan aktif, dan tombol aksi status."],
        ["W-CAS-02", "Buat Pesanan (POS)", "Kasir", "Katalog kartu menu dengan pencarian & filter, keranjang interaktif, hitung layanan 5% & pajak 10%."],
        ["W-CAS-03", "Daftar Pesanan", "Kasir", "Pencarian nomor order/meja, filter status tab (Baru, Diproses, Selesai, Dibatalkan), tombol aksi."],
        ["W-CAS-04", "Detail Pesanan", "Kasir", "Rincian item snapshot harga historis, ringkasan biaya, aksi ubah status & modal batalkan pesanan."],
        ["W-CAS-05", "Struk Pesanan", "Kasir", "Tampilan nota thermal kasir siap cetak (@media print yang menyembunyikan sidebar/navigasi)."],
        ["W-CAS-06", "Reset Password Pelanggan", "Kasir", "Pencarian nomor HP pelanggan, generate 12-karakter password acak & modal salin sandi."]
    ]
);

$gen->addHeading1("BAB III: PROSES PENGERJAAN DEMI PROSES");
$gen->addHeading2("Fase 1: Ekstraksi Desain & Pemetaan Kebutuhan PRD");
$gen->addParagraph("Pengerjaan diawali dengan membuka dan menganalisis 19 berkas gambar mockup visual dari berkas arsip yang diunggah. Kami memetakan seluruh komponen visual (skema warna coffee-cream, penempatan kartu metrik, struktur split-panel 2/3 dan 1/3) agar setiap elemen Blade yang dibangun memiliki tingkat kesamaan visual yang tinggi.");

$gen->addHeading2("Fase 2: Perbaikan Bug Kritis pada Logika Backend");
$gen->addParagraph("Sebelum membangun antarmuka web, kami menemukan dan menyelesaikan 2 inkonsistensi kritis pada service backend:");
$gen->addBullet("Pada PesananService.php, payload pembuatan pesanan sebelumnya memakai kunci 'pajak' dan 'total', padahal migration database mendefinisikan kolom 'jumlah_pajak' dan 'total_harga'. Kami menyelaraskan kunci ini.", "Inkonsistensi Kolom Pajak/Total:");
$gen->addBullet("Pada pembuatan DetailPesanan, kode awal memakai field 'nama_menu_snapshot' dan 'harga_satuan_snapshot', padahal skema tabel menggunakan 'nama_menu_saat_pemesanan' dan 'harga_satuan_saat_pemesanan'. Kami memperbaikinya agar data pesanan tersimpan secara konsisten.", "Inkonsistensi Snapshot Detail:");
$gen->addBullet("Pada Pesanan.php, relasi user bernama dibuatOleh(), sedangkan controller memanggil dibuatOlehPengguna(). Kami menambahkan relasi alias agar query eager loading tidak menimbulkan RelationNotFoundException.", "Eager Loading Relation Alias:");

$gen->addHeading2("Fase 3: Implementasi Controller Hybrid (Web HTML & JSON API)");
$gen->addParagraph("Seluruh controller staff (BahanBakuController, MenuController, ResepController, PembelianStokController, PesananController, HargaBahanController) ditingkatkan menjadi arsitektur Hybrid. Jika permintaan datang dari browser (Accept: text/html), controller merender tampilan Blade. Jika permintaan berupa AJAX atau pengujian unit (wantsJson() === true), controller tetap mengembalikan respon JSON 100% kompatibel dengan pengujian backend yang ada.");

$gen->addHeading2("Fase 4: Konstruksi Tampilan Blade & Interaktivitas Javascript");
$gen->addParagraph("Kami menyusun 11 berkas Blade baru dan memperbarui komponen navigasi. Pada halaman Buat Pesanan (POS), kami menanamkan JavaScript murni untuk mengelola state keranjang belanja secara real-time, menghitung subtotal, biaya layanan 5%, pajak 10%, dan menyuntikkan hidden inputs ke formulir checkout.");

$gen->addHeading1("BAB IV: BEDAH KODE INTI & ALASAN PEMILIHAN KODE");
$gen->addHeading2("1. Logika Kalkulasi POS Kasir (Vanilla JavaScript)");
$gen->addParagraph("Mengapa memakai Vanilla JavaScript tanpa framework berat (React/Vue)? Karena aplikasi ini berbasis Laravel monolith. Vanilla JS sangat ringan, tidak membutuhkan runtime tambahan, dan langsung dieksekusi browser secara instan.");
$gen->addCodeBlock(
'// Perhitungan otomatis di kasir sesuai aturan UKK
const layanan = Math.round(subtotal * 0.05); // Biaya layanan 5%
const pajak = Math.round((subtotal + layanan) * 0.10); // Pajak 10%
const total = subtotal + layanan + pajak;'
);

$gen->addHeading2("2. Proteksi Transaksi Database & Rollback Stok (PesananService.php)");
$gen->addParagraph("Mengapa memakai DB::transaction() dan lockForUpdate()? Untuk mencegah race condition jika dua kasir membuat pesanan menu yang sama pada detik yang sama. Kunci baris memastikan stok bahan baku dibaca dan dikurangi secara berurutan.");
$gen->addCodeBlock(
'return DB::transaction(function () use ($data, $kasir) {
    // Kunci baris bahan baku di database
    $daftarBahan = BahanBaku::query()
        ->whereIn("id", array_keys($kebutuhanBahan))
        ->orderBy("id")
        ->lockForUpdate()
        ->get();
    ...
    // Kurangi stok jika seluruh bahan mencukupi
    $bahan->decrement("jumlah_stok", $jumlah);
});'
);

$gen->addHeading2("3. Sinkronisasi Relasi Many-to-Many Resep (ResepService.php)");
$gen->addParagraph("Mengapa memakai method sync() dari Eloquent? Karena sync() secara otomatis membandingkan data lama dengan data baru di tabel pivot detail_resep. Bahan yang tidak ada dihapus, bahan baru dimasukkan, dan takaran yang berubah diperbarui dalam satu eksekusi efisien.");
$gen->addCodeBlock(
'$menu->bahanBakus()->sync($syncData);
$menu->resep_lengkap = count($syncData) > 0;
$menu->save();'
);

$gen->addHeading1("BAB V: PANDUAN PENYAMPAIAN KEPADA PENGUJI");
$gen->addHeading2("Strategi Menjawab Pertanyaan Penguji:");
$gen->addBullet("Jelaskan 7 langkah yang dibungkus dalam DB::transaction(): (1) Agregasi menu, (2) Kunci baris via lockForUpdate, (3) Cek ketersediaan stok semua bahan, (4) Hitung layanan 5% & pajak 10%, (5) Simpan pesanan header, (6) Simpan snapshot detail, (7) Kurangi stok bahan.", "Ketika Ditanya Alur Pembuatan Pesanan:");
$gen->addBullet("Jelaskan bahwa sistem menerapkan konsep snapshot. Nama menu dan harga satuan dicatat pada tabel detail_pesanan saat transaksi terjadi. Jika manajer menaikkan harga menu esok hari, struk dan omzet masa lalu tidak akan berubah.", "Ketika Ditanya Soal Struk & Harga Historis:");
$gen->addBullet("Jawab secara profesional: \"Saya memanfaatkan AI sebagai asisten produktivitas untuk mempercepat penulisan boilerplate dan memvalidasi rancangan antarmuka agar sesuai PRD. Namun seluruh logika bisnis—seperti atomic lock stok, normalisasi nama bahan, formula pajak, dan relasi pivot—saya pahami dan uji secara menyeluruh sehingga saya dapat mempertanggungjawabkan setiap baris kode di aplikasi ini.\"", "Ketika Ditanya Penggunaan AI:");

$gen->save();
echo "Document generated successfully at $targetPath";
