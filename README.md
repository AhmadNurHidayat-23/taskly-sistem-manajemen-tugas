# Taskly — Sistem Manajemen Tugas

Aplikasi pengelola tugas sederhana menggunakan Laravel 13, Blade, SQLite, dan JavaScript. Tampilannya minimalis dan responsif untuk komputer maupun ponsel.

## Fitur

- Tambah, lihat, edit, dan hapus tugas.
- Atur status, prioritas, dan tenggat waktu.
- Cari dan saring tugas berdasarkan status atau prioritas.
- Lihat statistik tugas dan penanda tenggat yang terlewat.

## Cara menjalankan

Siapkan PHP 8.3+ dengan ekstensi SQLite, Composer, Node.js 22.12+, dan npm.

```sh
git clone https://github.com/AhmadNurHidayat-23/taskly-sistem-manajemen-tugas.git
cd taskly-sistem-manajemen-tugas
composer run setup
php artisan serve --host=127.0.0.1
```

Buka [http://127.0.0.1:8000](http://127.0.0.1:8000).

Perintah pemasangan menyiapkan `.env`, kunci aplikasi, basis data SQLite, dan aset tampilan. Tidak perlu MySQL. File `.env` dan basis data lokal tidak disertakan di Git.

Untuk menambahkan contoh tugas:

```sh
php artisan db:seed
```

## Pengujian

```sh
composer test
```

## Struktur singkat

- `app/` — model, pengelolaan tugas, dan validasi.
- `database/` — migrasi SQLite dan contoh data.
- `resources/` — tampilan Blade, CSS, dan JavaScript.
- `routes/web.php` — rute aplikasi.
- `tests/` — pengujian otomatis.

Aplikasi ini untuk penggunaan lokal satu pengguna tanpa login. Tambahkan proteksi akses sebelum dipublikasikan sebagai aplikasi daring.

## Docker dan GitHub Container Registry

Image: `ghcr.io/ahmadnurhidayat-23/taskly:latest` (Linux amd64). Tag `sha-<commit SHA lengkap>` tersedia untuk versi tertentu.

```sh
docker pull ghcr.io/ahmadnurhidayat-23/taskly:latest
export APP_KEY="base64:$(openssl rand -base64 32)"
docker run -d --name taskly --platform linux/amd64 \
  -p 127.0.0.1:8080:80 \
  -e APP_KEY -e APP_URL=http://localhost:8080 \
  -v taskly-storage:/var/www/html/storage \
  ghcr.io/ahmadnurhidayat-23/taskly:latest
```

Buka http://localhost:8080. Simpan `APP_KEY` secara aman dan gunakan kunci yang sama saat membuat ulang container. `.env` dan kredensial tidak dimasukkan ke image; konfigurasi diberikan saat runtime. Volume `taskly-storage` mempertahankan SQLite dan sesi. Jangan menghapus volume jika datanya masih diperlukan.

Container memakai PHP 8.5 dan Apache. Default: `APP_ENV=production`, `APP_DEBUG=false`, `DB_CONNECTION=sqlite`, `DB_DATABASE=/var/www/html/storage/database/database.sqlite`, sesi/cache berbasis file. Migrasi berjalan saat startup; `RUN_MIGRATIONS=false` menonaktifkannya. Apple Silicon memerlukan emulasi amd64 di Docker Desktop.

Workflow [GHCR](https://github.com/AhmadNurHidayat-23/taskly-sistem-manajemen-tugas/actions/workflows/ghcr.yml) berjalan pada push ke `main` atau manual. Workflow menguji Laravel dan container sebelum mem-push image yang sama dengan tag `latest` dan SHA. Autentikasi memakai `GITHUB_TOKEN` dengan `contents: read` dan `packages: write`.
