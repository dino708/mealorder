# Audit Final Version
Baseline: MealOrder-V4 (source 127 file termasuk dokumentasi), dokumen Milestone 1 ZeroBug, brief terbaru. Audit sumber, bukan audit server produksi.
- V4: upload limit 2 MB, URL eksternal diterima, gambar tidak dihapus setelah penggantian, order_items belum memiliki snapshot gambar, delete produk ber-FK ditolak, nama seed Demo, status on_delivery tambahan.
- Final: upload 5 MB, MIME/ekstensi/ukuran/dimensi + decode/re-encode GD, nama UUID, path relatif; tidak menerima path/URL dari klien; cleanup file baru jika DB gagal dan cleanup lama jika tidak dirujuk setelah transaksi; soft delete untuk produk terkait pesanan; snapshot foto saat checkout; UI admin/katalog/detail/cart/order memakai referensi lokal dan fallback.
- Status dikembalikan ke pending → confirmed → processing → ready → completed / cancelled. Delivery opsional dari V4 dipertahankan untuk kompatibilitas namun default nonaktif; bukan bagian wajib Milestone.
- 7 tabel bisnis inti tetap, order_status_histories tambahan dari V4, serta kolom deleted_at dan product_image. Migrasi aditif; tidak menghapus transaksi. Status on_delivery lama dipetakan ke ready.
- 12 ilustrasi lokal original; bukan foto asli. Seeder katalog dan akun development opt-in, local/testing saja; tidak ada kredensial pada Blade/JS.
- Limit audit: Composer/GD/MySQL tidak tersedia di lingkungan ini; tidak ada pengujian end-to-end Laravel nyata atau audit penetrasi. Risiko file yatim akibat proses server mati mendadak di antara penyimpanan file dan DB tetap ada; backup dan pemeriksaan storage berkala diperlukan.
