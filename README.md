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
