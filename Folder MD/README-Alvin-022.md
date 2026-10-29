# README Pengembangan — Alvin Hibatullah

**Proyek** CarbonTrace — Platform Pelacakan dan Pemangkasan Jejak Karbon Harian  
**NIM** 102022430022  
**Peran** Ketua Kelompok, Scrum Master merangkap Developer  
**Modul** Target, Pengingat, dan Laporan  
**Akhiran berkas** `022` (tiga digit terakhir NIM)  
**Jumlah PBI** 3

---

## 1. Ruang Lingkup

Membangun sisi yang paling sering dilihat pengguna setelah masuk: penetapan target bulanan,
pengingat agar pencatatan tidak bolong, dan laporan yang dapat diunduh. Merangkap mengatur
jalannya sprint, menggabungkan hasil kerja seluruh anggota, dan menjaga agar cabang utama
selalu dalam keadaan dapat dijalankan.

### Yang dikerjakan

| PBI | KF | Nama Fitur | Kerumitan |
|---|---|---|---|
| PBI-10 | KF-11 | Manajemen Target Emisi Bulanan | Menengah |
| PBI-11 | KF-12 | Pengingat Pencatatan Harian | Menengah |
| PBI-12 | KF-13 | Laporan Jejak Karbon | Tinggi |

### Fitur pendukung (tidak dihitung sebagai PBI)

**KF-21 Dashboard Jejak Karbon**

Halaman utama setelah masuk. Hanya menampilkan hasil PBI milik modul lain, tidak mengelola
data sendiri.

**KF-22 Grafik Tren Emisi**

Grafik garis tujuh hari terakhir, turunan tampilan dari data PBI-04.

**KF-23 Rincian Emisi per Kategori**

Grafik donat empat kategori, baca saja dari data yang sama.

---

## 2. Lingkungan dan Konvensi Penamaan

**Tumpukan teknologi:** Laravel 12, PHP 8.3, MySQL 8, Blade + Tailwind, ApexCharts, spatie/laravel-permission, barryvdh/laravel-dompdf.

Setiap berkas yang kamu buat diberi akhiran **`022`** sebelum ekstensinya, supaya terlihat siapa yang menulisnya. Nama kelas di dalam berkas harus sama persis dengan nama berkasnya.

```
app/Http/Controllers/ManajemenTargetEmisiBulanan022.php
   -> class ManajemenTargetEmisiBulanan022 extends Controller
```

> **Catatan penting.** Laravel menebak nama tabel dari nama model. Karena nama model ikut berakhiran NIM, setiap model **wajib** menyebut tabelnya sendiri, kalau tidak Eloquent akan mencari tabel yang tidak ada:
>
> ```php
> protected $table = 'nama_tabel_sebenarnya';
> ```
>
> Nama **tabel** tidak pernah diberi akhiran NIM. Tabel dipakai bersama seluruh anggota dan harus persis sama dengan ERD.

Agar tidak bentrok saat menggabungkan pekerjaan, letakkan rute milikmu di berkas terpisah `routes/modul-target-pengingat-dan-laporan-022.php`, lalu daftarkan sekali saja di `routes/web.php`.

---

## 3. Rincian per PBI

### 3.1  PBI-10 — Manajemen Target Emisi Bulanan

`KF-11` · Kerumitan **Menengah** · Endpoint `/target-emisi`

**Kenapa tingkat kerumitannya segitu**

Perlu menjaga agar periode target tidak saling tumpang tindih, dan sisa kuotanya dihitung
langsung dari tabel activities milik modul lain.

**Tabel yang dimiliki PBI ini**

- `targets` — id, user_id, period_start, period_end, emission_limit

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `activities`, `users`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Menetapkan batas emisi untuk satu periode bulanan. |
| **Read** | Target berjalan beserta sisa kuota, persentase terpakai, dan riwayat target bulan-bulan sebelumnya. |
| **Update** | Mengubah besaran target selama periodenya belum berakhir. |
| **Delete** | Membatalkan target yang sudah tidak relevan. |

**Aturan validasi**

- period_start dan period_end wajib, period_end harus setelah period_start
- periode tidak boleh tumpang tindih dengan target lain milik pengguna sama
- emission_limit wajib, desimal lebih besar dari 0, maksimal 10000

**Logika bisnis yang harus diperhatikan**

