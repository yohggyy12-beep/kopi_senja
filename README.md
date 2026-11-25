# Sistem Informasi Kedai Kopi Senja

## 1. Ringkasan

Sistem Informasi Kedai Kopi Senja merupakan aplikasi yang dibuat untuk membantu kegiatan operasional kedai kopi, terutama dalam mengelola menu, resep, bahan baku, pesanan, dan laporan.

Pada versi yang dikerjakan, aplikasi web desktop digunakan oleh dua peran, yaitu Manajer dan Kasir. Manajer bertugas mengelola data menu, resep, bahan baku, pembelian stok, batas minimum stok, serta melihat laporan. Kasir bertugas membuat pesanan, melihat daftar menu, mengubah status pesanan, membatalkan pesanan yang masih berstatus Baru, dan membantu reset password pelanggan.

Sistem menggunakan resep untuk menghitung kebutuhan bahan setiap menu. Ketika pesanan dibuat, sistem memeriksa ketersediaan bahan berdasarkan seluruh item dalam pesanan. Jika stok mencukupi, stok bahan dikurangi sesuai takaran resep dan jumlah porsi. Jika stok tidak mencukupi, pesanan ditolak agar stok tidak menjadi minus. Manajer juga dapat melihat HPP, margin, laporan penjualan, dan bahan yang stoknya menipis.

untuk kondisi saat ini berfokus pada aplikasi web untuk Manajer dan Kasir. Aplikasi Android (APK) untuk Pelanggan belum dibuat. Beberapa endpoint REST API untuk autentikasi pelanggan sudah tersedia, tetapi fitur pemesanan dan pemantauan pesanan melalui APK belum tersedia.

## 2. Teknologi

Teknologi yang digunakan pada proyek ini adalah:

1. Bahasa backend | PHP 8.3 | Menjalankan logika aplikasi di sisi server. |
2. Framework backend | Laravel 13 | Mengatur route, controller, model, validasi, autentikasi, dan proses bisnis. |
3. Tampilan web | Blade | Membuat halaman web untuk Manajer dan Kasir. |
4. CSS | Tailwind CSS 4 | Mengatur tampilan dan tata letak halaman web. |
5. Build frontend | Vite 7 | Menyiapkan aset CSS dan JavaScript. |
6. Database | MySQL | Menyimpan data pengguna, menu, resep, bahan baku, pesanan, dan transaksi stok. |
7. Pengujian | PHPUnit 12 | Menjalankan pengujian otomatis pada aplikasi. |

APK Android untuk pelanggan belum dikembangkan, sehingga teknologi APK belum digunakan.

## 3. Cara Menjalankan Server

Petunjuk berikut disusun untuk Windows menggunakan PowerShell. Pastikan perangkat sudah memiliki:

- PHP 8.3 atau versi yang sesuai dengan kebutuhan proyek.
- Composer.
- Node.js dan NPM.
- MySQL dan aplikasi untuk mengelola database, misalnya phpMyAdmin.
- Git, jika proyek diambil melalui repositori.

### 3.1. Mengambil dan memasang dependensi proyek

Buka PowerShell, masuk ke folder tempat proyek disimpan, lalu jalankan:

```powershell
cd C:\Yogii\UKK\Kopi_senja
composer install
npm install
```

Jika proyek diambil dari repositori, sesuaikan perintah `cd` dengan lokasi folder hasil clone.

### 3.2. Menyiapkan file konfigurasi

Buat file `.env` dari file contoh:

```powershell
- Copy-Item .env.example .env
```

