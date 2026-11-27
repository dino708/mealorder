# Noe Ferreira

Modul 1 database dan CRUD serta modul 6 serverless/business logic
Branch `database-business-logic`.

## Scope dan dependency
Kelola ERD, upload GD, Storage path, transaksi, arsip, cleanup, snapshot, stok/kuota, pembayaran dan AWS quote. Aldino mengelola route/middleware; Rashad form; Bagus pengujian dan CI.

## File utama
- `database/`
- `app/Models/Product.php`
- `app/Models/Category.php`
- `app/Models/Order.php`
- `app/Models/OrderItem.php`
- `app/Models/PreorderSchedule.php`
- `app/Models/Payment.php`
- `app/Models/OrderStatusHistory.php`
- `app/Http/Controllers/Admin/CrudController.php`
- `app/Services/ProductImageService.php`
- `app/Services/CheckoutService.php`
- `app/Services/QuoteService.php`
- `app/Http/Requests/CheckoutRequest.php`
- `serverless/quote/`
- `config/meal.php`

## Acceptance criteria dan test
Upload valid, file palsu/oversize ditolak, edit tanpa file menjaga foto, ganti dan hapus aman, history utuh, stok dan kuota tidak negatif. Jalankan ProductPhotoTest, MealOrderTest, V4CategoryTest, OrderFlowTest.

## Rencana commit (contoh setelah pekerjaan nyata)
feat(db): verify schema and product archive; feat(storage): validate and optimize uploaded photos; fix(order): ensure stock and image snapshot integrity

## Presentasi
Jelaskan FK, transaction, lockForUpdate, storage vs DB, snapshot, serverless quote dan fallback lokal.

## Setup
Ikuti `INSTALLATION.md`. Source paket ini identik dengan baseline utama. Kerjakan branch sendiri; jangan commit `.env`, vendor, node_modules, foto pengguna atau hasil pengujian palsu.
