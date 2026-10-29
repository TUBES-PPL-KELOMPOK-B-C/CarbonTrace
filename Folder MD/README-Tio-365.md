# README Pengembangan — Tio Maria Anggita Sinaga

**Proyek** CarbonTrace — Platform Pelacakan dan Pemangkasan Jejak Karbon Harian  
**NIM** 102022400365  
**Peran** Developer  
**Modul** Edukasi dan Komunitas  
**Akhiran berkas** `365` (tiga digit terakhir NIM)  
**Jumlah PBI** 4

---

## 1. Ruang Lingkup

Mengisi sisi penjelasan produk: artikel edukasi, balasan artikel, rekomendasi tindakan yang
muncul ketika emisi melebihi target, dan pengumuman resmi kepada seluruh pengguna.

### Yang dikerjakan

| PBI | KF | Nama Fitur | Kerumitan |
|---|---|---|---|
| PBI-16 | KF-17 | Manajemen Artikel Edukasi | Menengah |
| PBI-17 | KF-18 | Manajemen Rekomendasi Aksi | Menengah |
| PBI-18 | KF-19 | Manajemen Pengumuman | Menengah |
| PBI-19 | KF-28 | Manajemen Balasan Artikel | Menengah |

### Fitur pendukung (tidak dihitung sebagai PBI)

**KF-27 Statistik Dampak Komunitas**

Menjumlahkan penurunan emisi seluruh pengguna. Bersifat baca saja, tidak mengelola data
sendiri.

---

## 2. Lingkungan dan Konvensi Penamaan

**Tumpukan teknologi:** Laravel 12, PHP 8.3, MySQL 8, Blade + Tailwind, ApexCharts, spatie/laravel-permission, barryvdh/laravel-dompdf.

Setiap berkas yang kamu buat diberi akhiran **`365`** sebelum ekstensinya, supaya terlihat siapa yang menulisnya. Nama kelas di dalam berkas harus sama persis dengan nama berkasnya.

```
app/Http/Controllers/ManajemenArtikelEdukasi365.php
   -> class ManajemenArtikelEdukasi365 extends Controller
```

> **Catatan penting.** Laravel menebak nama tabel dari nama model. Karena nama model ikut berakhiran NIM, setiap model **wajib** menyebut tabelnya sendiri, kalau tidak Eloquent akan mencari tabel yang tidak ada:
>
> ```php
> protected $table = 'nama_tabel_sebenarnya';
> ```
>
> Nama **tabel** tidak pernah diberi akhiran NIM. Tabel dipakai bersama seluruh anggota dan harus persis sama dengan ERD.

Agar tidak bentrok saat menggabungkan pekerjaan, letakkan rute milikmu di berkas terpisah `routes/modul-edukasi-dan-komunitas-365.php`, lalu daftarkan sekali saja di `routes/web.php`.

---

## 3. Rincian per PBI

### 3.1  PBI-16 — Manajemen Artikel Edukasi

`KF-17` · Kerumitan **Menengah** · Endpoint `/admin/artikel dan /edukasi`

**Kenapa tingkat kerumitannya segitu**

Punya status draf dan terbit, penyunting teks kaya, unggah gambar sampul, dan dua sudut
pandang: panel admin dan halaman baca publik.

**Tabel yang dimiliki PBI ini**

- `article_categories` — id, name, slug
- `articles` — id, user_id, article_category_id, title, summary, content, cover_image, status, published_at

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `users`, `article_comments`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Membuat artikel sebagai draf atau langsung terbit, beserta kategori, ringkasan, isi, dan gambar sampul. |
| **Read** | Panel admin menampilkan seluruh artikel beserta statusnya; halaman publik hanya menampilkan yang berstatus terbit. |
| **Update** | Menyunting isi dan mengubah status dari draf menjadi terbit atau sebaliknya. |
| **Delete** | Menghapus atau mencabut artikel. Pencabutan hanya mengubah status, balasan artikel tidak ikut hilang. |

**Aturan validasi**

- title wajib, maksimal 160 karakter
- slug dibuat otomatis dari judul dan harus unik
- summary wajib, maksimal 300 karakter
- content wajib, minimal 200 karakter
- cover_image opsional, jpg/png, maksimal 2 MB
- status wajib, salah satu dari: draf, terbit, dicabut

**Logika bisnis yang harus diperhatikan**

- published_at diisi otomatis saat status pertama kali menjadi terbit, dan tidak berubah lagi sesudahnya.
- Isi artikel wajib dibersihkan dari tag berbahaya sebelum ditampilkan.
- Pengunjung yang belum masuk tetap boleh membaca artikel terbit.

#### Skema Migrasi

