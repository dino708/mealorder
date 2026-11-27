# Aldino Shorabal Tasar

Modul 2 REST API/authentication dan modul 7 struktur proyek
Branch `backend-auth`.

## Scope dan dependency
Sanctum, CSRF, role, session, login/register/profile, API contracts dan integrasi. Pastikan OrderController hanya mengembalikan pesanan yang diizinkan. Koordinasi Noe untuk CRUD dan CheckoutService; Rashad untuk kontrak JSON; Bagus untuk event.

## File utama
- `app/Models/User.php`
- `app/Http/Controllers/Auth/`
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Middleware/`
- `app/Policies/`
- `routes/web.php`
- `routes/api.php`
- `routes/auth.php`
- `bootstrap/`
- `app/Http/Controllers/Api/OrderController.php`
- `routes/console.php`

## Acceptance criteria dan test
Customer tidak bisa /admin, login salah ditolak, register tidak bisa memilih role admin, customer tidak bisa melihat pesanan lain. Jalankan V4AuthTest, MealOrderTest.

## Rencana commit (contoh setelah pekerjaan nyata)
feat(auth): harden role and session access; feat(api): verify order response contracts; fix(integration): resolve reviewed integration issues

## Presentasi
Tunjukkan routing, autentikasi, policy, middleware dan struktur MVC; jelaskan bahwa validasi backend tidak bergantung pada UI.

## Setup
Ikuti `INSTALLATION.md`. Source paket ini identik dengan baseline utama. Kerjakan branch sendiri; jangan commit `.env`, vendor, node_modules, foto pengguna atau hasil pengujian palsu.
