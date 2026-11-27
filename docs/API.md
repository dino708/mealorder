# API Final
Prefix `/api`, same origin session Sanctum; JSON Accept dan X-CSRF-TOKEN untuk mutasi. File memakai multipart POST, `_method=PUT` untuk edit. Policy dan middleware memeriksa role di backend.
| Method | Path | Access |
|---|---|---|
| GET | /catalog, /categories, /schedules, /settings, /ping | public |
| POST | /quote | public, throttled; subtotal saja, tidak otoritatif |
| GET | /me | authenticated |
| GET | /orders, /orders/{id} | customer own; show admin allowed |
| POST | /orders | customer; schedule_id, items[{product_id,quantity}], notes, fulfillment pickup/delivery, delivery_address/contact_phone if delivery, payment_method cash/transfer/manual |
| GET/POST | /admin/categories, /admin/products, /admin/schedules | admin |
| PUT/DELETE | /admin/{type}/{id} | admin |
| GET | /admin/orders, /admin/customers | admin |
| PATCH | /admin/orders/{id}/status | admin; status + reason if cancelled |
| PATCH | /admin/orders/{id}/payment | admin; status unpaid/waiting_confirmation/paid/rejected |
| GET | /admin/report, /admin/report/export | admin; optional from/to YYYY-MM-DD |
Status: pending → confirmed → processing → ready → completed (paid). Cancellation hanya pending/confirmed; alasan wajib; jika sudah dibayar, kembalikan pembayaran manual lalu catat status terlebih dahulu. Idempotent same status. Server hitung harga dan ongkir, validasi stok/quota, simpan snapshot. 401 unauthenticated, 403 forbidden, 409 konflik relasi, 422 input atau transisi invalid. /admin/customers tidak mengirim password/token. CSV memuat kode, waktu selesai, metode penerimaan dan total.

## Upload Final
POST multipart `image_file`; edit POST dengan `_method=PUT`, atau PUT JSON tanpa gambar. Field `image` dilarang sebagai input; response `image` berisi path relatif. 5 MB, JPG/JPEG/PNG/WebP, maksimal 4096/sisi dan 12MP; re-encode JPEG. Delete produk bertransaksi menjadi arsip. `items[].product_image` adalah snapshot. `/settings` mengembalikan `delivery_enabled`, default false. Status valid: pending, confirmed, processing, ready, completed, cancelled.

## Autentikasi Token (Postman / klien non-browser)
Selain session cookie, API menerima bearer token Laravel Sanctum. Kirim `Authorization: Bearer <token>` dan `Accept: application/json`; header CSRF tidak diperlukan untuk request bertoken.
| Method | Path | Access | Keterangan |
|---|---|---|---|
| POST | /api/register | public, throttle 10/menit | name, email, phone?, password, password_confirmation. Peran selalu `customer`. 201 + token |
| POST | /api/login | public, throttle 10/menit | email, password, device_name?. 200 + token; 401 bila kredensial salah; 422 bila input kosong |
| POST | /api/logout | token | Mencabut token yang sedang dipakai |

Koleksi uji: `docs/postman/MealOrder.postman_collection.json` dengan environment `MealOrder.postman_environment.json`. Prasyarat lokal: `MEAL_SEED_CATALOG=true` dan `MEAL_SEED_DEV_ACCOUNTS=true` di `.env`, lalu `php artisan migrate:fresh --seed` dan `php artisan serve`.