```php
Schema::create('article_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->timestamps();
});
Schema::create('articles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('article_category_id')->constrained('article_categories')->cascadeOnDelete();
    $table->string('title');
    $table->text('summary')->nullable();
    $table->text('content')->nullable();
    $table->string('cover_image');
    $table->string('status');
    $table->timestamp('published_at')->nullable();
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/edukasi` | `artikel.publik` | Kisi artikel terbit | publik |
| `GET` | `/edukasi/{slug}` | `artikel.baca` | Halaman baca | publik |
| `GET` | `/admin/artikel` | `artikel.index` | Tabel seluruh artikel | auth + admin |
| `GET` | `/admin/artikel/tulis` | `artikel.create` | Penyunting | auth + admin |
| `POST` | `/admin/artikel` | `artikel.store` | Simpan draf / terbit | auth + admin |
| `PUT` | `/admin/artikel/{artikel}` | `artikel.update` | Sunting isi | auth + admin |
| `PATCH` | `/admin/artikel/{artikel}/status` | `artikel.status` | Ubah status terbit | auth + admin |
| `DELETE` | `/admin/artikel/{artikel}` | `artikel.destroy` | Hapus / cabut | auth + admin |

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/admin/artikel` — tabel artikel dengan penyaring status
- `/admin/artikel/tulis` — penyunting dua kolom: tulisan di kiri, pengaturan di kanan
- `/edukasi` — halaman publik berisi kisi kartu artikel
- `/edukasi/{slug}` — halaman baca

**Komponen antarmuka**

- Penyunting teks kaya dengan tombol dasar saja: tebal, miring, tautan, daftar, kutipan, gambar
- Pengunggah gambar sampul dengan pemotongan rasio 16 banding 9
- Penghitung kata dan taksiran lama baca yang berubah saat mengetik
- Tombol "Simpan Draf" dan "Terbitkan" terpisah, bukan satu menu tarik
- Kartu artikel di halaman publik: gambar, pelat kategori, judul, label "3 menit baca"
- Halaman baca dengan lebar teks maksimal 680px agar nyaman dibaca

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Halaman publik tanpa artikel terbit: "Artikel sedang disiapkan. Kembali lagi nanti." |
| **Memuat** | Kartu artikel menampilkan kerangka gambar dan dua baris teks. |
| **Galat** | Isi kurang dari 200 karakter: "Isi artikel minimal 200 karakter." Gambar terlalu besar: "Ukuran gambar maksimal 2 MB." |
| **Berhasil** | Setelah terbit, muncul notifikasi disertai tautan "Lihat di halaman publik". |

**Perilaku responsif**

- 1440px: penyunting dua kolom, kisi artikel tiga kolom
- 768px: pengaturan pindah ke bawah penyunting, kisi dua kolom
- 375px: kisi satu kolom, tombol penyunting menyusut jadi ikon

**Aksesibilitas**

- Setiap gambar wajib punya teks alternatif; penyunting menolak menerbitkan artikel yang gambarnya belum diberi keterangan
- Judul di halaman baca memakai hierarki heading yang benar
- Kontras teks pada gambar sampul diperiksa, bukan diletakkan begitu saja

**Komponen dan kelas CSS yang dipakai**

`.ct-penyunting`, `.ct-pemotong-gambar`, `<x-kartu>`, `.ct-pelat`, `.ct-isi-artikel`, `<x-tabel>`, `<x-kosong>`

#### Kasus Batas

- Judul artikel diubah setelah terbit: slug lama tetap dipakai agar tautan yang sudah dibagikan tidak mati.
- Dua artikel berjudul sama: slug kedua diberi akhiran angka.
- Artikel dicabut padahal sudah punya balasan: balasan tidak ikut hilang, hanya tidak terlihat publik.
- Gambar sampul gagal diunggah di tengah penyuntingan: isi artikel tidak ikut hilang, simpan draf dulu sebelum mengunggah.
- Penulis artikel dinonaktifkan: artikel tetap terbit, nama penulis ditampilkan apa adanya.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: title wajib
- [ ] Menolak ketika aturan dilanggar: slug dibuat otomatis dari judul dan harus unik
- [ ] Menolak ketika aturan dilanggar: summary wajib
- [ ] Menolak ketika aturan dilanggar: content wajib
- [ ] Menolak ketika aturan dilanggar: cover_image opsional
- [ ] Menolak ketika aturan dilanggar: status wajib
- [ ] Memastikan: artikel draf tidak muncul di halaman publik
- [ ] Memastikan: slug tidak pernah kembar
- [ ] Memastikan: gambar sampul tampil pada kartu artikel di halaman edukasi

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_article_categories_table_365.php` |
| Migration | `database/migrations/2026_01_01_0000_create_articles_table_365.php` |
| Model | `app/Models/ArticleCategorie365.php` |
| Model | `app/Models/Article365.php` |
| Controller | `app/Http/Controllers/ManajemenArtikelEdukasi365.php` |
| Form Request | `app/Http/Requests/SimpanManajemenArtikelEdukasi365.php` |
| Policy | `app/Policies/ManajemenArtikelEdukasiPolicy365.php` |
| View daftar | `resources/views/edukasi-dan-komunitas/manajemen-artikel-edukasi-365/index.blade.php` |
| View formulir | `resources/views/edukasi-dan-komunitas/manajemen-artikel-edukasi-365/form.blade.php` |
| Seeder | `database/seeders/ManajemenArtikelEdukasiSeeder365.php` |
| Feature Test | `tests/Feature/ManajemenArtikelEdukasiTest365.php` |

**Kriteria selesai**

- [ ] Artikel draf tidak muncul di halaman publik
- [ ] Slug tidak pernah kembar
- [ ] Gambar sampul tampil pada kartu artikel di halaman edukasi

---

### 3.2  PBI-17 — Manajemen Rekomendasi Aksi

`KF-18` · Kerumitan **Menengah** · Endpoint `/admin/rekomendasi`

**Kenapa tingkat kerumitannya segitu**

CRUD biasa, tetapi penayangannya dipicu oleh keadaan emisi pengguna, sehingga perlu
penyelarasan dengan modul Target.

**Tabel yang dimiliki PBI ini**

- `recommendations` — id, category_id, title, content, estimated_saving, threshold

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `categories`, `activities`, `targets`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Menambah rekomendasi beserta kategori sasaran, ambang pemicu, dan taksiran penghematannya. |
| **Read** | Daftar rekomendasi per kategori bagi admin; bagi pengguna, rekomendasi yang cocok dengan keadaan emisinya saat ini. |
| **Update** | Mengubah teks, ambang pemicu, atau taksiran penghematan. |
| **Delete** | Menghapus rekomendasi yang tidak dipakai. |

**Aturan validasi**

- title wajib, maksimal 140 karakter
- content wajib, minimal 80 karakter
- threshold wajib, desimal lebih besar dari 0
- estimated_saving wajib, desimal lebih besar dari 0
- category_id wajib dan harus ada

**Logika bisnis yang harus diperhatikan**

- Rekomendasi tampil jika emisi pengguna pada kategori tersebut melewati threshold dalam periode target berjalan.
- Urutan tampil berdasarkan estimated_saving terbesar, maksimal tiga buah sekaligus agar tidak membanjiri layar.
- Simak event dari PBI-10 ketika target terlampaui.

#### Skema Migrasi

