# Bagus Panji Fitrianto

Modul 5 Event-Driven dan modul 8 CI/CD dan Cloud deployment
Branch `event-driven-devops`.

## Scope dan dependency
Event/Queue, QA, CI/CD, review dashboard statistik dan laporan, QR, deployment. Noe menjaga service bisnis; Aldino controller API; Rashad tampilan dashboard. Tidak memalsukan hasil test.

## File utama
- `app/Events/`
- `app/Listeners/`
- `app/Jobs/`
- `app/Providers/AppServiceProvider.php`
- `tests/`
- `.github/workflows/`
- `docs/DEPLOY.md`
- `tools/make_qr.py`
- `TESTING-REPORT.md`

## Acceptance criteria dan test
Queue worker mencatat event setelah checkout, CI menjalankan lint/test/build, role dan transaksi dites, QR mengarah ke katalog, dokumentasi PASS/FAIL/NOT TESTED aktual. Deployment nyata hanya jika siap.

## Rencana commit (contoh setelah pekerjaan nyata)
test: cover product image and checkout scenarios; ci: verify PHP GD and application build; docs: record real deployment and smoke tests

## Presentasi
Jelaskan REST request vs Event-Driven, worker, automated testing, GitHub Actions dan deployment setelah konfigurasi.

## Setup
Ikuti `INSTALLATION.md`. Source paket ini identik dengan baseline utama. Kerjakan branch sendiri; jangan commit `.env`, vendor, node_modules, foto pengguna atau hasil pengujian palsu.