Buka file `.env`, kemudian sesuaikan konfigurasi aplikasi dan database. Contoh konfigurasi MySQL:

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:9ODBG1JPeEUPrG/5AZj7uE1L4d69iCW8k6iYtLB2mMo=
APP_DEBUG=true
SESSION_DRIVER=file
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kopi_senja
DB_USERNAME=root
DB_PASSWORD=
```

Buat database bernama `kopi_senja` terlebih dahulu melalui MySQL. Apabila nama database, username, atau password MySQL berbeda, ubah nilainya sesuai konfigurasi komputer yang digunakan.

Buat application key Laravel:

```powershell
php artisan key:generate
```
### 3.3. Mengatur koneksi Supplier

Aplikasi mengambil harga bahan dari layanan Supplier melalui server Laravel. Tambahkan konfigurasi berikut ke file `.env`:

```env
SUPPLIER_API_URL=https://ukksmkn1katapang.next-it.co.id/kafe
SUPPLIER_API_KEY=lVWMc6V2SXhkeXjRMioax61WUuRhy2VW
```
Setelah mengubah konfigurasi, jalankan:

```powershell
php artisan config:clear
```

Jika layanan Supplier tidak dapat dihubungi atau API key tidak valid, aplikasi dirancang menggunakan harga terakhir yang tersimpan di database beserta tanggal pembaruannya.

### 3.4. Menjalankan migration

Setelah database dikonfigurasi dan dibuat, jalankan migration untuk membuat tabel:

```powershell
php artisan migrate
```

### 3.5. Mengimpor data awal CSV

Data awal bahan baku, menu, dan resep berada di folder:

```text
database/import/original/
```

File utama yang digunakan oleh perintah import:

- `bahan.csv`
- `menu.csv`
- `resep.csv`

Jalankan perintah import berikut setelah migration selesai:

```powershell
php artisan kedai:import-csv
```

Perintah tersebut membaca file CSV dari folder `database/import/original/`, lalu menyimpan data bahan baku, menu, dan resep ke database. Hasil normalisasi disimpan di `database/import/normalized/`, sedangkan baris yang dilewati dicatat di `database/import/reports/import-report.csv`.

Jalankan import pada database yang sudah disiapkan untuk data awal. Jangan mengubah file CSV sumber sebelum proses import tanpa memastikan format kolomnya tetap sesuai dengan perintah import.

### 3.6. Menyiapkan akun uji

Jalankan seeder akun:

```powershell
php artisan db:seed
```

`DatabaseSeeder` akan menjalankan `AkunSeeder` untuk membuat akun uji Manajer, Kasir, dan satu akun Pelanggan.

### 3.7. Menjalankan seeder data demo

Setelah data awal CSV berhasil diimpor, jalankan seeder data demo:

```powershell
php artisan db:seed --class=DemoStateSeeder
```
Seeder ini menetapkan stok Susu UHT menjadi 300 ml (0,3 liter) untuk keadaan awal demo. Seeder ini terpisah dari proses import CSV.

### 3.8. Menyiapkan aset dan menjalankan aplikasi

Untuk menyiapkan aset frontend:

```powershell
npm run build
```

Kemudian jalankan server Laravel:

```powershell
php artisan serve
```

Buka alamat berikut melalui browser:

```text
http://127.0.0.1:8000
```

Untuk pengembangan frontend, Vite dapat dijalankan di terminal PowerShell terpisah:

```powershell
npm run dev
```

Untuk menjalankan pengujian otomatis yang tersedia di proyek:

```powershell
php artisan test
```

Perintah tersebut menjalankan kumpulan pengujian yang ada. Hasilnya perlu diperiksa pada komputer yang digunakan sebelum menyatakan seluruh pengujian berhasil.

## 4. Cara Memasang APK

Belum dikerjakan. Aplikasi Android untuk Pelanggan belum dibuat, sehingga belum ada file APK di folder `/apk` yang dapat dipasang pada perangkat Android.
Endpoint untuk membuat pesanan pelanggan, melihat status pesanan, dan melihat struk melalui aplikasi Android belum tersedia. Karena APK belum dibuat, sehingga alamat server untuk APK dan langkah pemasangan APK belum dapat diberikan.

## 5. Akun Uji

Akun berikut dibuat oleh `AkunSeeder` dan digunakan untuk pengujian lokal. Ganti atau sesuaikan kredensial ini apabila data akun di database sudah diubah.

| Peran | Username | Password sementara untuk uji | Keterangan |
| Manajer | `manager` | `Manajer#123` | Login melalui halaman web. |
| Kasir | `kasir` | `Kasir#124` | Login melalui halaman web. |

