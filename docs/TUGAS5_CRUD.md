# Tugas 5 — Operasi CRUD

Panel AdminLTE menyediakan Create, Read, Update, dan Delete untuk semua tabel pada basis data Queen Digital Stage.

## Cakupan tabel

| Tabel | Bagian panel |
| --- | --- |
| `admin_users` | Administrators |
| `band_members` | Band Members |
| `albums` | Albums |
| `songs` | Songs |
| `timeline_events` | Timeline & Awards |
| `media_items` | Media Library |
| `fan_zone_submissions` | Fan Zone |
| `product_categories` | Product Categories |
| `products` | Products |
| `orders` | Orders & Items |
| `order_items` | Orders & Items |

## Tahap tugas

- **WBS 2.2 — Create dan Read:** formulir Admin membuat data; daftar Admin dan halaman publik membaca data yang relevan. Detail implementasi terdapat di `app/admin-actions.php`, `app/admin-resources.php`, dan `app/views/admin.php`.
- **WBS 2.3 — Update dan Delete:** aksi Admin mengubah dan menghapus data. Operasi item pesanan dikelola melalui `app/admin-order-actions.php`; aksi tabel lainnya berada di `app/admin-actions.php`.
- Pesanan checkout menyimpan alamat pembeli di `orders`; `order_items` menghubungkan rincian produk ke pesanan. Fan Zone memerlukan persetujuan Admin sebelum kiriman tampil di website publik.

## Cara memeriksa

1. Impor `Tugas3_Implementasi_Basis_Data_Queen_Kelompok_8.sql` ke MySQL/MariaDB.
2. Atur kredensial pada `app/config.local.php` atau variabel lingkungan. Jangan mengunggah kredensial asli.
3. Buka `/admin/`, masuk dengan akun Admin lokal/hosting, lalu gunakan menu tabel di panel.
4. Periksa proses tambah, baca, edit, dan hapus pada data uji yang aman. Hindari menghapus data produksi.

Data contoh katalog disediakan pada `database/seed.sql`. Pesanan, akun Admin, dan kiriman Fan Zone tidak diberi data pribadi contoh.
