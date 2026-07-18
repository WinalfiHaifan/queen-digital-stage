# Implementation Guide — Queen Digital Stage

Dokumen ini menjelaskan pembaruan yang sudah diterapkan pada proyek dan bagian kode yang perlu diedit ketika tim ingin menambah konten.

## Tahap 1 — Struktur proyek bersama

Proyek mempertahankan struktur sederhana:

- 8 halaman HTML pada folder utama.
- 1 file CSS: `assets/css/styles.css`.
- 1 file JavaScript: `assets/js/main.js`.
- 1 folder gambar bersama: `assets/images/`.

Semua halaman memakai navigasi, footer, sistem layout, breakpoint, animasi, dan komponen interaktif yang sama.

## Tahap 2 — Flexbox dan CSS Grid

Flexbox dipakai untuk komponen satu dimensi, seperti:

- navigasi desktop dan mobile;
- susunan tombol dan toolbar;
- metadata kartu;
- kontrol pencarian dan filter;
- bagian dalam modal/dialog;
- baris aksi pada katalog lagu dan produk.

CSS Grid dipakai untuk komponen dua dimensi, seperti:

- daftar kartu pada Home;
- profil anggota pada About;
- timeline pada History;
- katalog dan cerita lagu pada Music;
- koleksi foto/video pada Gallery;
- kartu komunitas pada Fan Zone;
- katalog produk pada Store;
- daftar referensi pada Credits;
- susunan kolom footer.

Cari komentar `PROFESSIONAL RESPONSIVE SYSTEM` pada bagian bawah `styles.css` untuk melihat aturan tambahan yang menyatukan seluruh halaman.

## Tahap 3 — Animasi dan aksesibilitas

Animasi yang diterapkan:

- progress bar ketika halaman digulir;
- reveal animation ketika elemen memasuki viewport;
- hover dan focus animation pada kartu, tombol, gambar, dan navigasi;
- animasi menu mobile dan dialog;
- transisi filter pada Gallery, Store, dan Music.

JavaScript menambahkan kelas reveal dengan `IntersectionObserver`. Untuk pengguna yang mengaktifkan pengurangan gerakan, aturan `prefers-reduced-motion` menonaktifkan animasi yang tidak diperlukan.

## Tahap 4 — Responsive Web Design

Breakpoint utama berada pada:

- 1100 px: grid besar mulai dipadatkan;
- 980 px: susunan dua kolom berubah menjadi satu kolom;
- 760 px: navigasi mobile aktif dan toolbar ditumpuk;
- 620 px: kartu serta dialog dioptimalkan untuk layar kecil;
- 420 px: spacing dan ukuran tombol dipadatkan untuk ponsel kecil.

Gunakan tag viewport berikut pada setiap halaman:

```html
<meta name="viewport" content="width=device-width, initial-scale=1.0">
```

## Tahap 5 — YouTube tetap di dalam halaman

Video memakai iframe privacy-enhanced:

```html
<iframe
  src="https://www.youtube-nocookie.com/embed/VIDEO_ID"
  title="Judul video"
  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
  allowfullscreen>
</iframe>
```

Tautan video pada halaman lain ditangkap oleh `main.js`, lalu dibuka melalui dialog player di dalam halaman. Tombol fallback tetap tersedia bila pemilik video, wilayah, browser, atau platform menolak embed.

Untuk mengganti video lagu, edit properti `youtube` pada array `songs` di `assets/js/main.js` menggunakan ID video saja, bukan seluruh URL.

## Tahap 6 — Spotify tetap di dalam halaman

Spotify memakai iframe embed resmi pada `music.html`. Untuk mengganti album atau playlist:

1. Buka Spotify dan pilih album/playlist.
2. Pilih Share lalu Embed.
3. Salin ID album/playlist.
4. Ganti bagian ID pada atribut `src` iframe Spotify di `music.html`.

Pemutaran tetap berlangsung di area halaman selama browser dan Spotify mengizinkannya.

## Tahap 7 — Song Library, Stories, dan Lyrics Finder

Semua data katalog lokal tersimpan pada array `songs` di `assets/js/main.js`. Setiap entri berisi:

```js
{
  title: "Judul lagu",
  year: 1975,
  era: "1970s",
  album: "Nama album",
  writer: "Penulis",
  type: "Studio",
  image: "nama-file.webp",
  youtube: "VIDEO_ID",
  summary: "Ringkasan singkat",
  story: "Cerita lagu"
}
```

Dari satu sumber data tersebut, JavaScript otomatis membangun:

- katalog lagu;
- filter berdasarkan era;
- pencarian judul, album, tahun, dan penulis;
- 24 kartu cerita lagu;
- detail dialog setiap lagu;
- direktori Lyrics Finder;
- tombol pemutar YouTube untuk lagu yang memiliki ID video.

Lyrics Finder sengaja tidak menyimpan teks lirik penuh. Fitur ini menemukan lagu dari katalog, menampilkan informasi dasarnya, lalu menyediakan pencarian sumber lirik eksternal. Pendekatan tersebut menghindari duplikasi penuh karya berhak cipta di dalam proyek.

## Cara menambah lagu baru

1. Simpan gambar ke `assets/images/`.
2. Buka `assets/js/main.js`.
3. Cari `const songs = [`.
4. Tambahkan satu objek data dengan format yang sama.
5. Pastikan nama gambar dan ID video benar.
6. Muat ulang `music.html`; katalog, cerita, dan Lyrics Finder akan diperbarui otomatis.

## Pengujian yang sudah dilakukan

- 8 halaman memanggil tepat satu CSS dan satu JS.
- Tidak ada path aset lokal yang hilang.
- JavaScript lolos pemeriksaan sintaks.
- CSS lolos pemeriksaan parser.
- Tidak ada horizontal overflow pada viewport desktop 1440 px dan mobile 390 px.
- Menu mobile, filter Gallery, filter Store, cart, form komunitas, slider penghargaan, katalog lagu, dialog cerita, Lyrics Finder, dan pergantian video sudah diuji.

## Menjalankan proyek

Cara paling stabil:

1. Ekstrak ZIP.
2. Buka folder proyek melalui Visual Studio Code.
3. Pasang ekstensi Live Server.
4. Klik kanan `index.html`.
5. Pilih **Open with Live Server**.

Beberapa browser membatasi penyimpanan lokal dan embed saat halaman dibuka langsung melalui `file://`, sehingga Live Server lebih disarankan.