```php
Schema::create('recommendations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
    $table->string('title');
    $table->text('content')->nullable();
    $table->decimal('estimated_saving', 12, 4);
    $table->decimal('threshold', 12, 4);
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/admin/rekomendasi` | `rekom.index` | Tabel per kategori | auth + admin |
| `POST` | `/admin/rekomendasi` | `rekom.store` | Tambah | auth + admin |
| `PUT` | `/admin/rekomendasi/{rekom}` | `rekom.update` | Ubah teks / ambang | auth + admin |
| `DELETE` | `/admin/rekomendasi/{rekom}` | `rekom.destroy` | Hapus | auth + admin |
| `GET` | `/api/rekomendasi/saya` | `rekom.saya` | Rekomendasi yang cocok untuk pengguna (JSON) | auth |
| `POST` | `/api/rekomendasi/{rekom}/tutup` | `rekom.tutup` | Sembunyikan 7 hari | auth |

#### API

`GET /api/rekomendasi/saya`

Dipanggil dashboard setelah angka emisi selesai dihitung. Mengembalikan maksimal tiga
rekomendasi, diurutkan dari penghematan terbesar.

```jsonc
{
  "data": [
    {
      "id": 9,
      "judul": "Matikan AC dua jam lebih awal",
      "isi": "Mengurangi dua jam pemakaian AC per malam memangkas sekitar ...",
      "kategori": { "nama": "Listrik", "warna": "#38BDF8" },
      "perkiraan_hemat": 8.40,
      "pemicu": "Emisi listrikmu 44,2 kg, melewati ambang 35 kg"
    }
  ]
}
```

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/admin/rekomendasi` — tabel dikelompokkan per kategori
- Rekomendasi bagi pengguna muncul sebagai kartu di dashboard, bukan halaman sendiri

**Komponen antarmuka**

- Kolom ambang pemicu dengan penjelasan: "Muncul kalau emisi kategori ini melewati angka ini dalam satu periode target"
- Tombol "Uji Pemicu" yang menampilkan pratinjau tanpa perlu mencatat sungguhan
- Kartu rekomendasi di dashboard: ikon kategori, judul, satu kalimat, taksiran penghematan, tombol tutup
- Maksimal tiga kartu sekaligus, sisanya di balik tautan "Lihat semua"

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Pengguna yang emisinya masih aman tidak melihat kartu apa pun. Itu keadaan normal, bukan keadaan kosong yang perlu diisi. |
| **Memuat** | Kartu rekomendasi muncul belakangan setelah angka emisi selesai dihitung, jangan menahan seluruh dashboard. |
| **Galat** | Ambang nol: "Ambang pemicu harus lebih besar dari nol." |
| **Berhasil** | Kartu yang ditutup pengguna tidak muncul lagi selama tujuh hari. |

**Perilaku responsif**

- 1440px: tiga kartu bersisian di dashboard
- 768px: dua kartu
- 375px: satu kartu, sisanya digeser mendatar

**Aksesibilitas**

- Tombol tutup punya label "Tutup rekomendasi tentang listrik"
- Kartu tidak muncul tiba-tiba menggeser isi halaman; ruangnya disiapkan dulu

**Komponen dan kelas CSS yang dipakai**

`<x-tabel>`, `<x-kartu>`, `.ct-kartu-rekomendasi`, `.ct-tombol-tutup`, `<x-isian>`

#### Kasus Batas

- Ambang pemicu diturunkan: rekomendasi muncul untuk lebih banyak pengguna pada pemuatan berikutnya, tidak perlu memuat ulang manual.
- Pengguna menutup rekomendasi lalu emisinya turun di bawah ambang, lalu naik lagi dalam tujuh hari: rekomendasi tetap tersembunyi sampai tujuh hari lewat.
- Kategori rekomendasi dihapus: rekomendasi ikut tidak aktif, bukan galat.
- Pengguna tidak punya target berjalan: rekomendasi tidak muncul sama sekali, karena ambangnya dihitung relatif terhadap periode target.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: title wajib
- [ ] Menolak ketika aturan dilanggar: content wajib
- [ ] Menolak ketika aturan dilanggar: threshold wajib
- [ ] Menolak ketika aturan dilanggar: estimated_saving wajib
- [ ] Menolak ketika aturan dilanggar: category_id wajib dan harus ada
- [ ] Memastikan: rekomendasi muncul hanya ketika ambangnya terlewati
- [ ] Memastikan: maksimal tiga rekomendasi tampil sekaligus
- [ ] Memastikan: admin dapat menguji pemicunya tanpa harus mencatat aktivitas sungguhan

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_recommendations_table_365.php` |
| Model | `app/Models/Recommendation365.php` |
| Controller | `app/Http/Controllers/ManajemenRekomendasiAksi365.php` |
| Form Request | `app/Http/Requests/SimpanManajemenRekomendasiAksi365.php` |
| Policy | `app/Policies/ManajemenRekomendasiAksiPolicy365.php` |
| View daftar | `resources/views/edukasi-dan-komunitas/manajemen-rekomendasi-aksi-365/index.blade.php` |
| View formulir | `resources/views/edukasi-dan-komunitas/manajemen-rekomendasi-aksi-365/form.blade.php` |
| Seeder | `database/seeders/ManajemenRekomendasiAksiSeeder365.php` |
| Feature Test | `tests/Feature/ManajemenRekomendasiAksiTest365.php` |

**Kriteria selesai**

- [ ] Rekomendasi muncul hanya ketika ambangnya terlewati
- [ ] Maksimal tiga rekomendasi tampil sekaligus
- [ ] Admin dapat menguji pemicunya tanpa harus mencatat aktivitas sungguhan

---

### 3.3  PBI-18 — Manajemen Pengumuman

`KF-19` · Kerumitan **Menengah** · Endpoint `/admin/pengumuman`

**Kenapa tingkat kerumitannya segitu**

PBI paling lugas di modul ini. Kerumitannya hanya pada periode tayang dan penyaringan
pengumuman yang sedang berlaku.

**Tabel yang dimiliki PBI ini**

- `announcements` — id, user_id, title, content, start_date, end_date

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `users`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Membuat pengumuman beserta periode tayangnya. |
| **Read** | Daftar pengumuman bagi admin; bagi pengguna, banner pada beranda berisi pengumuman yang sedang berlaku. |
| **Update** | Mengubah isi atau periode tayang. |
| **Delete** | Menghapus pengumuman. |

