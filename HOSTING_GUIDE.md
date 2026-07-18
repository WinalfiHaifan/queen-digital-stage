# Panduan Hosting Queen Digital Stage

Proyek ini adalah website statis. Pastikan `index.html` berada di folder paling atas yang dipublikasikan, sejajar dengan halaman HTML lain dan folder `assets`.

## Opsi A — GitHub Pages

1. Buat repository baru di GitHub, misalnya `queen-digital-stage`.
2. Unggah seluruh isi folder proyek, bukan folder induk yang kosong.
3. Buka **Settings → Pages**.
4. Pada **Build and deployment**, pilih **Deploy from a branch**.
5. Pilih branch `main`, folder `/ (root)`, lalu **Save**.
6. Tunggu proses deployment selesai dan buka URL yang ditampilkan GitHub Pages.

Format URL biasanya:

```text
https://NAMA-PENGGUNA.github.io/queen-digital-stage/
```

## Opsi B — Netlify Drop

1. Masuk ke Netlify.
2. Buka halaman Netlify Drop.
3. Seret folder proyek yang sudah diekstrak ke area unggah.
4. Tunggu hingga URL publik dibuat.

## Pemeriksaan setelah hosting

- Home terbuka tanpa halaman 404.
- Navigasi menuju seluruh delapan halaman.
- Gambar, ikon, CSS, dan JavaScript termuat.
- Menu mobile dapat dibuka dan ditutup.
- Filter Gallery dan Store berfungsi.
- Pencarian Music memberi hasil.
- Ukuran, jumlah, Add to Cart, dan View Cart pada Store berfungsi.
- Form Fan Zone menampilkan pesan berhasil.
- Tidak ada scroll horizontal pada layar ponsel.

Jika gambar atau CSS tidak muncul, periksa kembali bahwa folder `assets` terunggah sejajar dengan `index.html` dan huruf besar/kecil nama file tidak berubah.
