# Daftar Link — Form Monev Keterbukaan Informasi Publik

Base URL: `http://localhost` di dev. Ganti prefix ke domain produksi (mis. `https://ppid.empatlawangkab.go.id`) saat isi form asli.

## Informasi Berkala (Profil Badan Publik)

| Item | Link |
|---|---|
| 7a. Alamat Kantor | `/kontak` |
| 7b. Tugas Pokok dan Fungsi | `/profil/tugas-fungsi` |
| 7c. Struktur Organisasi Pemerintahan Kab/Kota | `/profil/struktur-organisasi` — ⚠️ isi halaman masih struktur PPID, perlu diedit dulu jadi struktur Pemkab |
| 7d. Profil singkat Pejabat dan Pejabat Struktural | `/profil-pejabat` |
| 7e. LHKPN Pimpinan (Bupati/Wabup/Sekda) | `/informasi-publik/{id}` — ⚠️ **masih Draft** (dokumen ada di admin Informasi Publik kategori Berkala), upload file LHKPN asli dulu lalu publish untuk dapat link aktifnya |
| 8a-d. Ringkasan Laporan Layanan Informasi (jumlah diterima/dikabulkan/waktu/alasan tolak) | `/laporan#statistik` |

## Informasi Serta Merta

| Item | Link |
|---|---|
| 9. Prosedur peringatan dini & evakuasi darurat | `/informasi-publik/14` (Peringatan Dini Cuaca Ekstrem), `/informasi-publik/15` (Panduan Evakuasi Bencana) |

## Informasi Tersedia Setiap Saat

| Item | Link |
|---|---|
| 10. Peraturan, keputusan, kebijakan Badan Publik | `/informasi-publik?category=setiap-saat`, contoh dokumen: `/informasi-publik/16`, `/informasi-publik/17` |

## Digitalisasi (PPID)

| Item | Link |
|---|---|
| 1. Website khusus PPID | `/` |
| 2. Website PPID terhubung website resmi | `/` (menu Profil → link ke `https://empatlawangkab.go.id`) |
| 3a. Profil singkat organisasi PPID | `/profil/tentang-ppid` |
| 3b. Tugas dan Fungsi PPID | `/profil/tugas-fungsi` |
| 3c. Struktur Organisasi PPID | `/profil/struktur-organisasi` |
| 3d. Visi dan Misi PPID | `/profil/visi-misi` |
| 4a. Tata Cara Permohonan Informasi | `/standar-layanan#tata-cara` |
| 4b. Tata Cara Pengajuan Keberatan | `/standar-layanan#keberatan` |
| 4c. Tata Cara Permohonan Penyelesaian Sengketa ke KI | `/standar-layanan#sengketa` |
| 5. Regulasi Keterbukaan Informasi Publik | `/informasi-publik?category=setiap-saat` — ⚠️ belum ada dokumen regulasi KIP spesifik ter-upload, tambahkan di admin |
| 6. Media sosial | `/kontak` |

## Standar Layanan (tambahan, sering diminta form sejenis)

| Item | Link |
|---|---|
| Alur Layanan Informasi Publik | `/standar-layanan#alur` |
| SOP PPID | `/standar-layanan#sop` |
| Maklumat Pelayanan | `/standar-layanan#maklumat` |
| Waktu, Biaya & Hari Libur Layanan | `/standar-layanan#biaya` |
| Form Permohonan Informasi | `/permohonan-informasi` |
| Form Pengajuan Keberatan | `/pengajuan-keberatan` |
| Cek Status Permohonan | `/cek-status-permohonan` |

---

**Catatan:**
- Item bertanda ⚠️ perlu tindakan admin dulu (upload file / edit konten) sebelum link ini valid dipakai di form.
- `/informasi-publik/{id}` adalah halaman detail permanen per-item (baru dibuat) — link ini stabil dipakai berkali-kali walau kontennya nanti diedit. Cek ID sebenarnya di admin → Informasi Publik.
- Item yang kontennya sudah punya halaman sendiri (Alamat Kantor, Tugas Pokok, Struktur Organisasi, Profil Pejabat) **tidak** didaftarkan sebagai Document di Informasi Publik — langsung pakai link halaman aslinya, biar gak dobel maintenance.
