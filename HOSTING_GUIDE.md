# Menyiapkan hosting Queen Digital Stage

Untuk demo di laptop, ikuti langkah XAMPP pada [README](README.md). Aplikasi memakai PHP 8.2 dengan ekstensi `pdo_mysql` dan `mbstring`, serta MySQL 8.0.16+ atau MariaDB dengan dukungan `CHECK` dan InnoDB. Apache menggunakan `mod_rewrite` agar URL lama `.html` membuka halaman PHP yang sesuai.

Untuk hosting PHP, buat basis data `utf8mb4` di panel hosting. Impor `database/schema.sql`, lalu `database/checkout.sql` untuk tabel pesanan. Impor `database/seed.sql` satu kali pada basis data kosong. Pada instalasi yang sudah ada, jalankan `database/update_albums_spotify.sql` untuk memperbarui tautan dan cover lima album tanpa mengimpor ulang katalog. Sediakan `QUEEN_DB_HOST`, `QUEEN_DB_PORT`, `QUEEN_DB_NAME`, `QUEEN_DB_USER`, dan `QUEEN_DB_PASSWORD` di konfigurasi server. Gunakan akun basis data aplikasi yang hanya memiliki `SELECT`, `INSERT`, `UPDATE`, dan `DELETE` pada skema ini. Buat akun administrator dengan hash dari fungsi PHP `password_hash()` atau gunakan installer lokal saat server basis data tersedia pada `127.0.0.1`.

Aktifkan HTTPS dan dukungan `.htaccess`. Periksa bahwa direktori `app`, `database`, dan `scripts` tidak dapat diakses melalui HTTP. Kelola pengaturan dan kredensial lokal di luar berkas yang dibagikan. Hosting GitHub Pages mendukung aset statis. Gunakan hosting dengan PHP dan basis data untuk fitur Admin dan Fan Zone.