Akun tersebut hanya untuk kebutuhan pengujian lokal. Jangan menggunakan password contoh ini untuk akun produksi.

## 6. Letak Berkas

Lokasi file penting dalam proyek:

| Kebutuhan | Lokasi |
| Migration database | `database/migrations/` |
| Model | `app/Models/` |
| Controller | `app/Http/Controllers/` |
| Validasi Form Request | `app/Http/Requests/` |
| Service / logika proses bisnis | `app/Services/` |
| Route web | `routes/web.php` |
| Route API | `routes/api.php` |
| Perintah import CSV | `app/Console/Commands/ImportKedaiCsv.php` |
| Normalisasi data CSV | `app/Support/Import/CsvValueNormalizer.php` |
| Konfigurasi lokasi CSV dan alias bahan | `config/import.php` |
| Data CSV asli | `database/import/original/` |
| Hasil CSV yang dinormalisasi | `database/import/normalized/` |
| Laporan hasil import | `database/import/reports/import-report.csv` |
| Seeder akun | `database/seeders/AkunSeeder.php` |
| Seeder utama | `database/seeders/DatabaseSeeder.php` |
| Seeder kondisi demo | `database/seeders/DemoStateSeeder.php` |
| Konfigurasi layanan Supplier | `config/services.php` |

## 7. Keputusan Anda

Beberapa keputusan   teknis yang digunakan dalam proyek ini adalah sebagai berikut.

### 7.1. Format nomor telepon pelanggan

Nomor telepon pelanggan hanya menerima angka dengan panjang 10 sampai 20 digit. Nomor yang mengandung huruf atau karakter selain angka ditolak. Nomor telepon juga harus unik agar tidak digunakan oleh dua akun pelanggan.

### 7.2. Penyeragaman nama bahan

Nama bahan dinormalisasi dengan menghapus spasi di awal dan akhir, mengubah huruf menjadi huruf kecil, serta merapikan spasi berulang. Dengan cara ini, nama yang hanya berbeda huruf besar/kecil atau spasi dapat dikenali sebagai nama yang sama.

### 7.3. Penyeragaman satuan bahan

Satuan bahan diubah ke satuan dasar agar perhitungan stok dan resep konsisten:

Aturan konversinya adalah sebagai berikut:
1. Kilogram (kg) menjadi gram (g). Setiap 1 kg dihitung sebagai 1.000 g. Contohnya, 2 kg menjadi 2.000 g.
2. Liter atau l menjadi mililiter (ml). Setiap 1 liter dihitung sebagai 1.000 ml. Contohnya, 1,5 liter menjadi 1.500 ml.
3. Gram (g) tetap gram (g). Jumlah bahan tidak perlu dikonversi.
4. Mililiter (ml) tetap mililiter (ml). Jumlah bahan tidak perlu dikonversi.
5. Pieces (pcs) tetap pcs. Jumlah bahan tidak perlu dikonversi.

Harga bahan juga disesuaikan menjadi harga per satuan dasar dengan membagi harga awal sesuai faktor konversi.

### 7.4. Pencocokan nama bahan pada resep

Pencocokan nama bahan resep menggunakan nama yang sudah dinormalisasi dan daftar alias di -
 `config/import.php`. Pada konfigurasi saat ini, `biji kopi arabica` dicocokkan dengan `biji kopi arabika`.

Apabila bahan pada resep tidak ditemukan di data bahan baku, baris resep tersebut dilewati dan dicatat dalam laporan import. Menu yang resepnya tidak lengkap ditandai agar tidak dianggap mempunyai resep lengkap.

### 7.5. Batas minimum stok

