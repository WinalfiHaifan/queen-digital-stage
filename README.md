# Queen Digital Stage — Final Team Edition

Website multipage bertema band Queen untuk tugas Praktikum Desain Web Kelompok 8.

## Halaman

- `index.html` — Home
- `about.html` — profil band dan anggota
- `music.html` — katalog, pencarian, cerita lagu, video, dan Spotify embed
- `history.html` — linimasa dan penghargaan
- `gallery.html` — galeri foto/video dengan filter dan lightbox
- `fan-zone.html` — kartu komunitas dan formulir diskusi
- `store.html` — katalog merchandise, pilihan produk/ukuran/jumlah, dan simulasi keranjang
- `credits.html` — sumber media dan kredit

## Menjalankan secara lokal

Cara yang disarankan:

1. Ekstrak ZIP.
2. Buka folder ini di Visual Studio Code.
3. Jalankan `index.html` dengan ekstensi Live Server.

Alternatif tanpa ekstensi:

```bash
python -m http.server 8000
```

Lalu buka `http://localhost:8000`.

## Struktur utama

```text
queen-digital-stage-final/
├── index.html
├── about.html
├── music.html
├── history.html
├── gallery.html
├── fan-zone.html
├── store.html
├── credits.html
└── assets/
    ├── css/styles.css
    ├── images/
    └── js/
```

## Status finalisasi

Versi gabungan telah dirapikan dan diuji pada viewport desktop 1440 px serta mobile 390 px. Pengujian mencakup delapan halaman, aset lokal, error JavaScript, horizontal overflow, menu, filter, slider, pencarian musik, lightbox, formulir komunitas, serta kontrol Store.

Lihat `HOSTING_GUIDE.md` untuk publikasi. Kredit dan ketentuan media tersedia di `MEDIA_SOURCES.md`.

## Catatan

- Keranjang Store adalah simulasi sisi klien dan tersimpan di `localStorage`; tidak ada pembayaran nyata atau basis data.
- Pemutaran YouTube/Spotify bergantung pada kebijakan embed, koneksi, wilayah, dan browser penyedia.
- Lirik penuh tidak disimpan di proyek. Lyrics Finder hanya mencari katalog lokal dan mengarahkan pengguna ke pencarian eksternal.
