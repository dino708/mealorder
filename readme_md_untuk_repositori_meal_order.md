# Meal Order — Sistem Pemesanan Makanan Berbasis Pre-Order

**Mata Kuliah:** Pemrograman Web Dinamis 2026  
**Kelompok:** ZeroBug (Kelas A)  
**Dosen Pengampu:** Supriyanto, S.T., M.T.

---

## Deskripsi Proyek
**Meal Order** adalah aplikasi web untuk pemesanan makanan dari satu penjual menggunakan sistem *pre-order* (PO). Pelanggan memindai QR Code statis untuk mengakses katalog menu, memilih item ke keranjang, melakukan autentikasi saat *checkout*, dan memantau status pesanan. Admin mengelola kategori, produk, stok, jadwal dan kuota PO, verifikasi pembayaran manual, serta laporan penjualan.

---

## Stack Teknologi
* **Backend:** PHP, Laravel (MVC, REST API, Laravel Breeze/Session, Sanctum, Role Middleware)
* **Frontend:** Laravel Blade, Tailwind CSS, JavaScript (Fetch API, Web Components, Shadow DOM)
* **Database:** MySQL
* **Pendukung & Deployment:** Git/GitHub, Postman, Laravel Queue, AWS Lambda (Python), GitHub Actions, VPS

---

## Struktur Folder Repositori

```text
mealorder/
├── app/
│   ├── Events/                 # Event domain (misal: OrderCreated, PaymentConfirmed)
│   ├── Http/
│   │   ├── Controllers/        # Controller untuk Web (Admin/Customer) dan REST API
│   │   ├── Middleware/         # Middleware autentikasi & otorisasi role (admin/customer)
│   │   └── Requests/           # Form Request untuk validasi input
│   ├── Jobs/                   # Background jobs untuk Laravel Queue
│   ├── Listeners/              # Listener penangan event
│   ├── Models/                 # Model Eloquent (User, Category, Product, dll.)
│   ├── Policies/               # Kebijakan otorisasi akses data pesanan
│   └── Services/               # Business logic terisolasi (misal: CheckoutService)
├── database/
│   ├── migrations/             # Skema migrasi 7 tabel inti MySQL
│   └── seeders/                # Data awal (akun admin, kategori, contoh menu & jadwal PO)
├── docs/
│   ├── API.md                  # Dokumentasi endpoint REST API
│   ├── ERD.md                  # Dokumentasi skema relasi database & aturan bisnis
│   └── TESTING.md              # Skenario dan hasil pengujian aplikasi
├── resources/
│   ├── css/                    # Konfigurasi styling Tailwind CSS
│   ├── js/
│   │   └── components/         # Custom Web Components & Shadow DOM
│   └── views/                  # Template tampilan Laravel Blade
├── routes/
│   ├── api.php                 # Routing endpoint REST API
│   └── web.php                 # Routing halaman web pelanggan & admin
├── serverless/
│   └── quote/
│       └── handler.py          # Fungsi AWS Lambda untuk kalkulasi estimasi on-demand
├── tests/                      # Pengujian otomatis (Feature & Unit Tests)
├── .github/
│   └── workflows/
│       ├── ci.yml              # Pipeline Continuous Integration
│       └── deploy.yml          # Pipeline Continuous Deployment ke VPS
├── .env.example                # Contoh konfigurasi environment lokal
└── README.md                   # Dokumentasi utama proyek
```

---

## Desain Basis Data (7 Tabel Inti)
1. **`users`** — Menyimpan data akun `customer` dan `admin`.
2. **`categories`** — Mengelompokkan kategori menu makanan.
3. **`products`** — Menyimpan detail menu, harga, stok, gambar, dan status ketersediaan.
4. **`preorder_schedules`** — Mengatur periode buka/tutup PO (`start_at`, `end_at`), total `quota`, dan status (`draft`, `open`, `closed`).
5. **`orders`** — Mencatat transaksi pesanan pelanggan beserta `order_code` unik dan status pesanan (`pending`, `confirmed`, `processing`, `ready`, `completed`, `cancelled`).
6. **`order_items`** — Menyimpan rincian produk dalam pesanan beserta *snapshot* nama dan harga saat *checkout*.
7. **`payments`** — Mencatat bukti dan status pembayaran manual (`unpaid`, `waiting_confirmation`, `paid`, `rejected`).

---

## Pembagian Kerja & Struktur Branch Git

Setiap anggota kelompok bekerja pada *branch* terpisah sebelum digabungkan (*Pull Request*) ke `main`:

| Anggota | NIM | Peran | Fokus Komponen | Branch |
| :--- | :--- | :--- | :--- | :--- |
| **Aldino Shorabal Tasar** | 2400018271 | Ketua & Backend Dev | REST API, Autentikasi, Struktur Proyek | `backend-auth` |
| **Rashad Akbar** | 2400018256 | Frontend Dev | Async JS, Fetch API, Web Components & Shadow DOM | `frontend` |
| **Noe Ferreira** | 2414018267 | Database & Logic Dev | Database, Migrasi, CRUD, Serverless & Logika Bisnis | `database-business-logic` |
| **Bagus Panji Fitrianto** | 2400018254 | Event-Driven & DevOps | Queue, Event-Driven, CI/CD & Cloud Deployment | `event-driven-devops` |

---

## Panduan Instalasi Lokal

1. **Clone repositori dan masuk ke direktori proyek:**
   ```bash
   git clone https://github.com/dino708/mealorder.git
   cd mealorder
   ```
2. **Instal dependensi PHP dan JavaScript:**
   ```bash
   composer install
   npm install
   ```
3. **Salin file konfigurasi dan buat application key:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. **Sesuaikan konfigurasi database MySQL pada file `.env`, lalu jalankan migrasi & seeder:**
   ```bash
   php artisan migrate --seed
   ```
5. **Jalankan server pengembangan lokal:**
   ```bash
   php artisan serve
   npm run dev
   ```