- Sisa kuota = emission_limit dikurangi jumlah emisi activities pada rentang periode tersebut. Hitung lewat kueri agregasi, jangan disimpan sebagai kolom.
- Ketika pemakaian melewati 100 persen, modul Edukasi menampilkan rekomendasi aksi (PBI-17). Sediakan event agar modul itu bisa menyimaknya.
- Pengecekan tumpang tindih wajib memakai aturan validasi tersendiri, bukan pemeriksaan sambil lalu di controller.

#### Skema Migrasi

```php
Schema::create('targets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('period_start');
    $table->string('period_end');
    $table->decimal('emission_limit', 12, 4);
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/target-emisi` | `target.index` | Target berjalan dan riwayat | auth |
| `POST` | `/target-emisi` | `target.store` | Tetapkan target | auth |
| `PUT` | `/target-emisi/{target}` | `target.update` | Ubah besaran | auth + pemilik |
| `DELETE` | `/target-emisi/{target}` | `target.destroy` | Batalkan target | auth + pemilik |
| `GET` | `/api/target/berjalan` | `target.berjalan` | Sisa kuota periode berjalan (JSON) | auth |

#### API

`GET /api/target/berjalan`

Dipakai kartu target di halaman target dan kartu sisa kuota di dashboard.

```jsonc
{
  "ada_target": true,
  "periode": { "mulai": "2026-10-01", "selesai": "2026-10-31" },
  "batas": 167.00,
  "terpakai": 112.40,
  "sisa": 54.60,
  "persen": 67.3,
  "status": "hampir",          // aman | hampir | terlampaui
  "sisa_hari": 11
}
```

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/target-emisi` — kartu target berjalan di atas, riwayat target di bawah
- Panel geser untuk menetapkan dan mengubah target

**Komponen antarmuka**

- Cincin kemajuan besar di kartu target berjalan, menampilkan persentase terpakai di tengahnya
- Cincin berubah warna: hijau di bawah 70 persen, kuning 70 sampai 99, merah di atas 100
- Teks pendamping: "112,4 dari 167 kg CO2e. Sisa 54,6 kg untuk 11 hari."
- Pemilih periode dengan pintasan "Bulan ini" dan "Bulan depan"
- Kartu riwayat target dengan penanda tercapai atau terlampaui

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | "Belum ada target. Tetapkan batas emisi bulananmu agar kemajuanmu bisa diukur." disertai saran angka berdasarkan rata-rata emisi tiga puluh hari terakhir. |
| **Memuat** | Cincin kemajuan berputar pelan sebagai kerangka. |
| **Galat** | Periode bertabrakan: "Kamu sudah punya target untuk periode ini. Ubah target yang ada atau pilih periode lain." |
| **Berhasil** | Cincin beranimasi dari nol ke posisi sebenarnya selama satu detik, lalu notifikasi "Target ditetapkan." |

**Perilaku responsif**

- 1440px: cincin 240px di kiri, rincian di kanan
- 768px: cincin mengecil jadi 180px dan pindah ke atas rincian
- 375px: cincin 140px, teks pendamping dipendekkan

**Aksesibilitas**

- Cincin kemajuan punya padanan teks yang selalu terbaca, tidak hanya grafis
- Warna hijau, kuning, merah didampingi label tertulis Aman, Hampir, Terlampaui
- Animasi cincin dimatikan jika pengguna memilih kurangi gerak di sistemnya

**Komponen dan kelas CSS yang dipakai**

`.ct-cincin`, `<x-kartu>`, `<x-panel-geser>`, `<x-isian>`, `.ct-pelat`, `<x-kosong>`

#### Kasus Batas

- Pengguna membuat target untuk bulan depan lalu mengubah periodenya hingga bertabrakan dengan target berjalan: ditolak oleh aturan tumpang tindih yang sama, termasuk saat mengubah, bukan hanya saat membuat.
- Target dibuat di tengah bulan: sisa kuota dihitung dari tanggal mulai target, bukan dari tanggal 1.
- Pengguna menghapus seluruh catatannya: persentase kembali ke nol, bukan galat pembagian dengan nol.
- Emisi melewati 100 persen lalu catatan dikoreksi hingga kembali di bawah batas: rekomendasi aksi yang sempat muncul wajib ikut hilang.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: period_start dan period_end wajib
- [ ] Menolak ketika aturan dilanggar: periode tidak boleh tumpang tindih dengan target lain milik pengguna sama
- [ ] Menolak ketika aturan dilanggar: emission_limit wajib
- [ ] Memastikan: dua target dengan periode bertabrakan ditolak
- [ ] Memastikan: sisa kuota berubah tepat setelah satu aktivitas dicatat
- [ ] Memastikan: target kedaluwarsa pindah ke bagian riwayat secara otomatis

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_targets_table_022.php` |
| Model | `app/Models/Target022.php` |
| Controller | `app/Http/Controllers/ManajemenTargetEmisiBulanan022.php` |
| Form Request | `app/Http/Requests/SimpanManajemenTargetEmisiBulanan022.php` |
| Policy | `app/Policies/ManajemenTargetEmisiBulananPolicy022.php` |
| View daftar | `resources/views/target-pengingat-dan-laporan/manajemen-target-emisi-bulanan-022/index.blade.php` |
| View formulir | `resources/views/target-pengingat-dan-laporan/manajemen-target-emisi-bulanan-022/form.blade.php` |
| Seeder | `database/seeders/ManajemenTargetEmisiBulananSeeder022.php` |
| Feature Test | `tests/Feature/ManajemenTargetEmisiBulananTest022.php` |