**Aturan validasi**

- title wajib, maksimal 140 karakter
- content wajib, minimal 20 karakter
- start_date wajib
- end_date wajib, harus setelah start_date

**Logika bisnis yang harus diperhatikan**

- Pengumuman berlaku jika hari ini berada di antara start_date dan end_date.
- Sediakan scope bernama scopeActive() agar modul lain dapat memanggilnya tanpa menulis ulang kondisinya.
- Maksimal satu banner tampil di beranda, ambil yang paling baru.

#### Skema Migrasi

```php
Schema::create('announcements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('title');
    $table->text('content')->nullable();
    $table->date('start_date');
    $table->date('end_date');
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/admin/pengumuman` | `umum.index` | Tabel pengumuman | auth + admin |
| `POST` | `/admin/pengumuman` | `umum.store` | Buat pengumuman | auth + admin |
| `PUT` | `/admin/pengumuman/{pengumuman}` | `umum.update` | Ubah isi / periode | auth + admin |
| `DELETE` | `/admin/pengumuman/{pengumuman}` | `umum.destroy` | Hapus | auth + admin |
| `GET` | `/api/pengumuman/aktif` | `umum.aktif` | Banner yang sedang berlaku (JSON) | auth |

#### API

`GET /api/pengumuman/aktif`

Dipakai banner di puncak dashboard. Mengembalikan paling banyak satu pengumuman, yang paling
baru di antara yang sedang berlaku.

```jsonc
{
  "ada": true,
  "pengumuman": {
    "id": 3,
    "judul": "Tantangan Oktober dibuka",
    "isi": "Ikuti tantangan Seminggu Tanpa Motor sampai 31 Oktober.",
    "mulai": "2026-10-01",
    "selesai": "2026-10-31"
  }
}
```

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/admin/pengumuman` — tabel dengan penanda sedang tayang
- Banner pengumuman di puncak dashboard pengguna

**Komponen antarmuka**

- Penanda hijau "Sedang tayang" pada baris yang periodenya sedang berlaku
- Pratinjau banner di formulir, persis seperti yang akan dilihat pengguna
- Banner pengguna: ikon, judul, satu kalimat, tombol tutup
- Banner dapat ditutup dan tidak muncul lagi untuk pengumuman yang sama

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Tidak ada pengumuman berlaku berarti tidak ada banner. Itu keadaan normal. |
| **Memuat** | Banner tidak menampilkan kerangka; ia muncul begitu datanya siap agar tidak membuat halaman melompat. |
| **Galat** | Tanggal akhir sebelum tanggal mulai: "Tanggal berakhir harus setelah tanggal mulai." |
| **Berhasil** | Pratinjau langsung memperlihatkan hasilnya sebelum disimpan. |

**Perilaku responsif**

- 1440px: banner selebar isi halaman
- 768px: sama, teks dipendekkan
- 375px: banner dua baris, tombol tutup tetap mudah dijangkau

**Aksesibilitas**

- Banner memakai peran region dengan label, bukan sekadar div berwarna
- Penutupan banner mengembalikan fokus ke tempat yang masuk akal

**Komponen dan kelas CSS yang dipakai**

`<x-tabel>`, `.ct-banner`, `.ct-tombol-tutup`, `<x-isian>`, `.ct-pratinjau`

#### Kasus Batas

- Dua pengumuman berlaku bersamaan: hanya yang paling baru tampil sebagai banner.
- Periode pengumuman diubah hingga menjadi kedaluwarsa: banner hilang pada pemuatan berikutnya.
- Pengguna menutup banner lalu isinya diubah admin: banner muncul lagi, karena yang disimpan adalah penutupan untuk versi lama.
- Pengumuman dibuat dengan periode di masa depan: tidak tampil sampai tanggalnya tiba, dan di tabel admin ditandai "Akan tayang".

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: title wajib
- [ ] Menolak ketika aturan dilanggar: content wajib
- [ ] Menolak ketika aturan dilanggar: start_date wajib
- [ ] Menolak ketika aturan dilanggar: end_date wajib
- [ ] Memastikan: pengumuman kedaluwarsa hilang sendiri dari beranda
- [ ] Memastikan: scopeActive() dipakai di seluruh tempat, tidak ada kondisi yang ditulis ulang
- [ ] Memastikan: pengguna biasa tidak dapat membuat pengumuman

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_announcements_table_365.php` |
| Model | `app/Models/Announcement365.php` |
| Controller | `app/Http/Controllers/ManajemenPengumuman365.php` |
| Form Request | `app/Http/Requests/SimpanManajemenPengumuman365.php` |
| Policy | `app/Policies/ManajemenPengumumanPolicy365.php` |
| View daftar | `resources/views/edukasi-dan-komunitas/manajemen-pengumuman-365/index.blade.php` |
| View formulir | `resources/views/edukasi-dan-komunitas/manajemen-pengumuman-365/form.blade.php` |
| Seeder | `database/seeders/ManajemenPengumumanSeeder365.php` |
| Feature Test | `tests/Feature/ManajemenPengumumanTest365.php` |

**Kriteria selesai**

- [ ] Pengumuman kedaluwarsa hilang sendiri dari beranda
- [ ] scopeActive() dipakai di seluruh tempat, tidak ada kondisi yang ditulis ulang
- [ ] Pengguna biasa tidak dapat membuat pengumuman

---

### 3.4  PBI-19 — Manajemen Balasan Artikel

`KF-28` · Kerumitan **Menengah** · Endpoint `/artikel/{artikel}/balasan`

**Kenapa tingkat kerumitannya segitu**

Punya relasi ke dirinya sendiri lewat parent_id agar balasan bisa berjenjang, ditambah
antrean moderasi. Relasi berulang inilah yang membuatnya tidak selugas kelihatannya.

**Tabel yang dimiliki PBI ini**

