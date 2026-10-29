# README Pengembangan — Renaya Marvela Adoe

**Proyek** CarbonTrace — Platform Pelacakan dan Pemangkasan Jejak Karbon Harian  
**NIM** 102022400154  
**Peran** Developer merangkap Quality Assurance  
**Modul** Data Master Emisi  
**Akhiran berkas** `154` (tiga digit terakhir NIM)  
**Jumlah PBI** 3

---

## 1. Ruang Lingkup

Memegang seluruh data acuan yang dipakai menghitung emisi: kategori aktivitas, angka faktor
emisi beserta sumber rujukannya, dan aturan konversi satuan. Merangkap menguji modul anggota
lain sebelum digabungkan.

### Yang dikerjakan

| PBI | KF | Nama Fitur | Kerumitan |
|---|---|---|---|
| PBI-07 | KF-08 | Manajemen Kategori Emisi | Menengah |
| PBI-08 | KF-09 | Manajemen Faktor Emisi | Tinggi |
| PBI-09 | KF-10 | Manajemen Acuan Konversi Satuan | Menengah |

### Fitur pendukung (tidak dihitung sebagai PBI)

**KF-20 Perhitungan Emisi Otomatis**

Proses di belakang layar yang memakai PBI-07 sampai PBI-09. Tidak punya antarmuka
pengelolaan sendiri, tetapi kelas Service-nya dipakai PBI-04 dan KF-24.

---

## 2. Lingkungan dan Konvensi Penamaan

**Tumpukan teknologi:** Laravel 12, PHP 8.3, MySQL 8, Blade + Tailwind, ApexCharts, spatie/laravel-permission, barryvdh/laravel-dompdf.

Setiap berkas yang kamu buat diberi akhiran **`154`** sebelum ekstensinya, supaya terlihat siapa yang menulisnya. Nama kelas di dalam berkas harus sama persis dengan nama berkasnya.

```
app/Http/Controllers/ManajemenKategoriEmisi154.php
   -> class ManajemenKategoriEmisi154 extends Controller
```

> **Catatan penting.** Laravel menebak nama tabel dari nama model. Karena nama model ikut berakhiran NIM, setiap model **wajib** menyebut tabelnya sendiri, kalau tidak Eloquent akan mencari tabel yang tidak ada:
>
> ```php
> protected $table = 'nama_tabel_sebenarnya';
> ```
>
> Nama **tabel** tidak pernah diberi akhiran NIM. Tabel dipakai bersama seluruh anggota dan harus persis sama dengan ERD.

Agar tidak bentrok saat menggabungkan pekerjaan, letakkan rute milikmu di berkas terpisah `routes/modul-data-master-emisi-154.php`, lalu daftarkan sekali saja di `routes/web.php`.

---

## 3. Rincian per PBI

### 3.1  PBI-07 — Manajemen Kategori Emisi

`KF-08` · Kerumitan **Menengah** · Endpoint `/admin/kategori-emisi`

**Kenapa tingkat kerumitannya segitu**

CRUD lugas, tetapi menjadi induk bagi empat tabel lain, sehingga aturan penghapusannya harus
ketat.

**Tabel yang dimiliki PBI ini**

- `categories` — id, name, icon, color, base_unit, is_active

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `emission_factors`, `appliances`, `portion_units`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Menambah kategori beserta ikon, warna penanda, dan satuan bakunya. |
| **Read** | Daftar kategori beserta jumlah faktor emisi di bawahnya dan status pakainya. |
| **Update** | Mengubah nama, ikon, warna, atau satuan baku. |
| **Delete** | Menghapus kategori hanya jika belum dipakai catatan mana pun. Jika sudah dipakai, kategori hanya dapat dinonaktifkan. |

**Aturan validasi**

- name wajib, maksimal 60 karakter, unik
- base_unit wajib, salah satu dari: km, kWh, kg, liter, porsi
- color wajib, format heksadesimal enam digit
- icon wajib, maksimal 40 karakter

**Logika bisnis yang harus diperhatikan**