**Kriteria selesai**

- [ ] Dua target dengan periode bertabrakan ditolak
- [ ] Sisa kuota berubah tepat setelah satu aktivitas dicatat
- [ ] Target kedaluwarsa pindah ke bagian riwayat secara otomatis

---

### 3.2  PBI-11 — Pengingat Pencatatan Harian

`KF-12` · Kerumitan **Menengah** · Endpoint `/pengingat`

**Kenapa tingkat kerumitannya segitu**

Melibatkan penjadwal yang berjalan di latar belakang, bukan hanya formulir. Perlu pengaturan
cron dan penanganan zona waktu.

**Tabel yang dimiliki PBI ini**

- `reminders` — id, user_id, remind_time, is_active

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `users`, `activities`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Membuat jadwal pengingat pada jam tertentu. |
| **Read** | Daftar pengingat aktif milik pengguna beserta jam tayangnya. |
| **Update** | Mengubah jam pengingat atau menyalakan dan mematikannya. |
| **Delete** | Menghapus pengingat. |

**Aturan validasi**

- remind_time wajib, format jam 24 jam
- maksimal tiga pengingat aktif per pengguna
- tidak boleh ada dua pengingat pada jam yang sama untuk satu pengguna

**Logika bisnis yang harus diperhatikan**

- Pakai Laravel Scheduler dengan perintah tersendiri yang berjalan setiap menit dan memeriksa pengingat yang jatuh tempo.
- Pengingat dilewati jika pengguna sudah mencatat aktivitas pada hari itu.
- Seluruh waktu disimpan dalam zona Asia/Jakarta agar tidak bergeser.

#### Skema Migrasi