- `article_comments` — id, article_id, user_id, parent_id, content, is_approved, created_at

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `articles`, `users`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Pengguna terdaftar menulis balasan pada artikel, atau membalas balasan lain lewat parent_id. |
| **Read** | Daftar balasan berjenjang di bawah artikel; bagi admin, antrean balasan yang menunggu persetujuan. |
| **Update** | Penulis dapat menyunting balasannya sendiri dalam 15 menit pertama; admin dapat menyetujui atau menolak kapan saja. |
| **Delete** | Penulis dapat menghapus balasannya sendiri. Balasan yang sudah punya anak tidak dihapus, hanya isinya diganti penanda bahwa balasan telah dihapus. |

**Aturan validasi**

- content wajib, 2 sampai 1000 karakter
- article_id wajib dan artikelnya harus berstatus terbit
- parent_id opsional, jika diisi harus balasan pada artikel yang sama
- kedalaman balasan dibatasi maksimal tiga tingkat

**Logika bisnis yang harus diperhatikan**

- parent_id menunjuk ke article_comments.id, inilah relasi berulang yang digambar sebagai loop pada ERD dan class diagram.
- Pakai relasi parent() dan replies() pada model, dan muat bertingkat dengan eager loading agar tidak terjadi kueri beruntun.
- Balasan baru berstatus menunggu, is_approved bernilai 0, sampai admin menyetujuinya.
- Menghapus artikel induk ikut menghapus seluruh balasannya lewat cascade.

#### Skema Migrasi

```php
Schema::create('article_comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('parent_id')->nullable()->constrained('article_comments')->cascadeOnDelete();
    $table->text('content')->nullable();
    $table->boolean('is_approved')->default(true);
    $table->timestamp('created_at')->nullable();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `POST` | `/artikel/{artikel}/balasan` | `balasan.store` | Tulis balasan | auth |
| `PUT` | `/balasan/{balasan}` | `balasan.update` | Sunting (15 menit pertama) | auth + penulis |
| `DELETE` | `/balasan/{balasan}` | `balasan.destroy` | Hapus balasan | auth + penulis |
| `GET` | `/admin/balasan` | `balasan.antrean` | Antrean moderasi | auth + admin |
| `PATCH` | `/admin/balasan/{balasan}/setujui` | `balasan.setujui` | Setujui | auth + admin |
| `PATCH` | `/admin/balasan/{balasan}/tolak` | `balasan.tolak` | Tolak | auth + admin |

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- Balasan tampil di bawah isi artikel pada `/edukasi/{slug}`
- `/admin/balasan` — antrean moderasi bagi administrator

**Komponen antarmuka**

- Kolom tulis balasan dengan foto penulis di sebelahnya
- Balasan berjenjang ditandai garis vertikal dan lekukan ke kanan, maksimal tiga tingkat
- Tombol "Balas" di setiap balasan membuka kolom tulis tepat di bawahnya
- Pelat "Menunggu persetujuan" pada balasan sendiri yang belum disetujui
- Antrean moderasi dengan tombol Setujui dan Tolak berdampingan
- Balasan terhapus yang punya anak diganti teks miring abu-abu "Balasan ini telah dihapus"

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | "Belum ada balasan. Jadi yang pertama berbagi pendapat." |
| **Memuat** | Tiga kerangka balasan. Balasan baru muncul langsung di layar sebelum jawaban server datang, lalu dikoreksi jika gagal. |
| **Galat** | Melebihi tiga tingkat: tombol Balas tidak muncul, bukan muncul lalu ditolak. Artikel belum terbit: "Artikel ini belum terbit." |
| **Berhasil** | Balasan muncul dengan pelat "Menunggu persetujuan" dan penjelasan singkat bahwa balasan ditinjau dulu. |

**Perilaku responsif**

- 1440px: lekukan 32px per tingkat
- 768px: lekukan 20px per tingkat
- 375px: lekukan 12px, tingkat tiga ditampilkan rata dengan penanda "membalas @nama" agar tidak terlalu sempit

**Aksesibilitas**

- Jenjang balasan memakai daftar bersarang sungguhan agar pembaca layar memahami hubungannya
- Tombol Balas menyebut siapa yang dibalas: "Balas balasan dari Rani"
- Kolom tulis mendapat fokus otomatis setelah tombol Balas ditekan

**Komponen dan kelas CSS yang dipakai**

`.ct-balasan`, `.ct-balasan--jenjang`, `<x-isian>`, `.ct-pelat`, `<x-tabel>`, `<x-kosong>`, `.ct-avatar`

#### Kasus Batas

- Balasan tingkat tiga ingin dibalas lagi: tombol Balas tidak muncul sama sekali, bukan muncul lalu ditolak setelah diketik.
- Balasan induk dihapus padahal punya anak: isinya diganti penanda, barisnya tetap ada agar jenjang tidak putus.
- Artikel induk dihapus: seluruh balasannya ikut terhapus lewat cascade.
- Penulis menyunting balasan pada menit ke-16: ditolak, batas 15 menit dihitung dari created_at di sisi server, bukan dari jam peramban.
- Balasan ditolak admin lalu penulis menulis lagi isi yang sama: diperbolehkan, tetapi masuk antrean lagi.
- Artikel dengan 200 balasan: wajib dimuat bertahap, jangan sekaligus.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: content wajib
- [ ] Menolak ketika aturan dilanggar: article_id wajib dan artikelnya harus berstatus terbit
- [ ] Menolak ketika aturan dilanggar: parent_id opsional
- [ ] Menolak ketika aturan dilanggar: kedalaman balasan dibatasi maksimal tiga tingkat
- [ ] Memastikan: balasan tiga tingkat tampil berjenjang dengan benar
- [ ] Memastikan: balasan menunggu tidak terlihat oleh pengguna lain
- [ ] Memastikan: menghapus balasan bercabang tidak menghilangkan balasan anaknya
- [ ] Memastikan: memuat artikel dengan 50 balasan tidak menimbulkan kueri beruntun

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_article_comments_table_365.php` |
| Model | `app/Models/ArticleComment365.php` |
| Controller | `app/Http/Controllers/ManajemenBalasanArtikel365.php` |
| Form Request | `app/Http/Requests/SimpanManajemenBalasanArtikel365.php` |
| Policy | `app/Policies/ManajemenBalasanArtikelPolicy365.php` |
| View daftar | `resources/views/edukasi-dan-komunitas/manajemen-balasan-artikel-365/index.blade.php` |
| View formulir | `resources/views/edukasi-dan-komunitas/manajemen-balasan-artikel-365/form.blade.php` |
| Seeder | `database/seeders/ManajemenBalasanArtikelSeeder365.php` |
| Feature Test | `tests/Feature/ManajemenBalasanArtikelTest365.php` |

