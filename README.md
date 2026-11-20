# PPID Kabupaten Empat Lawang

Portal Pejabat Pengelola Informasi dan Dokumentasi (PPID) Kabupaten Empat Lawang:
situs publik keterbukaan informasi (profil, informasi publik, berita, galeri, agenda,
pengadaan, laporan, permohonan informasi & keberatan), panel admin, dan REST API.

- Produksi: https://ppid.empatlawangkab.go.id
- Dokumentasi API: [API.md](API.md)
- Link untuk form Monev KIP: [MONEV-LINKS.md](MONEV-LINKS.md)

## Teknologi

- Laravel 12, PHP ^8.2, Blade (tanpa build front-end; CSS/JS lewat CDN dan `public/`).
- Database: MySQL 8 di produksi; SQLite/Postgres juga jalan untuk lokal dan tes.
- Upload file: disk `public` = S3-compatible (IDCloudHost).
- Tidak ada queue/job — tidak perlu worker.

## Menjalankan lokal

```bash
composer install
cp .env.example .env          # isi DB_* dan S3_* bila perlu
php artisan key:generate
php artisan migrate
php artisan db:seed --class=UserSeeder   # akun admin: ppid@empatlawangkab.go.id / password
php artisan serve                        # http://127.0.0.1:8000, admin di /admin
```

Data contoh (opsional, tanpa internet):

```bash
php artisan db:seed --class=ProfileSeeder      # juga: News, Gallery, Event, Procurement,
                                               # Document, Official, PpidSetting, ContactSetting
```

`php artisan db:seed` penuh juga menjalankan `*ExternalSeeder` yang mengunduh data dari
internet dan meng-upload ke S3 — jangan dipakai tanpa kredensial S3 yang benar.

Tanpa kredensial S3, gambar upload tidak tampil (halaman tetap jalan).

## Perintah penting

| Perintah | Guna |
|---|---|
| `php artisan test` | Tes (SQLite in-memory, lihat `phpunit.xml`) |
| `php artisan test --filter=ApiTest` | Satu file tes |
| `php artisan migrate --force` | Migrasi (produksi) |
| `php artisan route:list` | Daftar route |
| `php artisan mail:test <email>` | Kirim email uji (cek konfigurasi SMTP) |
| `php artisan config:clear` | Setelah ubah `.env` di server yang memakai `config:cache` |

## Konfigurasi (.env)

Lihat `.env.example`. Variabel khusus aplikasi ini:

| Variabel | Guna |
|---|---|
| `API_TOKEN` | Bearer token REST API `/api/v1`. Kosong = API ditutup (401). |
| `S3_ENDPOINT`, `S3_REGION`, `S3_ACCESS_KEY_ID`, `S3_SECRET_ACCESS_KEY`, `S3_BUCKET_NAME`, `S3_ROOT_DIR`, `S3_USE_PATH_STYLE_ENDPOINT` | Penyimpanan semua upload (disk `public`). |
| `DB_CONNECTION`, `DB_URL` / `DB_HOST`… | Database. |
| `MAIL_*` | Pengiriman email (reset password). Lihat "Email (SMTP)" di bawah. |

### Email (SMTP)

Produksi — isi di `.env` server (jangan di-commit):

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp            # port 587 (STARTTLS); port 465 (SSL) pakai smtps
MAIL_HOST=mail.domain-anda.go.id
MAIL_PORT=587
MAIL_USERNAME=ppid@domain-anda.go.id
MAIL_PASSWORD=...           # Gmail/Google Workspace: App Password, bukan password akun
MAIL_FROM_ADDRESS="ppid@domain-anda.go.id"
MAIL_FROM_NAME="PPID Kabupaten Empat Lawang"
```

Lalu `php artisan config:clear` (bila server memakai `config:cache`) dan cek koneksi:

```bash
php artisan mail:test alamat@tujuan.id   # kirim email uji; gagal = pesan error SMTP
```

Lokal: `MAIL_MAILER=log` — email tidak dikirim, isinya tercatat di `storage/logs/laravel.log`.

Pengaturan yang diubah dari panel admin (bukan `.env`): kontak & media sosial, statistik
layanan, asisten virtual Sapa Madani (aktif, nomor WhatsApp, URL API chatbot) — tersimpan
di tabel `contact_settings` / `ppid_settings`.

## Arsitektur singkat

```
routes/web.php     situs publik (URL berbahasa Indonesia) + /admin (middleware auth)
routes/api.php     /api/v1/* — middleware BearerToken (app/Http/Middleware)
app/Http/Controllers         controller publik
app/Http/Controllers/Admin   CRUD panel admin
app/Http/Controllers/Api     REST API (JSON)
app/Models                   Eloquent: News, Gallery/GalleryItem, Event, Document,
                             InfoPublic, Procurement, Official, Profile, InformationRequest,
                             Complaint, Contact, ContactSetting, PpidSetting, User
app/Support/BmkgWeather.php  prakiraan cuaca & peringatan dini BMKG (di-cache)
app/helpers.php              helper global: storage_url, plain_text, wa_link, assistant_settings, …
resources/views              Blade; layouts/app.blade.php = layout publik + SEO head
```

- **Publik**: halaman Blade server-rendered. Widget cuaca memanggil `/cuaca` (JSON) lewat JS
  supaya BMKG yang lambat tidak menahan halaman. Chat Sapa Madani: `POST /sapa-madani/chat`
  meneruskan pesan ke URL API yang diatur admin.
- **Admin**: `/admin`, login Laravel UI; pendaftaran publik dimatikan (semua akun = admin).
  Upload galeri besar memakai chunk upload (`/admin/galeri/{id}/upload-chunk`).
- **SEO**: `/sitemap.xml` dan `/robots.txt` adalah route (`SitemapController`), bukan file statis.
- **File**: path disimpan di DB, URL dibentuk dengan `storage_url()` dari disk `public` (S3).