- Sebelum menghapus, hitung relasi ke emission_factors, appliances, portion_units, challenges, dan recommendations. Jika ada, tolak.
- Empat kategori awal wajib tersedia lewat seeder: Transportasi, Listrik, Makanan, Sampah.

#### Skema Migrasi

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('icon');
    $table->string('color');
    $table->string('base_unit');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/admin/kategori-emisi` | `kategori.index` | Tabel kategori | auth + admin |
| `POST` | `/admin/kategori-emisi` | `kategori.store` | Tambah | auth + admin |
| `PUT` | `/admin/kategori-emisi/{kategori}` | `kategori.update` | Ubah | auth + admin |
| `DELETE` | `/admin/kategori-emisi/{kategori}` | `kategori.destroy` | Hapus (hanya yang belum dipakai) | auth + admin |

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/admin/kategori-emisi` — tabel dengan pratinjau warna dan ikon

**Komponen antarmuka**

- Pemilih warna berbentuk kotak warna yang membuka palet
- Pemilih ikon berupa kisi ikon yang bisa dicari
- Kolom "Dipakai" berisi jumlah faktor emisi dan catatan di bawahnya
- Tombol hapus nonaktif disertai penjelasan saat disentuh kursor

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Tidak pernah kosong karena seeder mengisi empat kategori. Jika kosong, tampilkan tombol "Jalankan data awal". |
| **Memuat** | Kerangka tabel empat baris. |
| **Galat** | Mencoba menghapus kategori terpakai: "Kategori ini dipakai 12 faktor emisi. Nonaktifkan saja agar data lama tetap aman." |
| **Berhasil** | Warna baru langsung terlihat di pratinjau baris tanpa muat ulang. |

**Perilaku responsif**

- 1440px: tabel enam kolom
- 768px: kolom ikon dan warna digabung
- 375px: tabel menjadi kartu per kategori

**Aksesibilitas**

- Warna kategori selalu didampingi nama tertulis, tidak pernah warna saja
- Palet warna bisa diisi manual dengan kode heksadesimal lewat papan ketik

**Komponen dan kelas CSS yang dipakai**

`<x-tabel>`, `.ct-pemilih-warna`, `.ct-pemilih-ikon`, `<x-tombol varian="bahaya">`, `<x-modal>`

#### Kasus Batas