```php
Schema::create('reminders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->time('remind_time');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/pengingat` | `pengingat.index` | Daftar pengingat | auth |
| `POST` | `/pengingat` | `pengingat.store` | Tambah pengingat | auth |
| `PUT` | `/pengingat/{pengingat}` | `pengingat.update` | Ubah jam | auth + pemilik |
| `PATCH` | `/pengingat/{pengingat}/saklar` | `pengingat.saklar` | Nyala / mati | auth + pemilik |
| `DELETE` | `/pengingat/{pengingat}` | `pengingat.destroy` | Hapus | auth + pemilik |

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/pengingat` — daftar pengingat dalam bentuk kartu, maksimal tiga

**Komponen antarmuka**

- Pemilih jam berbentuk roda putar, bukan ketik manual
- Saklar aktif atau nonaktif di setiap kartu
- Kartu kosong bertanda tambah sebagai tombol tambah selama masih di bawah tiga
- Catatan kecil: "Pengingat dilewati kalau kamu sudah mencatat hari itu."

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | "Belum ada pengingat. Pencatatan yang bolong membuat tren emisimu sulit dibaca." |
| **Memuat** | Tiga kartu kerangka. |
| **Galat** | Pengingat keempat: "Maksimal tiga pengingat aktif." Jam kembar: "Sudah ada pengingat di jam ini." |
| **Berhasil** | Notifikasi "Pengingat disetel pukul 20.00." |

**Perilaku responsif**

- 1440px: tiga kartu bersisian
- 768px: dua kolom
- 375px: satu kolom

**Aksesibilitas**

- Roda putar jam punya alternatif kolom ketik untuk pengguna papan ketik
- Saklar memakai elemen checkbox sungguhan dengan label yang terhubung

**Komponen dan kelas CSS yang dipakai**

`<x-kartu>`, `.ct-roda-jam`, `.ct-saklar`, `.ct-kartu-tambah`, `<x-kosong>`

#### Kasus Batas

- Pengguna berada di zona waktu lain: seluruh waktu disimpan dan dibandingkan dalam Asia/Jakarta, jangan memakai zona waktu peramban.
- Penjadwal mati beberapa jam lalu hidup lagi: pengingat yang terlewat tidak dikirim susulan menumpuk, cukup dilewati.
- Pengguna mencatat aktivitas tepat satu menit sebelum jam pengingat: pengingat dilewati.
- Pengguna menonaktifkan akun: seluruh pengingatnya ikut berhenti.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: remind_time wajib
- [ ] Menolak ketika aturan dilanggar: maksimal tiga pengingat aktif per pengguna
- [ ] Menolak ketika aturan dilanggar: tidak boleh ada dua pengingat pada jam yang sama untuk satu pengguna
- [ ] Memastikan: pengingat terkirim pada jam yang ditetapkan
- [ ] Memastikan: pengguna yang sudah mencatat hari itu tidak menerima pengingat
- [ ] Memastikan: pengingat keempat ditolak dengan pesan jelas

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_reminders_table_022.php` |
| Model | `app/Models/Reminder022.php` |
| Controller | `app/Http/Controllers/PengingatPencatatanHarian022.php` |
| Form Request | `app/Http/Requests/SimpanPengingatPencatatanHarian022.php` |
| Policy | `app/Policies/PengingatPencatatanHarianPolicy022.php` |
| View daftar | `resources/views/target-pengingat-dan-laporan/pengingat-pencatatan-harian-022/index.blade.php` |
| View formulir | `resources/views/target-pengingat-dan-laporan/pengingat-pencatatan-harian-022/form.blade.php` |
| Seeder | `database/seeders/PengingatPencatatanHarianSeeder022.php` |
| Feature Test | `tests/Feature/PengingatPencatatanHarianTest022.php` |

**Kriteria selesai**

- [ ] Pengingat terkirim pada jam yang ditetapkan
- [ ] Pengguna yang sudah mencatat hari itu tidak menerima pengingat
- [ ] Pengingat keempat ditolak dengan pesan jelas

---

### 3.3  PBI-12 — Laporan Jejak Karbon

`KF-13` · Kerumitan **Tinggi** · Endpoint `/laporan`

**Kenapa tingkat kerumitannya segitu**

PBI terberat. Menggabungkan agregasi lintas tabel, penyusunan grafik, dan ekspor PDF. Mesin
PDF tidak menjalankan JavaScript, sehingga grafiknya tidak bisa memakai pustaka grafik
biasa.

**Tabel yang dimiliki PBI ini**

- `saved_reports` — id, user_id, title, period_start, period_end, category_scope

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `activities`, `emission_factors`, `categories`, `targets`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Menyusun laporan untuk rentang tanggal dan lingkup kategori pilihan. |
| **Read** | Membuka laporan tersimpan, melihat ringkasannya di layar, dan mengunduhnya sebagai PDF. |
| **Update** | Mengubah judul, rentang tanggal, atau lingkup kategori lalu menyusun ulang. |
| **Delete** | Menghapus laporan tersimpan. Data aktivitas aslinya tidak ikut terhapus. |

**Aturan validasi**

- title wajib, maksimal 120 karakter
- period_start dan period_end wajib, rentang maksimal 366 hari
- period_end tidak boleh melewati hari ini
- category_scope opsional, harus berisi id kategori yang ada

**Logika bisnis yang harus diperhatikan**

- dompdf tidak menjalankan JavaScript, jadi grafik di PDF wajib digambar sebagai SVG di sisi server atau disisipkan sebagai gambar. ApexCharts hanya dipakai untuk tampilan di layar.
- Laporan tidak menyimpan salinan angka. Setiap kali dibuka, angkanya dihitung ulang dari activities, sehingga koreksi catatan ikut tercermin.
- Penyusunan PDF dijalankan lewat antrean jika rentangnya panjang, agar permintaan web tidak kehabisan waktu.
- Satu kelas Service menyiapkan seluruh angkanya, dipakai bersama oleh tampilan layar dan berkas PDF.

#### Skema Migrasi

