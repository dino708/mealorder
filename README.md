# MealOrder Final Version — ZeroBug
QR-Based Food Pre-Order System untuk satu penjual. Laravel 12, Blade, Tailwind, JavaScript Fetch API, Web Components/Shadow DOM, MySQL, Sanctum, Breeze-style session auth, Laravel Queue, AWS Lambda quote dan GitHub Actions.

**Mulai dari `INSTALLATION.md`.** Root ini adalah project Laravel utuh (tanpa vendor/node_modules dan lockfile yang belum dihasilkan). Jangan gabungkan dengan ZIP V1–V4. Instal dependensi dari `composer.json` dan `package.json`, buat `.env`, migrasikan database, hubungkan storage, dan build frontend.

Dokumentasi:
- `INSTALLATION.md`: Windows/XAMPP, upgrade dengan database duplikat, foto dan QR.
- `DATABASE.md` dan `database/mealorder-final.sql`: ERD, data dictionary, SQL alternatif.
- `PEMBAGIAN-TUGAS.md`, `anggota/`, `GITHUB-WORKFLOW.md`: tugas 4 anggota dan integrasi.
- `TESTING-REPORT.md`, `AUDIT.md`, `CHANGELOG.md`: hasil pemeriksaan dan batasan.
- `PRESENTATION-GUIDE.md`, `docs/API.md`, `docs/DEPLOY.md`: teknis dan presentasi.
- `public/images/LICENSE.md`: aset lokal. Ada 12 ilustrasi makanan asli programatik, bukan foto produk nyata.
- `preview/`: screenshot browser dengan API simulasi; bukan bukti integrasi database.

Status: kode tersedia, lint dan browser simulasi lulus. Instalasi Composer, build Vite, migrasi MySQL, upload Laravel, dan deployment harus diuji nyata. Jangan menggunakan akun development atau APP_DEBUG=true di produksi. Tidak ada commit/push otomatis.