**Kriteria selesai**

- [ ] Balasan tiga tingkat tampil berjenjang dengan benar
- [ ] Balasan menunggu tidak terlihat oleh pengguna lain
- [ ] Menghapus balasan bercabang tidak menghilangkan balasan anaknya
- [ ] Memuat artikel dengan 50 balasan tidak menimbulkan kueri beruntun

---

## 4. Acuan Tampilan Modul Ini

Dua berkas prompt Figma menjadi acuan: versi wireframe hitam putih untuk struktur, dan versi berwarna untuk tampilan akhir.

- Frame 1 Landing bagian Edukasi: tiga kartu artikel dengan pelat kategori dan label "3 menit baca".
- Frame 4 Dashboard: menu sidebar "Edukasi", serta banner pengumuman di bagian atas beranda.

## Sistem Desain

Bagian ini sama persis di seluruh README anggota. Disalin ke tiap berkas dengan
sengaja, supaya satu README cukup diserahkan sendirian kepada AI agent tanpa
perlu berkas pendamping.

**Aturan tunggal yang paling penting.** Jangan pernah menulis nilai warna,
ukuran font, atau jarak langsung di Blade. Semuanya memakai variabel CSS dan
komponen di bawah ini. Kalau butuh sesuatu yang belum ada, tambahkan ke
komponen bersama, jangan dibuat sendiri di satu halaman.

### Token CSS

Letakkan di `resources/css/app.css`, dibuat sekali oleh Front-End Lead.

```css
:root {
  /* permukaan */
  --ct-bg:            #06130E;   /* latar halaman */
  --ct-bg-alt:        #0A0F0D;   /* bagian selang-seling */
  --ct-surface:       #111A17;   /* kartu, tabel, panel */
  --ct-surface-raised:#1A2622;   /* modal, menu, panel geser */
  --ct-border:        #1E2B26;   /* bingkai dan pemisah */

  /* makna */
  --ct-primary:       #10B981;   /* tombol utama, tautan, penanda aktif */
  --ct-accent:        #A3E635;   /* gradien, sorotan angka */
  --ct-warning:       #F59E0B;   /* target terpakai 70-99% */
  --ct-danger:        #F43F5E;   /* galat, hapus, target terlampaui */
  --ct-info:          #38BDF8;   /* catatan netral */

  /* teks */
  --ct-text:          #E8EDEB;
  --ct-text-muted:    #8CA099;

  /* jarak, kelipatan 4 */
  --ct-s1: 4px;  --ct-s2: 8px;  --ct-s3: 12px; --ct-s4: 16px;
  --ct-s5: 24px; --ct-s6: 32px; --ct-s7: 48px; --ct-s8: 64px;

  /* sudut */
  --ct-r-card: 16px;
  --ct-r-ctrl: 12px;
  --ct-r-pill: 999px;

  /* lainnya */
  --ct-font: 'Plus Jakarta Sans', system-ui, sans-serif;
  --ct-ring: 0 0 0 3px rgb(16 185 129 / 0.45);   /* bingkai fokus */
  --ct-max:  1200px;                              /* lebar isi maksimal */
  --ct-prose: 680px;                              /* lebar paragraf maksimal */
}

* { box-sizing: border-box; }
body {
  margin: 0;
  background: var(--ct-bg);
  color: var(--ct-text);
  font-family: var(--ct-font);
  font-size: 16px;
  line-height: 1.6;
}
:focus-visible { outline: none; box-shadow: var(--ct-ring); border-radius: var(--ct-r-ctrl); }

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after { animation: none !important; transition: none !important; }
}
```

### Skala tipografi

| Peran | Ukuran | Ketebalan | Tinggi baris |
|---|---|---|---|
| Judul besar | 48px | 800 | 1,1 |
| Judul halaman | 32px | 700 | 1,2 |
| Judul bagian | 24px | 700 | 1,3 |
| Judul kartu | 18px | 700 | 1,4 |
| Isi | 16px | 400 | 1,6 |
| Keterangan | 14px | 400 | 1,5 |
| Label kecil | 12px | 500 | 1,4 |
| Angka besar | 40px | 800 | 1,0 |

Paragraf tidak boleh lebih lebar dari `--ct-prose`.

### Jarak baku

| Konteks | Nilai |
|---|---|
| Antarelemen dalam satu kartu | `--ct-s3` |
| Isi kartu ke bingkainya | `--ct-s5` |
| Antarkartu | `--ct-s5` |
| Antarbagian halaman | `--ct-s8` |
| Tepi halaman di layar lebar | 120px |
| Tepi halaman di ponsel | `--ct-s4` |

### CSS komponen