```php
Schema::create('saved_reports', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('title');
    $table->string('period_start');
    $table->string('period_end');
    $table->string('category_scope');
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/laporan` | `laporan.index` | Daftar laporan tersimpan | auth |
| `GET` | `/laporan/susun` | `laporan.create` | Formulir dengan pratinjau | auth |
| `POST` | `/laporan` | `laporan.store` | Simpan laporan | auth |
| `GET` | `/laporan/{laporan}` | `laporan.show` | Tampilan di layar | auth + pemilik |
| `GET` | `/laporan/{laporan}/unduh` | `laporan.unduh` | Unduh PDF | auth + pemilik |
| `PUT` | `/laporan/{laporan}` | `laporan.update` | Ubah rentang atau judul | auth + pemilik |
| `DELETE` | `/laporan/{laporan}` | `laporan.destroy` | Hapus laporan | auth + pemilik |
| `POST` | `/api/laporan/pratinjau` | `laporan.pratinjau` | Angka ringkas tanpa menyimpan (JSON) | auth |

#### API

`POST /api/laporan/pratinjau`

Mengisi pratinjau di sisi kanan formulir penyusunan laporan tanpa menyimpan baris
saved_reports.

```jsonc
// kirim
{ "period_start": "2026-10-01", "period_end": "2026-10-31", "kategori": [1,2] }

// terima
{
  "total": 112.40,
  "rata_harian": 3.63,
  "per_kategori": [
    { "id": 1, "nama": "Transportasi", "emisi": 68.20, "persen": 60.7, "warna": "#10B981" },
    { "id": 2, "nama": "Listrik",      "emisi": 44.20, "persen": 39.3, "warna": "#38BDF8" }
  ],
  "tren": [ { "tanggal": "2026-10-01", "emisi": 3.1 } ],
  "perkiraan_halaman": 3
}
```

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/laporan` — daftar laporan tersimpan
- `/laporan/susun` — formulir penyusunan dengan pratinjau langsung
- `/laporan/{id}` — tampilan laporan di layar dengan tombol unduh

**Komponen antarmuka**

- Pemilih rentang tanggal dengan pintasan: 7 hari, 30 hari, bulan ini, periode target
- Pemilih kategori berbentuk kotak centang, bawaan semuanya tercentang
- Pratinjau laporan di sisi kanan yang ikut berubah saat penyaring diubah
- Tombol "Unduh PDF" dengan ikon, dan kotak kecil penjelas berapa halaman
- Kartu laporan tersimpan berisi judul, rentang, dan tanggal pembuatan

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | "Belum ada laporan tersimpan. Susun laporan pertamamu untuk melihat rekap emisi dalam satu halaman." |
| **Memuat** | Pratinjau menampilkan kerangka tabel dan kerangka grafik. Saat PDF sedang disusun, tombol berubah jadi "Menyusun PDF..." dengan batang kemajuan, dan halaman tidak terkunci. |
| **Galat** | Rentang lebih dari 366 hari: "Rentang laporan maksimal satu tahun." Jika penyusunan PDF gagal: "Laporan gagal disusun. Coba lagi atau persempit rentangnya." |
| **Berhasil** | Berkas terunduh sendiri, disertai notifikasi "Laporan Oktober 2026 terunduh." |

**Perilaku responsif**

- 1440px: formulir di kiri 400px, pratinjau di kanan
- 768px: pratinjau pindah ke bawah formulir dan dapat dilipat
- 375px: pratinjau diganti ringkasan angka saja, PDF tetap bisa diunduh

**Aksesibilitas**

- Batang kemajuan penyusunan PDF memakai aria-valuenow
- Tabel laporan punya kepala kolom sungguhan agar bisa dinavigasi pembaca layar
- Grafik di layar didampingi tabel angka yang sama, tersembunyi secara visual tetapi terbaca pembaca layar

**Komponen dan kelas CSS yang dipakai**

`<x-kartu>`, `.ct-rentang-tanggal`, `.ct-kemajuan`, `<x-tabel>`, `<x-kerangka jenis="grafik">`, `<x-kosong>`, `<x-tombol varian="utama">`

#### Kasus Batas

- Rentang laporan mencakup periode tanpa satu pun catatan: PDF tetap terbentuk dengan tabel kosong dan catatan penjelas, bukan gagal.
- Pengguna mengoreksi catatan setelah laporan tersimpan: angka laporan ikut berubah saat dibuka lagi, karena laporan tidak menyimpan salinan angka. Jelaskan ini di layar agar tidak dikira bug.
- Rentang satu tahun penuh: penyusunan dipindah ke antrean, pengguna diberi tahu laporan sedang disusun dan dikabari setelah selesai.
- Kategori yang dipilih kemudian dihapus admin: laporan tetap bisa dibuka, kategori yang hilang ditandai sebagai "Kategori dihapus".
- dompdf tidak menjalankan JavaScript: pastikan grafik di PDF benar-benar SVG sisi server, periksa dengan membuka PDF di perangkat tanpa internet.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: title wajib
- [ ] Menolak ketika aturan dilanggar: period_start dan period_end wajib
- [ ] Menolak ketika aturan dilanggar: period_end tidak boleh melewati hari ini
- [ ] Menolak ketika aturan dilanggar: category_scope opsional
- [ ] Memastikan: pDF terunduh berisi tabel ringkasan dan grafik yang terlihat
- [ ] Memastikan: laporan dengan rentang satu tahun selesai tanpa kehabisan waktu
- [ ] Memastikan: mengoreksi satu aktivitas membuat angka laporan ikut berubah
- [ ] Memastikan: laporan milik pengguna lain tidak dapat diunduh lewat URL langsung

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_saved_reports_table_022.php` |
| Model | `app/Models/SavedReport022.php` |
| Controller | `app/Http/Controllers/LaporanJejakKarbon022.php` |
| Form Request | `app/Http/Requests/SimpanLaporanJejakKarbon022.php` |
| Policy | `app/Policies/LaporanJejakKarbonPolicy022.php` |
| View daftar | `resources/views/target-pengingat-dan-laporan/laporan-jejak-karbon-022/index.blade.php` |
| View formulir | `resources/views/target-pengingat-dan-laporan/laporan-jejak-karbon-022/form.blade.php` |
| Seeder | `database/seeders/LaporanJejakKarbonSeeder022.php` |
| Feature Test | `tests/Feature/LaporanJejakKarbonTest022.php` |

