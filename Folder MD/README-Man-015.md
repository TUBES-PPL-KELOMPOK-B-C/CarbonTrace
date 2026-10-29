# README Pengembangan — Man Rezki Setiawan

**Proyek** CarbonTrace — Platform Pelacakan dan Pemangkasan Jejak Karbon Harian  
**NIM** 102022430015  
**Peran** Developer  
**Modul** Gamifikasi  
**Akhiran berkas** `015` (tiga digit terakhir NIM)  
**Jumlah PBI** 3

---

## 1. Ruang Lingkup

Membangun mekanisme yang membuat pengguna bertahan memakai produk: lencana, tantangan
berjangka, pencatatan keikutsertaan, dan pemberian poin. Modul ini yang menulis angka poin
yang kemudian dibaca papan peringkat pada modul Akun.

### Yang dikerjakan

| PBI | KF | Nama Fitur | Kerumitan |
|---|---|---|---|
| PBI-13 | KF-14 | Manajemen Lencana | Menengah |
| PBI-14 | KF-15 | Manajemen Tantangan | Menengah |
| PBI-15 | KF-16 | Keikutsertaan Tantangan | Tinggi |

### Fitur pendukung (tidak dihitung sebagai PBI)

**Tidak ada fitur pendukung non-PBI**

Papan peringkat (KF-26) memakai data modul ini, namun dikerjakan pada modul Akun dan
Administrasi oleh Rosi.

---

## 2. Lingkungan dan Konvensi Penamaan

**Tumpukan teknologi:** Laravel 12, PHP 8.3, MySQL 8, Blade + Tailwind, ApexCharts, spatie/laravel-permission, barryvdh/laravel-dompdf.

Setiap berkas yang kamu buat diberi akhiran **`015`** sebelum ekstensinya, supaya terlihat siapa yang menulisnya. Nama kelas di dalam berkas harus sama persis dengan nama berkasnya.

```
app/Http/Controllers/ManajemenLencana015.php
   -> class ManajemenLencana015 extends Controller
```

> **Catatan penting.** Laravel menebak nama tabel dari nama model. Karena nama model ikut berakhiran NIM, setiap model **wajib** menyebut tabelnya sendiri, kalau tidak Eloquent akan mencari tabel yang tidak ada:
>
> ```php
> protected $table = 'nama_tabel_sebenarnya';
> ```
>
> Nama **tabel** tidak pernah diberi akhiran NIM. Tabel dipakai bersama seluruh anggota dan harus persis sama dengan ERD.

Agar tidak bentrok saat menggabungkan pekerjaan, letakkan rute milikmu di berkas terpisah `routes/modul-gamifikasi-015.php`, lalu daftarkan sekali saja di `routes/web.php`.

---

## 3. Rincian per PBI

### 3.1  PBI-13 — Manajemen Lencana

`KF-14` · Kerumitan **Menengah** · Endpoint `/admin/lencana`

**Kenapa tingkat kerumitannya segitu**

CRUD lugas ditambah mesin pemeriksa syarat yang berjalan setiap kali poin atau emisi
pengguna berubah.

**Tabel yang dimiliki PBI ini**

- `badges` — id, name, description, icon, type, required_value, is_active
- `badge_user` — user_id, badge_id, earned_at

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `users`, `point_logs`, `activities`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Menambah lencana beserta jenis syarat dan nilai ambang perolehannya. |
| **Read** | Galeri lencana beserta jumlah pengguna yang sudah meraihnya; bagi pengguna, galeri miliknya sendiri beserta status terkunci atau terbuka. |
| **Update** | Mengubah nama, gambar, jenis syarat, atau nilai ambang. |
| **Delete** | Menonaktifkan lencana. Lencana yang sudah diraih tidak boleh dihapus permanen karena badge_user merujuk padanya. |

**Aturan validasi**

- name wajib, maksimal 80 karakter, unik
- type wajib, salah satu dari: poin, streak, penurunan_emisi, tantangan
- required_value wajib, desimal lebih besar dari 0
- icon wajib, maksimal 40 karakter

**Logika bisnis yang harus diperhatikan**