Batas minimum stok awal ditetapkan menjadi 0 untuk setiap bahan setelah proses import. Manajer dapat mengubah batas minimum tersebut melalui fitur pengelolaan stok.

### 7.6. Kondisi stok demo

Seeder data demo menetapkan stok Susu UHT menjadi 300 ml. Nilai ini ditetapkan, bukan ditambahkan, sehingga menjalankan seeder kembali tidak terus menambah jumlah stok.

### 7.7. Harga dari Supplier

Harga bahan diperbarui melalui layanan Supplier dari sisi server dengan header `X-API-Key`. Apabila layanan gagal dihubungi, API key ditolak, atau respons tidak sesuai, aplikasi menggunakan harga terakhir yang sudah tersimpan di database dan menampilkan informasi tanggal pembaruan yang tersedia.

## 8. Lembar Self-Check

Tabel berikut mengikuti 14 butir penerimaan pada dokumen UKK. Kolom klaim perlu disesuaikan setelah seluruh pengujian UAT dilakukan. Status `Perlu diverifikasi` bukan klaim Ya; ubah menjadi `Ya` hanya jika seluruh langkah pengujian berhasil, dan menjadi `Tidak` jika belum terpenuhi.

## 1. Pengelolaan Bahan Baku, Menu, dan Resep
- Klaim: Ya.
- Langkah uji: Login sebagai Manajer, kemudian coba menambahkan dan mengubah data bahan baku, menu, serta resep yang terdiri dari beberapa bahan dengan takaran masing-masing. Coba juga memasukkan nama bahan yang berbeda huruf besar atau kecil maupun spasi untuk memastikan nama bahan tidak dapat didaftarkan lebih dari satu kali.

## 2. Pencatatan Stok Masuk dan Batas Minimum Bahan
- Klaim: Ya.
- Langkah uji: Login sebagai Manajer, catat pembelian bahan baku untuk menambah stok. Setelah itu, ubah batas minimum salah satu bahan dan periksa apakah daftar bahan menipis menyesuaikan batas minimum yang telah ditentukan.

## 3. Pemesanan dengan Beberapa Menu dan Pengurangan Stok
- Klaim: Ya.
- Langkah uji: Login sebagai Kasir, buat pesanan yang berisi beberapa menu. Periksa apakah stok setiap bahan berkurang sesuai takaran resep dikalikan jumlah porsi yang dipesan. Coba juga memesan menu yang belum memiliki resep dan menu yang berstatus nonaktif.

## 4. Pemeriksaan Ketersediaan Stok untuk Seluruh Item Pesanan (Butir Inti)
- Klaim: Ya.
- Langkah uji: Siapkan beberapa menu yang menggunakan bahan baku yang sama. Atur stok bahan agar tidak mencukupi kebutuhan gabungan seluruh menu, kemudian buat pesanan. Pastikan sistem menolak pesanan dan tidak mengubah jumlah stok bahan.

## 5. Status Menu Habis
- Klaim: Ya.
- Langkah uji: Kurangi jumlah stok bahan hingga tidak mencukupi kebutuhan untuk membuat satu porsi menu. Periksa apakah menu tersebut otomatis ditampilkan dengan status Habis dan tidak dapat dipesan.

## 6. Perubahan Status dan Pembatalan Pesanan
- Klaim: Ya.
- Langkah uji: Buat pesanan sebagai Kasir, kemudian uji perubahan status dari Baru menjadi Diproses dan selanjutnya menjadi Selesai. Coba melakukan perubahan status secara langsung dari Baru ke Selesai untuk memastikan sistem menolaknya. Setelah itu, buat pesanan baru dan batalkan ketika statusnya masih Baru. Pastikan stok bahan dikembalikan dan tidak bertambah kembali apabila pembatalan dilakukan lebih dari satu kali.

