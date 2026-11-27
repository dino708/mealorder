# Instalasi MealOrder Final — Windows/XAMPP

## 1. Prasyarat
PHP 8.2+ yang kompatibel Laravel 12; Composer 2; Node 22+; MySQL/MariaDB XAMPP. PHP perlu pdo_mysql, pdo_sqlite (test), GD dengan JPEG/PNG/WebP, fileinfo, mbstring, openssl, curl, zip, XML. Pastikan `php --ini` menunjuk php.ini XAMPP, aktifkan `extension=gd` dan `extension=fileinfo` jika belum aktif. Set `upload_max_filesize=5M` atau lebih, `post_max_size=10M` atau lebih, `memory_limit=256M` atau lebih. Restart Apache bila memakai Apache dan restart `artisan serve`. Document root produksi hanya `public/`.

## 2. Instalasi baru (disarankan)
Ekstrak ZIP ke `D:\xampp\htdocs\MealOrder-Final-Version` dan buka root tersebut di VS Code. Pastikan artisan ada di folder root. Jalankan MySQL XAMPP; buat database kosong `meal_order_final` (utf8mb4) melalui phpMyAdmin.
```powershell
cd D:\xampp\htdocs\MealOrder-Final-Version
$env:Path = "D:\xampp\php;" + $env:Path
php -m | Select-String "gd|fileinfo|pdo_mysql|pdo_sqlite"
Copy-Item .env.example .env
composer install
npm install
```
Edit `.env`: DB_DATABASE=meal_order_final, DB_USERNAME=root, DB_PASSWORD sesuai XAMPP, APP_URL=http://127.0.0.1:8000. Untuk pengujian lokal dengan 12 menu dan akun development, atur:
```
MEAL_SEED_CATALOG=true
MEAL_SEED_DEV_ACCOUNTS=true
MEAL_DELIVERY_ENABLED=false
```
Lalu:
```powershell
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
npm run build
php artisan serve
```
Buka http://127.0.0.1:8000. Akun development (hanya lokal): admin@example.test dan customer@example.test; password keduanya `DemoOnly123!`. Admin masuk ke /admin. Gunakan Incognito untuk dua sesi.
Untuk admin nyata: jangan aktifkan akun development. Jalankan `php artisan meal:admin` dan masukkan email/password sendiri. Buat jadwal dan produk dari panel admin. Jangan gunakan password development di produksi.

Terminal kedua (root project, tambahkan PATH PHP jika perlu): `php artisan queue:work --tries=3`.

## 3. Upgrade dari V1–V4
Backup source, database dan `storage/app/public`. Uji menggunakan database duplikat, bukan database utama. Salin file storage lama dan aset foto lokal yang benar-benar digunakan ke project baru (jangan menimpa aset menu baru). Buat `.env` sesuai database duplikat; pertahankan APP_KEY lama jika memakai data/sesi lama. Jalankan `php artisan migrate:status`; jika migration sebelumnya cocok, jalankan `php artisan migrate`, `php artisan storage:link`, `php artisan optimize:clear`, `npm run build`. Jika skema berbeda, jangan lanjut sebelum diselaraskan. Jangan jalankan migrate:fresh terhadap data lama. Migration menormalisasi path dan mengganti label seed lama yang dikenal, serta memetakan `on_delivery` ke `ready`. URL eksternal lama menjadi null (gambar default), sehingga unggah ulang foto terkait. Snapshot foto untuk pesanan lama memakai gambar produk pada saat migrasi; tidak dapat merekonstruksi foto historis yang sudah hilang. Seeder opsional tidak mengubah stok/harga produk yang ada dan tidak membuka kembali jadwal lama.

## 4. SQL alternatif
Impor `database/mealorder-final.sql` hanya ke database baru yang benar-benar kosong. File berisi migration ledger. Jangan jalankan SQL snapshot setelah artisan migrate. Setelah import, set env, key:generate, migrate:status, seeder opsional, storage:link dan build. SQL belum diuji di MySQL nyata; migrasi Laravel adalah metode utama.

## 5. Upload gambar
Admin > Kelola menu > Tambah/Edit menu > Pilih file JPG/JPEG/PNG/WebP (maks. 5 MB; 4096 piksel per sisi, maks. 12 megapiksel). Preview muncul sebelum simpan. Backend decode dan encode ulang ke JPEG maks. 1200 piksel; file tersimpan di `storage/app/public/meal-images` dan database hanya menyimpan `meal-images/{uuid}.jpg`. URL publik `/storage/meal-images/{uuid}.jpg`. Menu lama tanpa file baru mempertahankan gambar. Gambar default jika file hilang. Jika storage link gagal di Windows, cek izin symlink/Developer Mode dan `public/storage`; jangan hapus file storage asli. Lakukan `php artisan storage:link` sekali saja.

## 6. QR Code menuju katalog
QR statis tidak memerlukan tabel database. Untuk demo ponsel satu jaringan, jalankan `php artisan serve --host=0.0.0.0 --port=8000` hanya pada jaringan tepercaya, gunakan alamat LAN laptop yang sebenarnya (bukan 127.0.0.1) pada APP_URL dan SANCTUM_STATEFUL_DOMAINS (beserta port). Atur firewall untuk akses lokal saja. `python -m pip install qrcode[pil]`, lalu `python tools/make_qr.py http://ALAMAT-IP-LAPTOP:8000/`. Ganti alamat dengan IP sebenarnya. Cetak `public/images/catalog-qr.png` dan uji scan. Untuk deployment pakai domain HTTPS publik.

## 7. Pengujian
`php artisan test` (SQLite memory, GD diperlukan), `php artisan route:list`, dan pengujian MySQL manual sesuai `TESTING-REPORT.md`. Uji menu, upload, checkout dan status di browser sebelum presentasi/push. Jangan menimpa `.env` atau membagikan kredensial produksi.

Setelah `composer install` dan `npm install` lulus, review dan commit `composer.lock` serta `package-lock.json` untuk build reproducible. Workflow deployment memakai `npm ci` setelah lockfile tersedia.