```css
/* ---------- tombol ---------- */
.ct-tombol {
  display:inline-flex; align-items:center; justify-content:center; gap:var(--ct-s2);
  height:48px; padding:0 var(--ct-s5); border-radius:var(--ct-r-ctrl);
  font-weight:600; font-size:15px; border:1px solid transparent; cursor:pointer;
  transition:filter .15s ease;
}
.ct-tombol--utama    { background:linear-gradient(90deg,var(--ct-primary),var(--ct-accent)); color:var(--ct-bg); }
.ct-tombol--sekunder { background:var(--ct-surface); border-color:var(--ct-border); color:var(--ct-text); }
.ct-tombol--hantu    { background:transparent; color:var(--ct-primary); padding:0 var(--ct-s3); }
.ct-tombol--bahaya   { background:var(--ct-danger); color:#fff; }
.ct-tombol:hover:not(:disabled) { filter:brightness(1.08); }
.ct-tombol:disabled  { opacity:.5; cursor:not-allowed; }
/* saat memuat: ukuran TIDAK berubah, hanya isinya */
.ct-tombol[aria-busy="true"] { pointer-events:none; }
@media (max-width:640px) { .ct-tombol { height:52px; width:100%; } }

/* ---------- kolom isian ---------- */
.ct-isian-grup { display:flex; flex-direction:column; gap:var(--ct-s2); }
.ct-label      { font-size:14px; color:var(--ct-text-muted); }
.ct-isian {
  height:48px; padding:0 var(--ct-s4); border-radius:var(--ct-r-ctrl);
  background:var(--ct-surface); border:1px solid var(--ct-border);
  color:var(--ct-text); font-family:inherit; font-size:15px; width:100%;
}
.ct-isian::placeholder { color:var(--ct-text-muted); opacity:.7; }
.ct-isian[aria-invalid="true"] { border-color:var(--ct-danger); }
.ct-galat { font-size:13px; color:var(--ct-danger); }     /* tepat di bawah kolomnya */
.ct-isian-satuan { position:relative; }
.ct-isian-satuan > span {
  position:absolute; right:var(--ct-s4); top:50%; transform:translateY(-50%);
  color:var(--ct-text-muted); font-size:14px; pointer-events:none;
}

/* ---------- kartu ---------- */
.ct-kartu {
  background:var(--ct-surface); border:1px solid var(--ct-border);
  border-radius:var(--ct-r-card); padding:var(--ct-s5);
}
.ct-kartu__judul { font-size:18px; font-weight:700; margin:0 0 var(--ct-s3); }

/* ---------- pelat ---------- */
.ct-pelat {
  display:inline-flex; align-items:center; gap:var(--ct-s1);
  padding:2px var(--ct-s3); border-radius:var(--ct-r-pill);
  font-size:12px; font-weight:500; border:1px solid var(--ct-border);
}
.ct-pelat--aman       { color:var(--ct-primary); }
.ct-pelat--hampir     { color:var(--ct-warning); }
.ct-pelat--terlampaui { color:var(--ct-danger); }

/* ---------- tabel ---------- */
.ct-tabel { width:100%; border-collapse:collapse; }
.ct-tabel th {
  text-align:left; font-size:12px; font-weight:600; letter-spacing:.04em;
  color:var(--ct-text-muted); padding:var(--ct-s3) var(--ct-s4);
  border-bottom:1px solid var(--ct-border);
}
.ct-tabel td { padding:var(--ct-s4); border-bottom:1px solid var(--ct-border); }
.ct-tabel .angka { text-align:right; font-variant-numeric:tabular-nums; }
/* di ponsel tabel menjadi kartu, TIDAK digeser mendatar */
@media (max-width:640px) {
  .ct-tabel, .ct-tabel tbody, .ct-tabel tr, .ct-tabel td { display:block; width:100%; }
  .ct-tabel thead { display:none; }
  .ct-tabel tr { border:1px solid var(--ct-border); border-radius:var(--ct-r-card);
                 margin-bottom:var(--ct-s3); padding:var(--ct-s3); }
  .ct-tabel td { border:0; display:flex; justify-content:space-between; }
  .ct-tabel td::before { content:attr(data-label); color:var(--ct-text-muted); }
}

/* ---------- kerangka memuat ---------- */
/* ukurannya WAJIB sama dengan isi aslinya supaya halaman tidak melompat */
.ct-kerangka {
  background:linear-gradient(90deg,var(--ct-surface),var(--ct-surface-raised),var(--ct-surface));
  background-size:200% 100%; animation:ct-denyut 1.4s ease-in-out infinite;
  border-radius:var(--ct-r-ctrl);
}
@keyframes ct-denyut { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ---------- keadaan kosong ---------- */
.ct-kosong { text-align:center; padding:var(--ct-s8) var(--ct-s5); }
.ct-kosong__ikon  { color:var(--ct-text-muted); opacity:.6; margin-bottom:var(--ct-s4); }
.ct-kosong__teks  { color:var(--ct-text-muted); margin-bottom:var(--ct-s5); }

/* ---------- cincin kemajuan (target) ---------- */
.ct-cincin { --nilai:0; width:240px; aspect-ratio:1; border-radius:50%;
  background:conic-gradient(var(--ct-warna) calc(var(--nilai)*1%), var(--ct-border) 0);
  display:grid; place-items:center; }
.ct-cincin::after { content:''; width:76%; aspect-ratio:1; border-radius:50%;
  background:var(--ct-surface); grid-area:1/1; }
.ct-cincin__isi { grid-area:1/1; z-index:1; text-align:center; }
@media (max-width:1024px){ .ct-cincin{width:180px} }
@media (max-width:640px) { .ct-cincin{width:140px} }

/* ---------- batang kemajuan ---------- */
.ct-kemajuan { height:8px; background:var(--ct-border); border-radius:var(--ct-r-pill); overflow:hidden; }
.ct-kemajuan__isi { height:100%; background:var(--ct-primary); border-radius:inherit; }

/* ---------- tabel pendamping grafik (untuk pembaca layar) ---------- */
.ct-tabel-tersembunyi {
  position:absolute; width:1px; height:1px; overflow:hidden;
  clip:rect(0 0 0 0); white-space:nowrap;
}
```

### Komponen Blade bersama

Dibuat **sekali** oleh Hadi selaku Front-End Lead pada minggu pertama Sprint 1,
sebelum siapa pun mulai membuat halaman. Letaknya `resources/views/components/`.

| Komponen | Atribut | Catatan |
|---|---|---|
| `<x-tombol>` | `varian` (utama, sekunder, hantu, bahaya), `memuat` | Ukuran tidak berubah saat memuat |
| `<x-isian>` | `label`, `nama`, `satuan`, `galat` | Label selalu di atas, bukan teks bayangan |
| `<x-kartu>` | `judul` | |
| `<x-tabel>` | `kepala` | Otomatis jadi kartu di 640px |
| `<x-pelat>` | `varian` | Selalu berisi tulisan, bukan warna saja |
| `<x-modal>` | `judul`, `bahaya` | Menahan fokus, tutup dengan Esc |
| `<x-panel-geser>` | `judul`, `lebar` | Untuk formulir, daftar di belakang tetap terlihat |
| `<x-notifikasi>` | `jenis` | Kanan atas, hilang 4 detik, maksimal satu |
| `<x-kosong>` | `ikon`, `pesan`, `aksi` | Wajib untuk setiap daftar, tabel, dan grafik |
| `<x-kerangka>` | `jenis` (teks, kartu, tabel, grafik, angka) | Seukuran isi aslinya |
| `<x-tab>` | `item`, `aktif` | Dipindah dengan tombol panah |
| `<x-kartu-statistik>` | `label`, `angka`, `satuan` | |