- Pemeriksaan syarat diletakkan di satu kelas Service, dipanggil lewat event setelah aktivitas dicatat atau poin bertambah.
- Satu lencana hanya boleh diraih sekali per pengguna. Tegakkan dengan kunci unik gabungan pada badge_user.
- Mengubah nilai ambang tidak mencabut lencana yang sudah diraih.

#### Skema Migrasi

```php
Schema::create('badges', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('icon');
    $table->string('type');
    $table->decimal('required_value', 12, 4);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
Schema::create('badge_user', function (Blueprint $table) {
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('badge_id')->constrained('badges')->cascadeOnDelete();
    $table->timestamp('earned_at')->nullable();
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/lencana` | `lencana.galeri` | Galeri lencana pengguna | auth |
| `GET` | `/admin/lencana` | `lencana.index` | Kisi kartu admin | auth + admin |
| `POST` | `/admin/lencana` | `lencana.store` | Tambah lencana | auth + admin |
| `PUT` | `/admin/lencana/{lencana}` | `lencana.update` | Ubah syarat | auth + admin |
| `PATCH` | `/admin/lencana/{lencana}/status` | `lencana.status` | Aktif / nonaktif | auth + admin |
| `DELETE` | `/admin/lencana/{lencana}` | `lencana.destroy` | Hapus (hanya yang belum diraih) | auth + admin |

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/admin/lencana` — kisi kartu lencana bagi admin
- `/lencana` — galeri lencana bagi pengguna

**Komponen antarmuka**

- Lencana terkunci ditampilkan abu-abu dengan gembok dan teks syaratnya
- Lencana terbuka ditampilkan berwarna dengan tanggal perolehan
- Batang kemajuan kecil di bawah lencana terkunci: "840 dari 1000 poin"
- Perayaan singkat saat lencana baru diraih: konfeti sederhana dan modal

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Galeri pengguna baru menampilkan seluruh lencana dalam keadaan terkunci beserta syaratnya, bukan halaman kosong. |
| **Memuat** | Kisi dua belas kartu kerangka. |
| **Galat** | Menghapus lencana yang sudah diraih: "Lencana ini sudah diraih 24 pengguna. Nonaktifkan saja." |
| **Berhasil** | Modal perayaan muncul sekali, dapat ditutup, dan tidak muncul lagi untuk lencana yang sama. |

**Perilaku responsif**

- 1440px: kisi enam kolom
- 768px: empat kolom
- 375px: tiga kolom

**Aksesibilitas**

- Konfeti dimatikan jika pengguna memilih kurangi gerak
- Status terkunci atau terbuka disebut dalam teks, tidak hanya lewat warna abu-abu

**Komponen dan kelas CSS yang dipakai**

`.ct-lencana`, `.ct-lencana--terkunci`, `.ct-kemajuan`, `<x-modal>`, `<x-kerangka jenis="kartu">`

#### Kasus Batas

- Syarat lencana diturunkan admin: pengguna yang sebelumnya belum memenuhi syarat langsung meraihnya pada pemeriksaan berikutnya, dan itu benar.
- Syarat dinaikkan: lencana yang sudah diraih tidak dicabut.
- Dua aktivitas tercatat hampir bersamaan dan keduanya memicu lencana yang sama: kunci unik pada badge_user mencegah baris ganda.
- Lencana dinonaktifkan: hilang dari galeri publik, tetapi tetap terlihat pada pengguna yang sudah meraihnya.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: name wajib
- [ ] Menolak ketika aturan dilanggar: type wajib
- [ ] Menolak ketika aturan dilanggar: required_value wajib
- [ ] Menolak ketika aturan dilanggar: icon wajib
- [ ] Memastikan: lencana otomatis diberikan ketika syaratnya terpenuhi
- [ ] Memastikan: tidak muncul baris ganda pada badge_user
- [ ] Memastikan: lencana yang pernah diraih tidak dapat dihapus permanen

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_badges_table_015.php` |
| Migration | `database/migrations/2026_01_01_0000_create_badge_user_table_015.php` |
| Model | `app/Models/Badge015.php` |
| Model | `app/Models/BadgeUser015.php` |
| Controller | `app/Http/Controllers/ManajemenLencana015.php` |
| Form Request | `app/Http/Requests/SimpanManajemenLencana015.php` |
| Policy | `app/Policies/ManajemenLencanaPolicy015.php` |
| View daftar | `resources/views/gamifikasi/manajemen-lencana-015/index.blade.php` |
| View formulir | `resources/views/gamifikasi/manajemen-lencana-015/form.blade.php` |
| Seeder | `database/seeders/ManajemenLencanaSeeder015.php` |
| Feature Test | `tests/Feature/ManajemenLencanaTest015.php` |