- Admin menonaktifkan kategori yang sedang dipakai formulir pencatatan yang terbuka: pengiriman berikutnya ditolak dengan pesan jelas.
- Warna kategori diubah: seluruh grafik donat ikut berubah tanpa perlu membersihkan cache secara manual.
- Dua kategori diberi warna yang terlalu mirip: beri peringatan di formulir, karena grafik donat jadi sulit dibedakan.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: name wajib
- [ ] Menolak ketika aturan dilanggar: base_unit wajib
- [ ] Menolak ketika aturan dilanggar: color wajib
- [ ] Menolak ketika aturan dilanggar: icon wajib
- [ ] Memastikan: kategori yang sudah dipakai tidak bisa dihapus dan pesannya jelas
- [ ] Memastikan: seeder menghasilkan empat kategori baku
- [ ] Memastikan: perubahan warna langsung terlihat pada grafik rincian per kategori

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_categories_table_154.php` |
| Model | `app/Models/Categorie154.php` |
| Controller | `app/Http/Controllers/ManajemenKategoriEmisi154.php` |
| Form Request | `app/Http/Requests/SimpanManajemenKategoriEmisi154.php` |
| Policy | `app/Policies/ManajemenKategoriEmisiPolicy154.php` |
| View daftar | `resources/views/data-master-emisi/manajemen-kategori-emisi-154/index.blade.php` |
| View formulir | `resources/views/data-master-emisi/manajemen-kategori-emisi-154/form.blade.php` |
| Seeder | `database/seeders/ManajemenKategoriEmisiSeeder154.php` |
| Feature Test | `tests/Feature/ManajemenKategoriEmisiTest154.php` |

**Kriteria selesai**

- [ ] Kategori yang sudah dipakai tidak bisa dihapus dan pesannya jelas
- [ ] Seeder menghasilkan empat kategori baku
- [ ] Perubahan warna langsung terlihat pada grafik rincian per kategori

---

### 3.2  PBI-08 — Manajemen Faktor Emisi

`KF-09` · Kerumitan **Tinggi** · Endpoint `/admin/faktor-emisi`

**Kenapa tingkat kerumitannya segitu**

Satu-satunya sumber kebenaran angka pada produk ini. Butuh penelusuran sumber rujukan,
penanganan versi angka, dan pengarsipan, bukan penghapusan.

**Tabel yang dimiliki PBI ini**

- `emission_factors` — id, category_id, name, unit, factor_value, reference_url, is_active

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `categories`, `activities`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Menambah faktor emisi: kategori, nama, satuan, nilai pengali, dan tautan sumber rujukannya. |
| **Read** | Tabel faktor per kategori beserta sumber dan status aktifnya, dengan pencarian menurut nama. |
| **Update** | Memperbarui nilai beserta sumbernya. Perubahan tidak menghitung ulang catatan lama. |
| **Delete** | Mengarsipkan faktor usang dengan mengubah is_active menjadi 0. Penghapusan permanen dilarang karena activities merujuk padanya. |

**Aturan validasi**

- category_id wajib dan harus ada
- name wajib, maksimal 120 karakter, unik dalam satu kategori
- factor_value wajib, desimal lebih besar dari 0, maksimal 4 angka desimal
- reference_url wajib, format URL, maksimal 255 karakter
- unit wajib dan harus cocok dengan base_unit kategorinya

**Logika bisnis yang harus diperhatikan**

- factor_value wajib decimal(10,4). Tipe bilangan bulat akan membulatkan 0,681 menjadi 1 dan merusak seluruh perhitungan.
- Faktor acuan listrik Indonesia 0,681 kg CO2e per kWh wajib ada di seeder beserta tautan sumbernya, karena angka ini disebut di proposal.
- Catatan lama memakai nilai yang berlaku saat dicatat. Perubahan faktor tidak boleh mengubah riwayat.

#### Skema Migrasi

```php
Schema::create('emission_factors', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
    $table->string('name');
    $table->string('unit');
    $table->decimal('factor_value', 12, 4);
    $table->string('reference_url');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/admin/faktor-emisi` | `faktor.index` | Tabel faktor per kategori | auth + admin |
| `POST` | `/admin/faktor-emisi` | `faktor.store` | Tambah faktor | auth + admin |
| `PUT` | `/admin/faktor-emisi/{faktor}` | `faktor.update` | Perbarui nilai | auth + admin |
| `PATCH` | `/admin/faktor-emisi/{faktor}/arsip` | `faktor.arsip` | Arsipkan faktor usang | auth + admin |
| `GET` | `/api/faktor-emisi` | `faktor.data` | Faktor aktif per kategori (JSON) | auth |

#### API

`GET /api/faktor-emisi?kategori=2`

Mengisi pemilih faktor emisi pada formulir pencatatan, menyesuaikan kategori yang sedang
dipilih. Hanya mengembalikan faktor berstatus aktif.

```jsonc
{
  "data": [
    { "id": 7, "nama": "Listrik PLN", "unit": "kWh", "nilai": 0.6810, "sumber": "https://..." }
  ]
}
```

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/admin/faktor-emisi` — tabel dikelompokkan per kategori, dengan pencarian
- Panel geser untuk tambah dan ubah

**Komponen antarmuka**

- Kolom nilai dengan empat angka desimal terlihat jelas, bukan dibulatkan
- Tautan sumber yang dapat diklik dan membuka tab baru, dengan ikon tautan luar
- Pelat status: Aktif atau Diarsipkan
- Kotak peringatan di formulir ubah: "Mengubah nilai tidak mengubah catatan yang sudah ada."

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | "Belum ada faktor emisi. Jalankan data awal untuk memuat acuan resmi, termasuk 0,681 kg CO2e per kWh untuk listrik Indonesia." |
| **Memuat** | Kerangka tabel delapan baris. |
| **Galat** | Nilai dengan lebih dari empat desimal: "Maksimal empat angka di belakang koma." Tautan tidak sah: "Alamat sumber tidak valid." |
| **Berhasil** | Notifikasi "Faktor emisi diperbarui." dengan tautan "Lihat riwayat perubahan". |

**Perilaku responsif**

- 1440px: tabel penuh dengan kolom sumber terlihat
- 768px: kolom sumber jadi ikon saja
- 375px: kartu per faktor, nilai ditampilkan paling menonjol

**Aksesibilitas**

- Angka desimal dibacakan dengan satuan lengkap, "nol koma enam delapan satu kilogram CO2e per kilowatt jam"
- Tautan sumber punya label yang menjelaskan tujuannya, bukan "klik di sini"

**Komponen dan kelas CSS yang dipakai**

`<x-tabel>`, `<x-panel-geser>`, `.ct-pelat`, `.ct-tautan-luar`, `.ct-catatan`, `<x-kosong>`

#### Kasus Batas

- Admin memperbarui nilai faktor: catatan lama tidak berubah. Sediakan catatan riwayat perubahan agar bisa dijelaskan saat diuji dosen.
- Tautan sumber mati atau berubah: tidak diperiksa otomatis, tetapi wajib bisa ditampilkan apa adanya agar penelusuran tetap mungkin.
- Nilai sangat kecil seperti 0,0008: tetap tersimpan utuh dengan empat desimal, tidak dibulatkan menjadi nol.
- Dua faktor bernama sama di kategori berbeda: diperbolehkan. Keunikan hanya berlaku dalam satu kategori.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: category_id wajib dan harus ada
- [ ] Menolak ketika aturan dilanggar: name wajib
- [ ] Menolak ketika aturan dilanggar: factor_value wajib
- [ ] Menolak ketika aturan dilanggar: reference_url wajib
- [ ] Menolak ketika aturan dilanggar: unit wajib dan harus cocok dengan base_unit kategorinya
- [ ] Memastikan: nilai 0,681 tersimpan utuh, tidak dibulatkan
- [ ] Memastikan: faktor yang sudah dipakai tidak bisa dihapus permanen
- [ ] Memastikan: setiap faktor punya tautan sumber yang bisa dibuka
- [ ] Memastikan: mengubah faktor tidak mengubah angka emisi catatan lama

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_emission_factors_table_154.php` |
| Model | `app/Models/EmissionFactor154.php` |
| Controller | `app/Http/Controllers/ManajemenFaktorEmisi154.php` |
| Form Request | `app/Http/Requests/SimpanManajemenFaktorEmisi154.php` |
| Policy | `app/Policies/ManajemenFaktorEmisiPolicy154.php` |
| View daftar | `resources/views/data-master-emisi/manajemen-faktor-emisi-154/index.blade.php` |
| View formulir | `resources/views/data-master-emisi/manajemen-faktor-emisi-154/form.blade.php` |
| Seeder | `database/seeders/ManajemenFaktorEmisiSeeder154.php` |
| Feature Test | `tests/Feature/ManajemenFaktorEmisiTest154.php` |

**Kriteria selesai**

- [ ] Nilai 0,681 tersimpan utuh, tidak dibulatkan
- [ ] Faktor yang sudah dipakai tidak bisa dihapus permanen
- [ ] Setiap faktor punya tautan sumber yang bisa dibuka
- [ ] Mengubah faktor tidak mengubah angka emisi catatan lama

---

### 3.3  PBI-09 — Manajemen Acuan Konversi Satuan

`KF-10` · Kerumitan **Menengah** · Endpoint `/admin/konversi-satuan`

**Kenapa tingkat kerumitannya segitu**

Dua tabel sekaligus dalam satu PBI: daya peralatan listrik dan konversi porsi makanan ke
kilogram. Keduanya menjadi masukan rumus.

**Tabel yang dimiliki PBI ini**

- `appliances` — id, category_id, name, power_watt
- `portion_units` — id, category_id, name, kg_conversion

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `categories`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Menambah peralatan listrik beserta dayanya, atau satuan porsi beserta nilai konversinya ke kilogram. |
| **Read** | Dua tabel bersisian dalam satu halaman, dikelompokkan menurut kategori. |
| **Update** | Mengubah nilai daya atau nilai pengali konversi. |
| **Delete** | Menghapus aturan yang belum dipakai catatan mana pun. |

**Aturan validasi**

- name wajib, maksimal 80 karakter, unik per kategori
- power_watt wajib untuk peralatan, bilangan bulat 1 sampai 5000
- kg_conversion wajib untuk satuan porsi, desimal lebih besar dari 0

**Logika bisnis yang harus diperhatikan**

- Peralatan hanya boleh berada di bawah kategori Listrik, satuan porsi hanya di bawah kategori Makanan. Tegakkan lewat validasi.
- Perhitungan listrik: kWh = (power_watt x jam pemakaian) dibagi 1000.
- Perhitungan makanan: kilogram = jumlah porsi x kg_conversion.

#### Skema Migrasi

```php
Schema::create('appliances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
    $table->string('name');
    $table->integer('power_watt')->default(0);
    $table->timestamps();
});
Schema::create('portion_units', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
    $table->string('name');
    $table->decimal('kg_conversion', 12, 4);
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/admin/konversi-satuan` | `konversi.index` | Dua tab | auth + admin |
| `POST` | `/admin/konversi-satuan/peralatan` | `peralatan.store` | Tambah peralatan | auth + admin |
| `PUT` | `/admin/konversi-satuan/peralatan/{id}` | `peralatan.update` | Ubah daya | auth + admin |
| `DELETE` | `/admin/konversi-satuan/peralatan/{id}` | `peralatan.destroy` | Hapus peralatan | auth + admin |
| `POST` | `/admin/konversi-satuan/porsi` | `porsi.store` | Tambah satuan porsi | auth + admin |
| `PUT` | `/admin/konversi-satuan/porsi/{id}` | `porsi.update` | Ubah pengali | auth + admin |
| `DELETE` | `/admin/konversi-satuan/porsi/{id}` | `porsi.destroy` | Hapus satuan | auth + admin |

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/admin/konversi-satuan` — dua tab: Peralatan Listrik dan Satuan Porsi

**Komponen antarmuka**

- Dua tabel terpisah di balik tab, bukan satu tabel campur
- Kalkulator mini di sisi formulir peralatan: masukkan jam pemakaian, langsung terlihat berapa kWh
- Contoh nyata di bawah setiap kolom, misalnya "AC 1 PK biasanya 900 watt"

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | "Belum ada acuan konversi. Jalankan data awal untuk memuat lima peralatan dan tiga satuan porsi." |
| **Memuat** | Kerangka tabel lima baris pada tab yang sedang aktif. |
| **Galat** | Peralatan di kategori salah: "Peralatan listrik hanya bisa berada di bawah kategori Listrik." |
| **Berhasil** | Kalkulator mini menampilkan hasil baru seketika setelah nilai daya diubah. |

**Perilaku responsif**

- 1440px: tabel dan formulir bersisian
- 768px: formulir pindah ke panel geser
- 375px: tab menjadi menu tarik

**Aksesibilitas**

- Kalkulator mini memakai aria-live agar hasilnya dibacakan saat berubah
- Tab dapat dipindah dengan tombol panah kiri dan kanan

**Komponen dan kelas CSS yang dipakai**

`<x-tab>`, `<x-tabel>`, `<x-isian>`, `.ct-kalkulator-mini`, `.ct-catatan`

#### Kasus Batas

- Peralatan dihapus padahal sedang dipakai kalkulator mini yang terbuka: permintaan berikutnya ditolak dengan pesan jelas.
- Daya peralatan diubah: catatan lama tidak dihitung ulang, sama seperti faktor emisi.
- Satuan porsi dengan nilai konversi sangat kecil: tetap desimal, jangan sampai dibulatkan menjadi nol.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: name wajib
- [ ] Menolak ketika aturan dilanggar: power_watt wajib untuk peralatan
- [ ] Menolak ketika aturan dilanggar: kg_conversion wajib untuk satuan porsi
- [ ] Memastikan: peralatan tidak bisa dibuat di bawah kategori Transportasi
- [ ] Memastikan: rumus kWh menghasilkan angka benar untuk kasus uji AC 900 watt 8 jam
- [ ] Memastikan: seeder memuat minimal lima peralatan dan tiga satuan porsi

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_appliances_table_154.php` |
| Migration | `database/migrations/2026_01_01_0000_create_portion_units_table_154.php` |
| Model | `app/Models/Appliance154.php` |
| Model | `app/Models/PortionUnit154.php` |
| Controller | `app/Http/Controllers/ManajemenAcuanKonversiSatuan154.php` |
| Form Request | `app/Http/Requests/SimpanManajemenAcuanKonversiSatuan154.php` |
| Policy | `app/Policies/ManajemenAcuanKonversiSatuanPolicy154.php` |
| View daftar | `resources/views/data-master-emisi/manajemen-acuan-konversi-satuan-154/index.blade.php` |
| View formulir | `resources/views/data-master-emisi/manajemen-acuan-konversi-satuan-154/form.blade.php` |
| Seeder | `database/seeders/ManajemenAcuanKonversiSatuanSeeder154.php` |
| Feature Test | `tests/Feature/ManajemenAcuanKonversiSatuanTest154.php` |

**Kriteria selesai**

- [ ] Peralatan tidak bisa dibuat di bawah kategori Transportasi
- [ ] Rumus kWh menghasilkan angka benar untuk kasus uji AC 900 watt 8 jam
- [ ] Seeder memuat minimal lima peralatan dan tiga satuan porsi

---

## 4. Acuan Tampilan Modul Ini

Dua berkas prompt Figma menjadi acuan: versi wireframe hitam putih untuk struktur, dan versi berwarna untuk tampilan akhir.

- Tidak tampil langsung pada wireframe karena seluruhnya berada di panel administrator.
- Warna kategori yang disimpan pada PBI-07 dipakai grafik donat Frame 4.

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
| Sprint 1 | Week 7–10 | PBI-07, PBI-08, PBI-09 |
| Sprint 2 | Week 11–15 | Penyempurnaan, pengujian, dan perbaikan cacat |

---

## 6. Catatan Khusus

- Kerjakan paling awal di Sprint 1 bersama Rosi. Modul Pencatatan tidak bisa jalan sebelum
  faktor emisi tersedia.
- Keliru di modul ini membuat seluruh angka produk salah, jadi sediakan seeder berisi data
  acuan nyata beserta sumbernya, bukan data karangan.
- Sebagai QA, susun daftar uji untuk setiap modul anggota lain dan jalankan sebelum
  permintaan gabung disetujui.

---

## 7. Sebelum Mengajukan Permintaan Gabung

- [ ] Seluruh kriteria selesai pada setiap PBI sudah tercentang
- [ ] Keempat keadaan layar sudah dibuat untuk setiap halaman: kosong, memuat, galat, dan berhasil
- [ ] Sudah dicoba pada lebar 1440px, 768px, dan 375px
- [ ] Seluruh alur dapat diselesaikan hanya dengan papan ketik
- [ ] Seluruh teks antarmuka berbahasa Indonesia dan tanpa istilah teknis
- [ ] Nama berkas sudah berakhiran `154` dan nama kelas di dalamnya cocok
- [ ] Setiap model sudah menyebut `protected $table`
- [ ] Migration dapat dijalankan dari nol tanpa galat (`migrate:fresh --seed`)
- [ ] Tidak ada perubahan pada tabel milik anggota lain
- [ ] Feature test untuk setiap PBI sudah lulus
- [ ] Sudah diuji Renaya selaku Quality Assurance
- [ ] Sudah ditinjau Alvin selaku pemegang cabang utama

