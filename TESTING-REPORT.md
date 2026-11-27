# Testing report — MealOrder Final Version

Status di bawah hanya untuk pemeriksaan yang benar-benar dilakukan. Browser memakai Blade yang dirender sebagai HTML, API mock, dan byte aset lokal melalui data URI karena navigasi jaringan dibatasi. **Bukan pengujian end-to-end Laravel**. Screenshot ada di `preview/`.

| Kasus | Status | Bukti / batas |
|---|---|---|
| Sintaks PHP (61 file non-Blade) | PASS | `php -l` |
| Sintaks JavaScript (4 file) | PASS | `node --check` |
| Lambda quote unit tests | PASS | 2 tes Python |
| Katalog 12 ilustrasi, decode gambar | PASS | Chromium mock, desktop dan HP |
| Pencarian katalog, detail gambar, keranjang, subtotal | PASS | Chromium mock |
| Gambar detail/cart/order (byte aset lokal) | PASS | Chromium mock |
| Preview upload WebP dan tolak pilihan file script | PASS | Chromium mock |
| Admin membuka/batal form dan navigasi | PASS | Chromium mock |
| Checkout, status, konfirmasi pembayaran | PASS | Chromium mock, bukan DB |
| Responsivitas customer 390/768/1440 px, admin 390/1440 | PASS | Chromium mock; tidak overflow pada ukuran yang diperiksa |
| Composer install dan build Vite | NOT TESTED | Composer dan dependensi tidak tersedia |
| PHP GD, Storage, upload valid JPG/JPEG/PNG/WebP 5 MB | NOT TESTED | GD tidak tersedia; test ProductPhotoTest disediakan |
| MIME palsu, ekstensi executable, oversize, decode gagal, path traversal | NOT TESTED | Feature test disediakan, belum dieksekusi |
| Edit tanpa gambar, ganti gambar, delete, shared reference, DB gagal | NOT TESTED | Feature test disediakan |
| Arsip menu, snapshot foto, pengembalian stok dan riwayat | NOT TESTED | Feature test disediakan |
| Migration MySQL, SQL import, storage:link Windows | NOT TESTED | MySQL dan Composer tidak tersedia |
| Login/register nyata, role/CSRF/IDOR | NOT TESTED | Feature test disediakan |
| CRUD kategori dan produk nyata | NOT TESTED | Feature test disediakan |
| Stok/kuota, checkout/pembayaran/status nyata | NOT TESTED | Feature test disediakan |
| Dashboard statistik dan laporan CSV dari MySQL | NOT TESTED | Belum dijalankan |
| QR scan di HP, AWS Lambda URL, VPS dan GitHub Actions | NOT TESTED | Membutuhkan lingkungan nyata |
| Audit penetrasi/production readiness | NOT TESTED | Tidak ada jaminan bebas bug |

Kegagalan selama pengujian: navigasi HTTP mock terblokir oleh sandbox; metode uji diubah menjadi HTML lokal dan pemetaan byte aset, lalu suite UI lulus. Tidak ada klaim bahwa URL storage benar-benar dapat diakses melalui Laravel sebelum pengujian integrasi.

## Pengujian wajib setelah setup
1. `php artisan test` (SQLite memory, GD diperlukan), `npm run build`, `php artisan route:list`.
2. Jalankan MySQL dan aplikasi. Login admin; tambah kategori, menu, upload foto JPG/JPEG/PNG/WebP, uji 5 MB dan >5 MB. Cek file di storage/app/public/meal-images dan path relatif di DB. Uji script, file ganti ekstensi, MIME salah; pastikan 422 dan tidak ada file tak terpakai.
3. Cek foto pada katalog, detail, cart, pesanan customer, pesanan admin. Ganti foto produk: foto lama terhapus jika tak dirujuk; foto yang tercatat di order_items harus tetap ada. Edit tanpa file, verifikasi path tidak berubah. Hapus produk tanpa pesanan, lalu produk dengan pesanan; yang kedua terarsip, history tetap utuh. Coba customer request CRUD langsung, harus ditolak.
4. Checkout dengan jadwal aktif/tutup, stok/kuota habis dan harga dimanipulasi; cek database. Uji pending → confirmed → processing → ready → completed (wajib lunas), cancellation dua kali tanpa pengembalian stok ganda. Customer lain tidak dapat melihat order.
5. Uji seluruh halaman desktop/tablet/HP dan refresh, link, foto default jika file hilang. Cek dashboard stok/kuota dan laporan sesuai DB, QR scan, queue worker. Catat hasil aktual PASS/FAIL/NOT TESTED dan perbaiki FAIL sebelum deployment.