**Kriteria selesai**

- [ ] Lencana otomatis diberikan ketika syaratnya terpenuhi
- [ ] Tidak muncul baris ganda pada badge_user
- [ ] Lencana yang pernah diraih tidak dapat dihapus permanen

---

### 3.2  PBI-14 — Manajemen Tantangan

`KF-15` · Kerumitan **Menengah** · Endpoint `/admin/tantangan`

**Kenapa tingkat kerumitannya segitu**

CRUD dengan periode berjangka dan status yang berubah sendiri mengikuti tanggal, sehingga
perlu perhatian pada peralihan status.

**Tabel yang dimiliki PBI ini**

- `challenges` — id, category_id, title, description, start_date, end_date, reduction_target, reward_points

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `categories`, `challenge_user`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Membuat tantangan: judul, uraian, kategori sasaran, periode, target penurunan, dan poin hadiah. |
| **Read** | Daftar tantangan berjalan, akan datang, dan telah berakhir, beserta jumlah pesertanya. |
| **Update** | Mengubah periode, target, atau poin hadiah selama tantangan belum dimulai. |
| **Delete** | Membatalkan tantangan. Jika sudah ada peserta, tantangan hanya ditutup, bukan dihapus. |

**Aturan validasi**

- title wajib, maksimal 120 karakter
- start_date wajib, tidak boleh di masa lalu saat dibuat
- end_date wajib, minimal tujuh hari setelah start_date
- reduction_target wajib, desimal lebih besar dari 0
- reward_points wajib, bilangan bulat 10 sampai 1000

**Logika bisnis yang harus diperhatikan**

- Tantangan yang sudah punya peserta tidak boleh diubah periodenya, karena kemajuan peserta dihitung dari rentang itu.
- Status berjalan, akan datang, dan berakhir dihitung dari tanggal, bukan disimpan sebagai kolom.

#### Skema Migrasi