## 7. Perhitungan Biaya dan Struk Pesanan (Butir Inti)
- Klaim: Ya.
- Langkah uji: Buat pesanan dan periksa rincian struk yang menampilkan subtotal, service charge sebesar 5% dari subtotal, pajak sebesar 10% dari subtotal ditambah service charge, serta total pembayaran. Kemudian ubah harga jual salah satu menu dan buka kembali struk pesanan sebelumnya. Pastikan nilai pada struk lama tidak ikut berubah.

## 8. Perhitungan HPP dan Margin Menu
- Klaim: Ya.
- Langkah uji: Login sebagai Manajer, buka informasi HPP dan margin menu. Periksa apakah HPP dihitung dari jumlah takaran setiap bahan dalam resep dikalikan harga bahan per satuan dasar. Pastikan margin dihitung dari harga jual menu dikurangi HPP.

## 9. Laporan dan Pembatasan Hak Akses Pengguna
- Klaim: Ya.
- Langkah uji: Login sebagai Manajer dan periksa laporan penjualan per menu, daftar bahan menipis, serta margin setiap menu. Selanjutnya, login sebagai Kasir dan coba membuka halaman Manajer melalui alamat URL secara langsung. Pastikan akses ditolak. Periksa juga bahwa Kasir hanya dapat melihat daftar menu tanpa informasi resep, HPP, dan margin. Pastikan Manajer tidak dapat membuat pesanan maupun mengubah status pesanan.

## 10. Import dan Penanganan Data Awal CSV
- Klaim: Ya.
- Langkah uji: Jalankan proses import data awal CSV. Periksa apakah satuan bahan sudah diubah ke satuan dasar, nama bahan yang salah ketik pada data resep dapat dicocokkan dengan bahan yang benar, bahan yang tidak terdaftar tidak ikut diimpor, dan batas minimum setiap bahan ditetapkan menjadi 0.

## 11. Integrasi Supplier dan Penggunaan Harga Terakhir
- Klaim: Ya.
- Langkah uji: Atur alamat layanan Supplier dan API key yang benar, kemudian jalankan proses pembaruan harga bahan. Setelah itu, ubah alamat layanan atau API key menjadi tidak valid dan coba jalankan kembali proses pembaruan. Pastikan aplikasi tetap dapat menggunakan harga terakhir yang tersimpan di database dan menampilkan tanggal pembaruan harga tersebut.

## 12. Akun Pelanggan melalui APK
- Klaim: Tidak.
- Langkah uji: Fitur pendaftaran, login, penggantian password, reset password oleh Kasir, kewajiban mengganti password sementara saat login berikutnya, dan logout pelanggan melalui APK belum dapat diuji karena aplikasi Android belum dibuat. Beberapa endpoint autentikasi pelanggan pada REST API sudah tersedia, tetapi belum menjadi aplikasi Android yang dapat digunakan pelanggan.

## 13. Pemesanan Pelanggan melalui APK
Klaim: Tidak.
Langkah uji: Fitur pemesanan pelanggan melalui APK, pemilihan nomor meja, penerapan aturan stok, dan pembatasan satu pesanan berstatus Baru untuk setiap pelanggan belum tersedia sehingga belum dapat diuji.

## 14. Pemantauan Status dan Struk Pesanan melalui APK
Klaim: Tidak.
Langkah uji: Fitur untuk melihat status dan struk pesanan milik pelanggan melalui APK belum tersedia. Pengujian untuk memastikan pelanggan hanya dapat melihat pesanan miliknya sendiri juga belum dapat dilakukan.

## Fitur Opsional

Berikut adalah daftar fitur tambahan yang tercantum dalam soal UKK beserta status pengerjaannya :
O1. Ukuran Reguler dan Large untuk Menu Minuman
Status: Tidak.
Keterangan: Fitur pilihan ukuran Reguler dan Large dengan tambahan harga serta penyesuaian takaran bahan belum dikerjakan.

O2. Promo Happy Hour
Status: Tidak.
Keterangan: Fitur diskon 20% untuk menu kategori Minuman pada pesanan yang dibuat mulai pukul 14.00 sampai sebelum pukul 16.00 belum dikerjakan.

