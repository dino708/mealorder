# Pembagian tugas — ZeroBug
Mengacu dokumen Milestone 1: Aldino modul 2 dan 7, Rashad modul 3 dan 4, Noe modul 1 dan 6, Bagus modul 5 dan 8. Empat paket berisi source terintegrasi identik, masing-masing dengan petunjuk PIC. Tidak perlu menimpa proyek satu anggota dengan proyek anggota lain. Baseline bersama → tiap anggota mengubah, menguji, mendokumentasikan dan commit pekerjaan nyata → review dan merge. Jangan membuat riwayat commit palsu.
- Aldino — backend/auth, struktur dan koordinasi (branch `backend-auth`).
- Rashad — frontend, Fetch API, Web Components/Shadow DOM, upload preview (branch `frontend`).
- Noe — database, CRUD, validasi/Storage foto, checkout, Lambda (branch `database-business-logic`).
- Bagus — Event-Driven, Queue, pengujian, CI/CD dan deployment; review dashboard/report bersama Aldino/Rashad (branch `event-driven-devops`).
Branch V4 `feature/core-auth`, `feature/menu-management`, `feature/customer-ordering`, `feature/admin-management` boleh tetap dipakai bila sudah ada setelah tim sepakat. Jangan membuat dua branch untuk tugas yang sama.
File bersama: `resources/js/meal.js`, `resources/views/meal/customer.blade.php`, `resources/views/meal/admin.blade.php`, `routes/api.php`, `app/Http/Controllers/Api/OrderController.php`, `database/seeders/DatabaseSeeder.php`. Koordinasikan perubahan fungsi sebelum commit. Detail file, dependency, acceptance dan presentasi ada di `anggota/Member-1.md` s.d. `Member-4.md`.