```php
Schema::create('challenges', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
    $table->string('title');
    $table->text('description')->nullable();
    $table->date('start_date');
    $table->date('end_date');
    $table->decimal('reduction_target', 12, 4);
    $table->integer('reward_points')->default(0);
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/admin/tantangan` | `tantangan.index` | Tiga tab status | auth + admin |
| `POST` | `/admin/tantangan` | `tantangan.store` | Buat tantangan | auth + admin |
| `PUT` | `/admin/tantangan/{tantangan}` | `tantangan.update` | Ubah (hanya jika belum berpeserta) | auth + admin |
| `DELETE` | `/admin/tantangan/{tantangan}` | `tantangan.destroy` | Batalkan / tutup | auth + admin |
| `GET` | `/admin/tantangan/{tantangan}/peserta` | `tantangan.peserta` | Daftar peserta | auth + admin |

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/admin/tantangan` — tabel dengan tiga tab: Berjalan, Akan Datang, Berakhir
- Panel geser untuk tambah dan ubah

**Komponen antarmuka**

- Garis waktu kecil di tiap baris yang menunjukkan posisi hari ini dalam periode
- Pelat status berwarna beserta tulisannya
- Kolom jumlah peserta yang dapat diklik untuk melihat daftarnya
- Kotak peringatan saat mengubah tantangan berpeserta

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | "Belum ada tantangan. Buat tantangan pertama untuk mendorong satu perubahan kebiasaan dalam periode tertentu." |
| **Memuat** | Kerangka tabel lima baris. |
| **Galat** | Periode kurang dari tujuh hari: "Tantangan minimal berjalan tujuh hari." Mengubah tantangan berpeserta: "Tantangan ini sudah diikuti 18 orang, periodenya tidak bisa diubah." |
| **Berhasil** | Baris baru muncul di tab yang sesuai dengan tanggalnya. |

**Perilaku responsif**

- 1440px: tabel tujuh kolom
- 768px: garis waktu disembunyikan
- 375px: kartu per tantangan

**Aksesibilitas**

- Garis waktu didampingi teks "Hari ke-4 dari 14"
- Tab berpindah dengan tombol panah

**Komponen dan kelas CSS yang dipakai**

`<x-tab>`, `<x-tabel>`, `.ct-garis-waktu`, `.ct-pelat`, `<x-panel-geser>`, `.ct-catatan`

#### Kasus Batas

- Tantangan dibuat dengan tanggal mulai hari ini: langsung berstatus berjalan dan bisa diikuti seketika.
- Admin menutup tantangan yang sedang berjalan: peserta tidak kehilangan poin yang sudah diperoleh, statusnya menjadi dibatalkan.
- Kategori tantangan dihapus: tantangan tetap ada, kategorinya ditandai kosong.
- Tantangan berakhir tepat tengah malam: peralihan status memakai zona waktu Asia/Jakarta, bukan UTC.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: title wajib
- [ ] Menolak ketika aturan dilanggar: start_date wajib
- [ ] Menolak ketika aturan dilanggar: end_date wajib
- [ ] Menolak ketika aturan dilanggar: reduction_target wajib
- [ ] Menolak ketika aturan dilanggar: reward_points wajib
- [ ] Memastikan: tantangan dengan peserta tidak dapat diubah periodenya
- [ ] Memastikan: status berganti sendiri saat tanggal terlewat
- [ ] Memastikan: poin hadiah tersalurkan hanya satu kali per peserta

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_challenges_table_015.php` |
| Model | `app/Models/Challenge015.php` |
| Controller | `app/Http/Controllers/ManajemenTantangan015.php` |
| Form Request | `app/Http/Requests/SimpanManajemenTantangan015.php` |
| Policy | `app/Policies/ManajemenTantanganPolicy015.php` |
| View daftar | `resources/views/gamifikasi/manajemen-tantangan-015/index.blade.php` |
| View formulir | `resources/views/gamifikasi/manajemen-tantangan-015/form.blade.php` |
| Seeder | `database/seeders/ManajemenTantanganSeeder015.php` |
| Feature Test | `tests/Feature/ManajemenTantanganTest015.php` |

**Kriteria selesai**

- [ ] Tantangan dengan peserta tidak dapat diubah periodenya
- [ ] Status berganti sendiri saat tanggal terlewat
- [ ] Poin hadiah tersalurkan hanya satu kali per peserta

---

### 3.3  PBI-15 — Keikutsertaan Tantangan

`KF-16` · Kerumitan **Tinggi** · Endpoint `/tantangan-saya`

**Kenapa tingkat kerumitannya segitu**

Kemajuan peserta harus dihitung dari emisi nyata pada rentang tantangan, dibandingkan dengan
emisi sebelum ikut. Perhitungan pembanding inilah yang membuatnya berat.

**Tabel yang dimiliki PBI ini**

- `challenge_user` — user_id, challenge_id, status, progress, joined_at
- `point_logs` — id, user_id, points, reason, created_at

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `challenges`, `activities`, `users`, `badges`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Mendaftarkan pengguna ke satu tantangan dan mengunci garis dasar emisinya pada saat itu. |
| **Read** | Daftar tantangan yang diikuti beserta persentase kemajuan dan sisa waktunya. |
| **Update** | Memperbarui status menjadi berjalan, selesai, atau gagal, beserta angka kemajuannya. |
| **Delete** | Mengundurkan diri dari tantangan. Poin yang sudah diperoleh tidak ditarik kembali. |

**Aturan validasi**

- Satu pengguna hanya boleh mendaftar satu kali per tantangan
- Pendaftaran ditolak jika tantangan sudah berakhir
- status hanya menerima: berjalan, selesai, gagal, mundur
- progress desimal 0 sampai 100

**Logika bisnis yang harus diperhatikan**

- Garis dasar pembanding diambil dari rata-rata emisi harian pengguna pada periode yang sama panjangnya sebelum ia mendaftar.
- progress = persentase penurunan terhadap garis dasar itu, dibatasi 100.
- Perhitungan ulang dijalankan lewat penjadwal harian, bukan pada setiap pemuatan halaman.
- Poin hadiah ditulis ke point_logs dan menjadi sumber angka papan peringkat (PBI-20) milik modul Akun.