O3. Penyesuaian Stok Bahan
Status: Tidak.
Keterangan: Fitur penyesuaian jumlah stok karena bahan rusak atau hasil opname dengan alasan yang wajib diisi belum dikerjakan.

O4. Laporan Jam Ramai
Status: Tidak.
Keterangan: Fitur laporan jumlah pesanan berdasarkan jam pemesanan belum dikerjakan.

O5. Reset Password melalui Email
Status: Tidak.
Keterangan: Fitur reset password pelanggan melalui tautan atau kode yang dikirim menggunakan server email tiruan lokal seperti Mailpit belum dikerjakan.

## 9. Belum Selesai / Keterbatasan

Berikut bagian yang belum tersedia atau masih perlu diselesaikan sebelum seluruh kebutuhan UKK terpenuhi:

1. Aplikasi Android (APK) untuk Pelanggan belum dibuat.
2. Fitur pelanggan untuk membuat pesanan melalui aplikasi Android belum tersedia.
3. Fitur pelanggan untuk melihat status dan struk pesanan melalui aplikasi Android belum tersedia.
4. Endpoint REST API untuk pemesanan dan pemantauan pesanan pelanggan belum dibuat. Endpoint API yang tersedia saat ini berfokus pada registrasi, login, logout, profil, dan perubahan password pelanggan.
5. Pada salinan proyek yang diperiksa, folder dan berkas ERD (`docs/erd`) serta UML (`docs/uml`) belum tersedia dan perlu ditambahkan.

## 10. Referensi dan Bantuan

Referensi dan alat yang digunakan selama proses pengerjaan:

- Dokumentasi Laravel
https://laravel.com/docs
Digunakan sebagai referensi dalam mempelajari dan menerapkan fitur Laravel, seperti routing, controller, model, migration, validasi, middleware, autentikasi, dan seeder.

- Dokumentasi Tailwind CSS
https://tailwindcss.com/docs
Digunakan sebagai panduan dalam membuat tampilan halaman web, mengatur tata letak, warna, ukuran teks, jarak antar elemen, dan komponen antarmuka.

- Dokumentasi Vite
https://vite.dev/guide/
Digunakan untuk memahami proses pengelolaan dan pembangunan aset frontend, termasuk menjalankan server pengembangan dan membuat hasil build CSS serta JavaScript.

- Dokumentasi MySQL
https://dev.mysql.com/doc/
Digunakan sebagai referensi dalam mengelola database, membuat struktur tabel, menentukan tipe data, serta memahami relasi antar tabel.

## 10.2. Alat Pengembangan

- Visual Studio Code
Digunakan sebagai editor untuk menulis, mengubah, dan memeriksa kode program serta mengelola file proyek.

- PowerShell
Digunakan untuk menjalankan perintah pengembangan, seperti perintah Artisan Laravel, pengujian aplikasi, pengelolaan dependensi, dan menjalankan server lokal.

- Composer
Digunakan untuk memasang dan mengelola library atau package PHP yang dibutuhkan oleh Laravel.

- Node.js dan NPM
Digunakan untuk mengelola dependensi frontend serta menjalankan perintah Vite dan Tailwind CSS.

- Git dan GitHub
Digunakan untuk mengelola riwayat perubahan kode, menyimpan proyek ke repositori, dan membantu proses pengembangan serta pencadangan source code.

## 10.3. Bantuan Pengembangan

ChatGPT (OpenAI)

Digunakan sebagai alat bantu selama proses pengerjaan proyek, terutama untuk mempelajari konsep pemrograman, memahami struktur Laravel, menyusun tahapan pengembangan, menganalisis pesan kesalahan, membantu memeriksa logika program.
Setiap saran dan kode yang diperoleh tetap diperiksa, disesuaikan, serta diuji kembali agar sesuai dengan kebutuhan dan aturan bisnis Sistem Informasi Kedai Kopi Senja.