### Empat keadaan yang wajib ada di setiap layar

| Keadaan | Aturan |
|---|---|
| **Kosong** | Ikon atau ilustrasi, satu kalimat yang menjelaskan kenapa kosong dan apa untungnya diisi, satu tombol menuju aksi pengisinya. Jangan pernah menulis "Data tidak ditemukan". |
| **Memuat** | Kerangka abu-abu seukuran isi aslinya, bukan lingkaran berputar di tengah halaman. Tiap bagian memuat sendiri-sendiri. |
| **Galat** | Galat satu bagian hanya merusak bagian itu. Selalu ada tombol "Coba lagi". Galat formulir muncul tepat di bawah kolomnya, bukan dikumpulkan di atas. |
| **Berhasil** | Umpan balik yang terlihat: notifikasi, baris baru yang disorot singkat, atau angka yang berubah. |

### Tata letak dan navigasi

| Kerangka | Dipakai untuk | Isi |
|---|---|---|
| Publik | Landing, kalkulator, edukasi, komunitas, masuk, daftar | Navbar pelat mengambang, isi di tengah maksimal `--ct-max`, footer empat kolom |
| Aplikasi | Seluruh halaman setelah masuk | Sidebar kiri 240px, bilah atas 72px, isi di kanan |

Sidebar berisi delapan menu berurutan: Dashboard, Catat Aktivitas, Riwayat,
Target, Tantangan, Papan Peringkat, Edukasi, Profil. Menu admin menjadi kelompok
terpisah di bawah, hanya bagi yang berperan administrator. Halaman yang sedang
dibuka ditandai garis warna utama di sisi kiri **dan** teks lebih terang, dua
penanda, bukan satu.

| Lebar | Perilaku |
|---|---|
| 1440px ke atas | Tata letak penuh, sidebar terbuka |
| 1024px | Isi menyempit, sidebar tetap terbuka |
| 768px | Sidebar menyusut jadi ikon saja |
| 375px | Sidebar jadi menu bawah lima ikon, isi satu kolom, aksi utama menempel di bawah layar |

### Aturan penulisan teks antarmuka

Seluruh teks berbahasa Indonesia, tanpa campuran.

| Jangan tulis | Tulis |
|---|---|
| Submit | Simpan |
| Delete | Hapus |
| Loading... | Memuat... |
| Data tidak valid | Jumlah harus lebih besar dari nol |
| Error 500 | Gagal memuat. Coba lagi. |
| Record berhasil di-create | Catatan tersimpan |
| Emission factor | Faktor emisi |

- Sapa pengguna dengan "kamu", bukan "Anda".
- Tombol memakai kata kerja yang menyebut hasilnya: "Simpan Perubahan", bukan "OK".
- Angka: koma untuk desimal, titik untuk ribuan — `1.840` dan `0,681`.
- Satuan emisi selalu `kg CO2e`, dengan spasi.
- Tanggal `26 Oktober 2026`, bukan `2026-10-26`, kecuali di tabel sempit.
- Tanpa tanda seru, kecuali modal perayaan lencana.

### Delapan aturan aksesibilitas yang wajib

1. Setiap kolom isian punya `label` terhubung lewat `for` dan `id`.
2. Seluruh alur selesai hanya dengan papan ketik, termasuk modal dan panel geser.
3. Bingkai fokus terlihat jelas; `--ct-ring` tidak boleh dihilangkan.
4. Setiap gambar punya teks alternatif; gambar hiasan diberi `alt=""`.
5. Setiap grafik didampingi tabel angka `.ct-tabel-tersembunyi`.
6. Angka yang berubah tanpa muat ulang memakai `aria-live="polite"`.
7. Animasi mati saat `prefers-reduced-motion` aktif.
8. Kontras teks minimal 4,5 banding 1; warna tidak pernah jadi satu-satunya pembeda.

---

## 5. Urutan Pengerjaan

| Sprint | Minggu | Yang dikerjakan |
|---|---|---|
| Sprint 1 | Week 7–10 | Persiapan dan dukungan modul lain |
| Sprint 2 | Week 11–15 | PBI-16, PBI-17, PBI-18, PBI-19 |

---

## 6. Catatan Khusus

- Memegang empat PBI, satu lebih banyak dari anggota lain, karena PBI-19 balasan artikel
  ditambahkan atas saran dosen.
- PBI-19 bergantung pada PBI-16. Kerjakan artikelnya dulu sampai bisa terbit, baru
  balasannya.
- PBI-17 perlu menyimak event dari modul Target milik Alvin. Sepakati nama event-nya sebelum
  mulai.

---

## 7. Sebelum Mengajukan Permintaan Gabung

- [ ] Seluruh kriteria selesai pada setiap PBI sudah tercentang
- [ ] Keempat keadaan layar sudah dibuat untuk setiap halaman: kosong, memuat, galat, dan berhasil
- [ ] Sudah dicoba pada lebar 1440px, 768px, dan 375px
- [ ] Seluruh alur dapat diselesaikan hanya dengan papan ketik
- [ ] Seluruh teks antarmuka berbahasa Indonesia dan tanpa istilah teknis
- [ ] Nama berkas sudah berakhiran `365` dan nama kelas di dalamnya cocok
- [ ] Setiap model sudah menyebut `protected $table`
- [ ] Migration dapat dijalankan dari nol tanpa galat (`migrate:fresh --seed`)
- [ ] Tidak ada perubahan pada tabel milik anggota lain
- [ ] Feature test untuk setiap PBI sudah lulus
- [ ] Sudah diuji Renaya selaku Quality Assurance
- [ ] Sudah ditinjau Alvin selaku pemegang cabang utama