#### Skema Migrasi

```php
Schema::create('challenge_user', function (Blueprint $table) {
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('challenge_id')->constrained('challenges')->cascadeOnDelete();
    $table->string('status');
    $table->decimal('progress', 12, 4);
    $table->timestamp('joined_at')->nullable();
    $table->timestamps();
});
Schema::create('point_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->integer('points')->default(0);
    $table->string('reason');
    $table->timestamp('created_at')->nullable();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/tantangan` | `ikut.daftar` | Tantangan yang bisa diikuti | auth |
| `GET` | `/tantangan-saya` | `ikut.index` | Tantangan yang diikuti | auth |
| `POST` | `/tantangan/{tantangan}/ikuti` | `ikut.store` | Daftar ikut serta | auth |
| `DELETE` | `/tantangan/{tantangan}/mundur` | `ikut.destroy` | Mengundurkan diri | auth + pemilik |
| `GET` | `/api/tantangan-saya/progres` | `ikut.progres` | Kemajuan seluruh tantangan (JSON) | auth |

#### API

`GET /api/tantangan-saya/progres`

Dipakai batang kemajuan pada kartu tantangan. Angkanya dihitung ulang oleh penjadwal harian,
bukan pada setiap pemuatan halaman.

```jsonc
{
  "data": [
    {
      "tantangan_id": 4,
      "judul": "Seminggu Tanpa Motor",
      "status": "berjalan",
      "progres": 62.5,
      "garis_dasar": 4.80,
      "rata_sekarang": 1.80,
      "sisa_hari": 3,
      "poin_hadiah": 150
    }
  ]
}
```

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/tantangan` — daftar tantangan yang bisa diikuti
- `/tantangan-saya` — tantangan yang sedang diikuti beserta kemajuannya

**Komponen antarmuka**

- Kartu tantangan: judul, kategori, sisa hari, target penurunan, poin hadiah
- Batang kemajuan dengan persentase dan angka nyata di bawahnya
- Tombol "Ikuti" berubah jadi "Sedang Diikuti" setelah ditekan
- Penanda sisa waktu yang berubah warna saat tersisa kurang dari tiga hari
- Modal konfirmasi saat mengundurkan diri, menjelaskan poin tidak ditarik

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Belum ikut apa pun: "Kamu belum mengikuti tantangan. Ikut satu untuk mengubah kebiasaan dalam periode tertentu." |
| **Memuat** | Batang kemajuan menampilkan kerangka berdenyut, bukan nol, supaya tidak terbaca sebagai kemajuan nol. |
| **Galat** | Tantangan sudah berakhir: "Tantangan ini sudah ditutup." Pendaftaran ganda: "Kamu sudah mengikuti tantangan ini." |
| **Berhasil** | Kartu berpindah sendiri ke bagian Sedang Diikuti dengan animasi singkat, disertai notifikasi. |

**Perilaku responsif**

- 1440px: kisi tiga kolom
- 768px: dua kolom
- 375px: satu kolom, tombol ikuti melebar penuh

**Aksesibilitas**

- Batang kemajuan memakai aria-valuenow dan punya padanan teks
- Perubahan tombol menjadi "Sedang Diikuti" diumumkan lewat aria-live
- Sisa waktu tidak hanya ditandai warna, ada tulisan "Sisa 2 hari"

**Komponen dan kelas CSS yang dipakai**

`<x-kartu>`, `.ct-kemajuan`, `.ct-pelat`, `<x-modal>`, `<x-kosong>`, `<x-notifikasi>`

#### Kasus Batas

- Pengguna baru tanpa riwayat emisi mendaftar tantangan: garis dasar tidak bisa dihitung, pakai rata-rata seluruh pengguna sebagai pengganti dan beri catatan.
- Pengguna mengundurkan diri lalu mendaftar lagi pada tantangan yang sama: diperbolehkan, tetapi garis dasar dihitung ulang dari saat pendaftaran kedua.
- Pengguna tidak mencatat apa pun selama tantangan: kemajuan nol, bukan 100 persen. Nol catatan bukan berarti nol emisi.
- Tantangan selesai saat penjadwal sedang berjalan: hindari poin ganda dengan memeriksa point_logs sebelum menulis.
- Poin hadiah diberikan lalu tantangan dibatalkan admin: poin tidak ditarik kembali, sesuai yang dijanjikan di layar.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: Satu pengguna hanya boleh mendaftar satu kali per tantangan
- [ ] Menolak ketika aturan dilanggar: Pendaftaran ditolak jika tantangan sudah berakhir
- [ ] Menolak ketika aturan dilanggar: status hanya menerima: berjalan
- [ ] Menolak ketika aturan dilanggar: progress desimal 0 sampai 100
- [ ] Memastikan: pendaftaran ganda ditolak lewat kunci unik di basis data
- [ ] Memastikan: kemajuan terhitung benar untuk kasus uji penurunan 20 persen
- [ ] Memastikan: poin hadiah muncul di point_logs tepat satu kali
- [ ] Memastikan: papan peringkat membaca angka ini tanpa perubahan apa pun di modul Akun

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_challenge_user_table_015.php` |
| Migration | `database/migrations/2026_01_01_0000_create_point_logs_table_015.php` |
| Model | `app/Models/ChallengeUser015.php` |
| Model | `app/Models/PointLog015.php` |
| Controller | `app/Http/Controllers/KeikutsertaanTantangan015.php` |
| Form Request | `app/Http/Requests/SimpanKeikutsertaanTantangan015.php` |
| Policy | `app/Policies/KeikutsertaanTantanganPolicy015.php` |
| View daftar | `resources/views/gamifikasi/keikutsertaan-tantangan-015/index.blade.php` |
| View formulir | `resources/views/gamifikasi/keikutsertaan-tantangan-015/form.blade.php` |
| Seeder | `database/seeders/KeikutsertaanTantanganSeeder015.php` |
| Feature Test | `tests/Feature/KeikutsertaanTantanganTest015.php` |

