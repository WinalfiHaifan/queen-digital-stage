# Queen Digital Stage

Website Praktikum Pemrograman Web Kelompok 8. Halaman publik memakai PHP untuk membaca konten dari basis data. Panel AdminLTE 4.10.0 mengelola sebelas tabel. Aset dan lisensi template tersedia di `assets/vendor/adminlte/`.

## Menjalankan di XAMPP

1. Letakkan folder `queen-digital-stage-final` di `htdocs/web5d` dan nyalakan Apache serta MySQL/MariaDB melalui XAMPP Control Panel.
2. Untuk pemasangan baru, jalankan perintah ini dari folder proyek. Sesuaikan port dengan server basis data XAMPP Anda.

   ```powershell
   $env:QUEEN_DB_PORT = '3307'
   & 'D:\XAMPP\php\php.exe' .\scripts\install.php
   ```

3. Buka [website lokal](http://localhost/web5d/queen-digital-stage-final/) atau [panel Admin](http://localhost/web5d/queen-digital-stage-final/admin/).

Installer membuat basis data `queen_digital_stage`, mengimpor katalog, dan menyiapkan akun basis data khusus aplikasi. Jika server memerlukan sandi untuk pemasangan, sediakan `QUEEN_INSTALL_USER` dan `QUEEN_INSTALL_PASSWORD` pada lingkungan terminal. Akun `queen_admin` dibuat saat tabel administrator kosong. Baca sandi awal dari `%LOCALAPPDATA%\QueenDigitalStage\admin-login.txt`. Anda dapat mengubah sandi dari menu **Administrators**. File pengaturan lokal `app/config.local.php` diabaikan oleh Git dan akses HTTP ke direktori internal diblokir oleh konfigurasi Apache.

## Alur demo

- **Music** menampilkan koleksi album dan lagu dari basis data. Pencarian, filter genre, urutan, ringkasan, cerita, serta tautan pemutaran tersedia di halaman yang sama.
- **About**, **Home**, **History**, dan **Media** memakai profil, lagu unggulan, linimasa, penghargaan, serta foto dan video dari panel Admin. Atribusi media tersedia di halaman **Credits**.
- **Store** menampilkan produk aktif dengan stok dan berat. Produk aktif yang stoknya nol tetap tampil sebagai *Out of Stock*. Stok dihitung per produk, termasuk jumlah gabungan semua ukuran pakaian.
- **Checkout** dibuka dari **View Cart → Proceed to Checkout**. Isi nama, nomor telepon, alamat, kota, provinsi, dan kode pos. Periksa ringkasan lalu pilih **Place order**. Sistem menyimpan pesanan COD, menampilkan nomor pesanan, dan mengurangi stok saat pesanan berhasil dibuat. Ongkir demo bernilai nol.
- **Admin → Orders & Items** memungkinkan admin membuat pesanan COD, melihat item, serta mengubah alamat sebelum pengiriman. Selama status **Pending**, admin dapat menambah, mengubah, atau menghapus item. Sistem menghitung ulang total dan stok. Alur pesanan berjalan dari **Pending** ke **Processing**, **Shipped**, lalu **Delivered and paid**. Pembatalan sebelum pengiriman mengembalikan stok dan memungkinkan penghapusan pesanan beserta itemnya.
- **Fan Zone** menyimpan kiriman sebagai pending. Admin bisa menambah, membaca, mengedit, dan menghapus kiriman. **Approve and publish** menampilkannya di halaman publik, dan **Hide note** menariknya.

## Basis data dan sumber data

- `database/schema.sql` memuat 9 tabel inti dari rancangan basis data dan seluruh relasi FK-nya.
- `database/checkout.sql` menambahkan `orders` dan `order_items` untuk checkout. Jalankan installer yang sama untuk memasang tabel tambahan pada instalasi XAMPP yang sudah ada.
- `Tugas3_Implementasi_Basis_Data_Queen_Kelompok_8.sql` adalah satu berkas siap impor yang menggabungkan 11 tabel, relasi, dan katalog demo untuk database lokal baru. `database/update_albums_spotify.sql` menambahkan cover dan tautan Spotify ke lima album tanpa membuat baris album duplikat.
- `database/seed.sql` berisi katalog dari website awal. Stok dan berat adalah data demo, dengan USD sebagai asumsi rancangan. Nilai tersebut bisa disesuaikan di **Admin → Products**.
- `app/` berisi query, validasi, dan tampilan PHP. `assets/` berisi gaya, gambar, dan interaksi browser.
- Tanggal lahir anggota memakai profil resmi [Freddie Mercury](https://www.queenonline.com/freddie_mercury), [Brian May](https://www.queenonline.com/brian_may), [Roger Taylor](https://www.queenonline.com/roger_taylor), dan [John Deacon](https://www.queenonline.com/john_deacon). Rujukan linimasa memakai [sejarah Queen](https://www.queenonline.com/queen). Genre digunakan sebagai label editorial katalog.

Lihat [panduan hosting PHP](HOSTING_GUIDE.md), [panduan CRUD Tugas 5](docs/TUGAS5_CRUD.md), dan [kredit media](MEDIA_SOURCES.md).