**Kriteria selesai**

- [ ] PDF terunduh berisi tabel ringkasan dan grafik yang terlihat
- [ ] Laporan dengan rentang satu tahun selesai tanpa kehabisan waktu
- [ ] Mengoreksi satu aktivitas membuat angka laporan ikut berubah
- [ ] Laporan milik pengguna lain tidak dapat diunduh lewat URL langsung

---

## 4. Acuan Tampilan Modul Ini

Dua berkas prompt Figma menjadi acuan: versi wireframe hitam putih untuk struktur, dan versi berwarna untuk tampilan akhir.

- Frame 4 Dashboard pada wireframe: empat kotak statistik di baris pertama, grafik tren dan grafik donat di baris kedua.
- Menu sidebar "Target" dan "Dashboard".

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
| Sprint 1 | Week 7–10 | PBI-10, PBI-11 |
| Sprint 2 | Week 11–15 | PBI-12 |

---

## 6. Catatan Khusus

- Sebagai Scrum Master, siapkan papan sprint dan pastikan setiap anggota menandai PBI yang
  sedang dikerjakan.
- Sebagai pemegang cabang utama, lakukan peninjauan setiap permintaan gabung sebelum
  digabungkan.
- Modul ini bergantung paling banyak pada modul lain, jadi mulailah setelah PBI-04 dapat
  menyimpan data.

---

## 7. Sebelum Mengajukan Permintaan Gabung

- [ ] Seluruh kriteria selesai pada setiap PBI sudah tercentang
- [ ] Keempat keadaan layar sudah dibuat untuk setiap halaman: kosong, memuat, galat, dan berhasil
- [ ] Sudah dicoba pada lebar 1440px, 768px, dan 375px
- [ ] Seluruh alur dapat diselesaikan hanya dengan papan ketik
- [ ] Seluruh teks antarmuka berbahasa Indonesia dan tanpa istilah teknis
- [ ] Nama berkas sudah berakhiran `022` dan nama kelas di dalamnya cocok
- [ ] Setiap model sudah menyebut `protected $table`
- [ ] Migration dapat dijalankan dari nol tanpa galat (`migrate:fresh --seed`)
- [ ] Tidak ada perubahan pada tabel milik anggota lain
- [ ] Feature test untuk setiap PBI sudah lulus
- [ ] Sudah diuji Renaya selaku Quality Assurance
- [ ] Sudah ditinjau Alvin selaku pemegang cabang utama