**Kriteria selesai**

- [ ] Pendaftaran ganda ditolak lewat kunci unik di basis data
- [ ] Kemajuan terhitung benar untuk kasus uji penurunan 20 persen
- [ ] Poin hadiah muncul di point_logs tepat satu kali
- [ ] Papan peringkat membaca angka ini tanpa perubahan apa pun di modul Akun

---

## 4. Acuan Tampilan Modul Ini

Dua berkas prompt Figma menjadi acuan: versi wireframe hitam putih untuk struktur, dan versi berwarna untuk tampilan akhir.

- Frame 4 Dashboard: menu sidebar "Tantangan" dan "Papan Peringkat".
- Frame 1 Landing bagian Fitur, kartu "Tantangan & Lencana".

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
| Sprint 2 | Week 11–15 | PBI-13, PBI-14, PBI-15 |

---

## 6. Catatan Khusus

- Modul ini menulis ke point_logs. Sepakati bentuk kolom reason bersama Rosi sebelum mulai,
  karena papan peringkat menyaring berdasarkan kolom itu.
- PBI-15 adalah yang terberat di modul ini. Dahulukan PBI-13 dan PBI-14 supaya ada data
  untuk diuji.
- Seluruh pemberian poin lewat satu kelas Service, jangan menulis ke point_logs dari banyak
  tempat.

---

## 7. Sebelum Mengajukan Permintaan Gabung

- [ ] Seluruh kriteria selesai pada setiap PBI sudah tercentang
- [ ] Keempat keadaan layar sudah dibuat untuk setiap halaman: kosong, memuat, galat, dan berhasil
- [ ] Sudah dicoba pada lebar 1440px, 768px, dan 375px
- [ ] Seluruh alur dapat diselesaikan hanya dengan papan ketik
- [ ] Seluruh teks antarmuka berbahasa Indonesia dan tanpa istilah teknis
- [ ] Nama berkas sudah berakhiran `015` dan nama kelas di dalamnya cocok
- [ ] Setiap model sudah menyebut `protected $table`
- [ ] Migration dapat dijalankan dari nol tanpa galat (`migrate:fresh --seed`)
- [ ] Tidak ada perubahan pada tabel milik anggota lain
- [ ] Feature test untuk setiap PBI sudah lulus
- [ ] Sudah diuji Renaya selaku Quality Assurance
- [ ] Sudah ditinjau Alvin selaku pemegang cabang utama

