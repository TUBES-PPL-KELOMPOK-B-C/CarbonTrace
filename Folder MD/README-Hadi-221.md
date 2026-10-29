# README Pengembangan — Hadi Dwi Pranoto

**Proyek** CarbonTrace — Platform Pelacakan dan Pemangkasan Jejak Karbon Harian  
**NIM** 102022400221  
**Peran** Product Owner merangkap Developer dan Front-End Lead  
**Modul** Pencatatan Aktivitas  
**Akhiran berkas** `221` (tiga digit terakhir NIM)  
**Jumlah PBI** 3

---

## 1. Ruang Lingkup

Membangun jalur masuk data utama produk: pencatatan aktivitas harian, pintasan aktivitas
berulang, dan bank pertanyaan untuk menaksir garis dasar emisi pengguna baru. Merangkap
menjaga urutan prioritas backlog dan konsistensi tampilan antarhalaman.

### Yang dikerjakan

| PBI | KF | Nama Fitur | Kerumitan |
|---|---|---|---|
| PBI-04 | KF-05 | Pencatatan Aktivitas Emisi Harian | Tinggi |
| PBI-05 | KF-06 | Template Aktivitas Rutin | Menengah |
| PBI-06 | KF-07 | Manajemen Bank Pertanyaan Kuis | Menengah |

### Fitur pendukung (tidak dihitung sebagai PBI)

**KF-24 Kalkulator Jejak Karbon Publik**

Dapat dipakai pengunjung tanpa akun. Hanya membaca faktor emisi dan menampilkan hasil, tidak
menyimpan data.

**KF-25 Pengisian Kuis Awal dan Garis Dasar**

Pemakaian dari PBI-06 oleh pengguna baru, bukan pengelolaan data baru.

---

## 2. Lingkungan dan Konvensi Penamaan

**Tumpukan teknologi:** Laravel 12, PHP 8.3, MySQL 8, Blade + Tailwind, ApexCharts, spatie/laravel-permission, barryvdh/laravel-dompdf.

Setiap berkas yang kamu buat diberi akhiran **`221`** sebelum ekstensinya, supaya terlihat siapa yang menulisnya. Nama kelas di dalam berkas harus sama persis dengan nama berkasnya.

```
app/Http/Controllers/PencatatanAktivitasEmisiHarian221.php
   -> class PencatatanAktivitasEmisiHarian221 extends Controller
```

> **Catatan penting.** Laravel menebak nama tabel dari nama model. Karena nama model ikut berakhiran NIM, setiap model **wajib** menyebut tabelnya sendiri, kalau tidak Eloquent akan mencari tabel yang tidak ada:
>
> ```php
> protected $table = 'nama_tabel_sebenarnya';
> ```
>
> Nama **tabel** tidak pernah diberi akhiran NIM. Tabel dipakai bersama seluruh anggota dan harus persis sama dengan ERD.

Agar tidak bentrok saat menggabungkan pekerjaan, letakkan rute milikmu di berkas terpisah `routes/modul-pencatatan-aktivitas-221.php`, lalu daftarkan sekali saja di `routes/web.php`.

---

## 3. Rincian per PBI

### 3.1  PBI-04 — Pencatatan Aktivitas Emisi Harian

`KF-05` · Kerumitan **Tinggi** · Endpoint `/aktivitas`

**Kenapa tingkat kerumitannya segitu**

Inti produk. Setiap penyimpanan memicu perhitungan emisi, pembaruan streak, pemeriksaan
target, dan pemberian poin. Satu-satunya PBI yang disentuh hampir seluruh modul lain.

**Tabel yang dimiliki PBI ini**

- `activities` — id, user_id, emission_factor_id, amount, emission, activity_date, note

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `emission_factors`, `categories`, `user_settings`, `appliances`, `portion_units`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Menambah catatan: kategori, faktor emisi, jumlah, satuan, tanggal, dan catatan opsional. Nilai emission dihitung sistem, bukan diisi pengguna. |
| **Read** | Riwayat harian dan mingguan dengan penyaringan kategori dan rentang tanggal, serta jumlah total emisi pada rentang tersebut. |
| **Update** | Mengoreksi jumlah, kategori, atau tanggal. Emisi wajib dihitung ulang dan rekap harian ikut menyesuaikan. |
| **Delete** | Menghapus catatan yang keliru. Total harian dan poin terkait ikut dikoreksi. |

**Aturan validasi**

- emission_factor_id wajib, harus ada di emission_factors dan berstatus aktif
- amount wajib, angka desimal lebih besar dari 0, maksimal 100000
- activity_date wajib, format tanggal, tidak boleh melewati hari ini
- note opsional, maksimal 255 karakter

**Logika bisnis yang harus diperhatikan**

- emission = amount x factor_value. Untuk kategori listrik, hasilnya dibagi household_size dari PBI-02.
- Kolom emission wajib bertipe decimal. Bilangan bulat membuat 1,5 kg menjadi 1 kg dan seluruh angka produk salah.
- Seluruh perhitungan diletakkan di satu kelas Service, bukan di controller, karena kalkulator publik (KF-24) memakai kelas yang sama.
- Setelah tersimpan, panggil pembaruan streak dan pemberian poin milik modul Gamifikasi lewat event, bukan panggilan langsung.

#### Skema Migrasi

```php
Schema::create('activities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('emission_factor_id')->constrained('emission_factors')->cascadeOnDelete();
    $table->decimal('amount', 12, 4);
    $table->decimal('emission', 12, 4);
    $table->date('activity_date');
    $table->string('note');
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/aktivitas` | `aktivitas.index` | Riwayat aktivitas | auth |
| `GET` | `/aktivitas/buat` | `aktivitas.create` | Panel formulir | auth |
| `POST` | `/aktivitas` | `aktivitas.store` | Simpan catatan | auth |
| `GET` | `/aktivitas/{aktivitas}/ubah` | `aktivitas.edit` | Panel ubah | auth + pemilik |
| `PUT` | `/aktivitas/{aktivitas}` | `aktivitas.update` | Perbarui catatan | auth + pemilik |
| `DELETE` | `/aktivitas/{aktivitas}` | `aktivitas.destroy` | Hapus catatan | auth + pemilik |
| `POST` | `/api/aktivitas/pratinjau` | `aktivitas.pratinjau` | Hitung emisi tanpa menyimpan (JSON) | auth |

#### API

`POST /api/aktivitas/pratinjau`

Dipanggil setiap kali kolom jumlah berubah, supaya pengguna melihat angka emisinya sebelum
menekan simpan. Tidak menulis apa pun ke basis data.

```jsonc
// kirim
{ "emission_factor_id": 7, "amount": 12.5 }

// terima
{
  "emisi": 1.50,
  "satuan": "kg CO2e",
  "rincian": "12,5 km x 0,12 kg CO2e/km",
  "faktor": { "nama": "Motor bensin", "nilai": 0.1200, "sumber": "https://..." }
}
```

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/aktivitas` — riwayat, tabel dengan penyaring di atasnya
- `/aktivitas/buat` — formulir pencatatan, dibuka sebagai panel geser dari kanan agar riwayat di belakangnya tetap terlihat
- Tombol bulat bertanda tambah di kanan bawah dashboard membuka panel yang sama

**Komponen antarmuka**

- Pemilih kategori berbentuk empat kartu berikon, bukan menu tarik
- Pemilih faktor emisi yang isinya menyesuaikan kategori terpilih
- Kolom jumlah dengan satuan menempel, berubah mengikuti kategori (km, kWh, porsi, kg)
- Pemilih tanggal, bawaan hari ini, tidak bisa memilih masa depan
- Pratinjau hasil hitung yang berubah seketika saat jumlah diketik, sebelum disimpan
- Penyaring riwayat: rentang tanggal dan kategori
- Setiap baris riwayat punya menu titik tiga: Ubah, Hapus

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Riwayat kosong menampilkan ilustrasi garis sederhana dan teks "Belum ada catatan. Mulai dari perjalananmu hari ini." beserta tombol "Catat Aktivitas". |
| **Memuat** | Pratinjau hasil hitung menampilkan tiga titik berdenyut selama menunggu, tidak pernah menampilkan angka lama yang salah. |
| **Galat** | Jumlah nol atau tanggal masa depan ditandai merah di kolomnya. Jika faktor emisi tidak aktif: "Faktor emisi ini sudah diarsipkan, pilih yang lain." |
| **Berhasil** | Panel menutup, baris baru muncul di puncak riwayat dengan sorotan singkat, dan notifikasi "Tercatat 2,4 kg CO2e." |

**Perilaku responsif**

- 1440px: panel geser 480px, riwayat tetap terlihat di kiri
- 768px: panel menjadi layar penuh
- 375px: empat kartu kategori jadi dua baris, tombol simpan menempel di bawah layar agar selalu terjangkau ibu jari

**Aksesibilitas**

- Pratinjau hasil hitung memakai aria-live supaya perubahan angka dibacakan
- Panel geser menahan fokus papan ketik di dalamnya dan ditutup dengan Esc
- Kartu kategori dapat dipilih dengan papan ketik, bukan hanya klik

**Komponen dan kelas CSS yang dipakai**

`<x-panel-geser>`, `<x-isian>`, `.ct-pilihan-kartu`, `.ct-isian-satuan`, `.ct-pratinjau-hitung`, `<x-tabel>`, `<x-kosong>`, `<x-tombol-tambang>`, `<x-notifikasi>`

#### Kasus Batas

- Faktor emisi diarsipkan admin setelah pengguna membuka formulir tetapi sebelum menekan simpan: ditolak dengan pesan jelas, bukan galat 500.
- Pengguna mencatat aktivitas untuk tanggal yang sudah punya target, lalu target itu dihapus: catatan tetap ada, hanya kehilangan konteks kuota.
- Jumlah dengan banyak angka desimal, misalnya 12,345678: dibulatkan ke empat desimal saat disimpan, dan hasilnya dua desimal.
- Pengguna mengubah tanggal catatan ke bulan sebelumnya: rekap kedua bulan wajib ikut berubah, bukan hanya bulan asalnya.
- Menghapus catatan yang sudah memberi poin: poin ikut dikoreksi lewat point_logs bernilai negatif, bukan dengan menghapus baris poin lama.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: emission_factor_id wajib
- [ ] Menolak ketika aturan dilanggar: amount wajib
- [ ] Menolak ketika aturan dilanggar: activity_date wajib
- [ ] Menolak ketika aturan dilanggar: note opsional
- [ ] Memastikan: catatan tersimpan beserta angka emisi yang benar sampai dua angka desimal
- [ ] Memastikan: mengubah jumlah membuat angka emisi ikut berubah
- [ ] Memastikan: menghapus catatan membuat total harian berkurang sesuai
- [ ] Memastikan: tanggal di masa depan ditolak

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_activities_table_221.php` |
| Model | `app/Models/Activitie221.php` |
| Controller | `app/Http/Controllers/PencatatanAktivitasEmisiHarian221.php` |
| Form Request | `app/Http/Requests/SimpanPencatatanAktivitasEmisiHarian221.php` |
| Policy | `app/Policies/PencatatanAktivitasEmisiHarianPolicy221.php` |
| View daftar | `resources/views/pencatatan-aktivitas/pencatatan-aktivitas-emisi-harian-221/index.blade.php` |
| View formulir | `resources/views/pencatatan-aktivitas/pencatatan-aktivitas-emisi-harian-221/form.blade.php` |
| Seeder | `database/seeders/PencatatanAktivitasEmisiHarianSeeder221.php` |
| Feature Test | `tests/Feature/PencatatanAktivitasEmisiHarianTest221.php` |

**Kriteria selesai**

- [ ] Catatan tersimpan beserta angka emisi yang benar sampai dua angka desimal
- [ ] Mengubah jumlah membuat angka emisi ikut berubah
- [ ] Menghapus catatan membuat total harian berkurang sesuai
- [ ] Tanggal di masa depan ditolak
- [ ] Tersedia pengujian otomatis untuk rumus perhitungannya

---

### 3.2  PBI-05 — Template Aktivitas Rutin

`KF-06` · Kerumitan **Menengah** · Endpoint `/template-aktivitas`

**Kenapa tingkat kerumitannya segitu**

CRUD biasa, tetapi punya satu aksi tambahan yang menyalin template menjadi catatan aktivitas
hari ini, sehingga bergantung pada PBI-04.

**Tabel yang dimiliki PBI ini**

- `activity_templates` — id, user_id, emission_factor_id, name, amount, estimated_emission

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `emission_factors`, `activities`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Menyimpan satu kombinasi kategori, faktor emisi, dan jumlah sebagai template bernama, misalnya "Motor ke kampus pulang pergi". |
| **Read** | Daftar template milik pengguna beserta taksiran emisinya. |
| **Update** | Mengubah nama, jumlah, atau faktor emisi template. Taksiran emisi dihitung ulang. |
| **Delete** | Menghapus template. Catatan aktivitas yang pernah dibuat dari template itu tidak ikut terhapus. |

**Aturan validasi**

- name wajib, maksimal 80 karakter, unik per pengguna
- emission_factor_id wajib dan harus aktif
- amount wajib, desimal lebih besar dari 0

**Logika bisnis yang harus diperhatikan**

- Aksi "Pakai hari ini" membuat satu baris activities memakai kelas Service yang sama dengan PBI-04, bukan menulis langsung ke tabel.
- estimated_emission disimpan sebagai cache tampilan. Jika faktor emisinya berubah, nilai ini wajib dihitung ulang.
- Pengguna hanya melihat template miliknya sendiri.

#### Skema Migrasi

```php
Schema::create('activity_templates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('emission_factor_id')->constrained('emission_factors')->cascadeOnDelete();
    $table->string('name');
    $table->decimal('amount', 12, 4);
    $table->decimal('estimated_emission', 12, 4);
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/template-aktivitas` | `template.index` | Kisi template | auth |
| `POST` | `/template-aktivitas` | `template.store` | Simpan template | auth |
| `PUT` | `/template-aktivitas/{template}` | `template.update` | Ubah template | auth + pemilik |
| `DELETE` | `/template-aktivitas/{template}` | `template.destroy` | Hapus template | auth + pemilik |
| `POST` | `/template-aktivitas/{template}/pakai` | `template.pakai` | Jadikan catatan hari ini | auth + pemilik |

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/template-aktivitas` — kisi kartu template
- Formulir template memakai panel geser yang sama dengan PBI-04

**Komponen antarmuka**

- Kartu template: nama, ikon kategori, taksiran emisi, tombol "Pakai Hari Ini"
- Tombol "Pakai Hari Ini" berubah jadi tanda centang selama 2 detik setelah ditekan, lalu kembali
- Menu titik tiga di tiap kartu: Ubah, Gandakan, Hapus

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | "Belum ada template. Simpan aktivitas yang kamu lakukan tiap hari supaya tidak perlu mengisi ulang." dengan tombol buat. |
| **Memuat** | Enam kartu kerangka abu-abu. |
| **Galat** | Nama kembar: "Kamu sudah punya template dengan nama ini." |
| **Berhasil** | Notifikasi "Tercatat dari template Motor ke Kampus." disertai tautan "Lihat catatan". |

**Perilaku responsif**

- 1440px: kisi empat kolom
- 768px: dua kolom
- 375px: satu kolom

**Aksesibilitas**

- Perubahan tombol menjadi centang diumumkan lewat aria-live
- Setiap kartu punya satu tujuan klik utama yang jelas, menu titik tiga terpisah

**Komponen dan kelas CSS yang dipakai**

`<x-kartu>`, `<x-tombol varian="sekunder">`, `.ct-menu-titik-tiga`, `<x-kerangka jenis="kartu">`, `<x-kosong>`

#### Kasus Batas

- Faktor emisi yang dipakai template diarsipkan: template ditandai perlu diperbarui, tombol "Pakai Hari Ini" dinonaktifkan dengan penjelasan.
- Pengguna menekan "Pakai Hari Ini" dua kali cepat: hanya satu catatan yang terbentuk, cegah dengan menonaktifkan tombol selama permintaan berjalan.
- Nilai faktor emisi berubah: estimated_emission pada template wajib dihitung ulang, kalau tidak angkanya menyesatkan.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: name wajib
- [ ] Menolak ketika aturan dilanggar: emission_factor_id wajib dan harus aktif
- [ ] Menolak ketika aturan dilanggar: amount wajib
- [ ] Memastikan: satu klik menghasilkan catatan aktivitas hari ini dengan angka benar
- [ ] Memastikan: nama template tidak boleh kembar untuk satu pengguna
- [ ] Memastikan: menghapus template tidak menghapus riwayat aktivitas

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_activity_templates_table_221.php` |
| Model | `app/Models/ActivityTemplate221.php` |
| Controller | `app/Http/Controllers/TemplateAktivitasRutin221.php` |
| Form Request | `app/Http/Requests/SimpanTemplateAktivitasRutin221.php` |
| Policy | `app/Policies/TemplateAktivitasRutinPolicy221.php` |
| View daftar | `resources/views/pencatatan-aktivitas/template-aktivitas-rutin-221/index.blade.php` |
| View formulir | `resources/views/pencatatan-aktivitas/template-aktivitas-rutin-221/form.blade.php` |
| Seeder | `database/seeders/TemplateAktivitasRutinSeeder221.php` |
| Feature Test | `tests/Feature/TemplateAktivitasRutinTest221.php` |

**Kriteria selesai**

- [ ] Satu klik menghasilkan catatan aktivitas hari ini dengan angka benar
- [ ] Nama template tidak boleh kembar untuk satu pengguna
- [ ] Menghapus template tidak menghapus riwayat aktivitas

---

### 3.3  PBI-06 — Manajemen Bank Pertanyaan Kuis

`KF-07` · Kerumitan **Menengah** · Endpoint `/admin/bank-kuis`

**Kenapa tingkat kerumitannya segitu**

Pilihan jawaban disimpan sebagai JSON dengan bobot per pilihan, sehingga validasinya
berlapis dan tidak bisa memakai aturan bawaan begitu saja.

**Tabel yang dimiliki PBI ini**

- `quiz_questions` — id, question, options, emission_weight, is_active

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `onboarding_results`

**Rincian CRUD**

| Operasi | Yang dilakukan |
|---|---|
| **Create** | Menambah pertanyaan beserta daftar pilihan jawaban dan bobot emisi masing-masing pilihan. |
| **Read** | Daftar pertanyaan dengan penyaringan status aktif, beserta jumlah pengguna yang sudah menjawabnya. |
| **Update** | Mengubah teks pertanyaan, pilihan jawaban, atau bobot. |
| **Delete** | Menonaktifkan pertanyaan. Pertanyaan yang sudah pernah dijawab tidak boleh dihapus permanen karena onboarding_results merujuk padanya. |

**Aturan validasi**

- question wajib, 10 sampai 500 karakter
- options wajib, larik, minimal 2 dan maksimal 6 pilihan
- setiap pilihan wajib punya label teks dan bobot angka
- emission_weight wajib, desimal, 0 sampai 1000

**Logika bisnis yang harus diperhatikan**

- Kolom options bertipe JSON di MySQL. Pada ERD Visual Paradigm tipe ini ditulis clob karena VP tidak punya tipe json.
- Pakai casts: protected $casts = ['options' => 'array'];
- Menonaktifkan pertanyaan tidak boleh mengubah hasil kuis yang sudah terlanjur dihitung.

#### Skema Migrasi

```php
Schema::create('quiz_questions', function (Blueprint $table) {
    $table->id();
    $table->text('question')->nullable();
    $table->json('options');
    $table->decimal('emission_weight', 12, 4);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/admin/bank-kuis` | `kuis.index` | Tabel pertanyaan | auth + admin |
| `POST` | `/admin/bank-kuis` | `kuis.store` | Tambah pertanyaan | auth + admin |
| `PUT` | `/admin/bank-kuis/{soal}` | `kuis.update` | Ubah pertanyaan | auth + admin |
| `PATCH` | `/admin/bank-kuis/{soal}/status` | `kuis.status` | Aktif / nonaktif | auth + admin |
| `DELETE` | `/admin/bank-kuis/{soal}` | `kuis.destroy` | Hapus (hanya yang belum dijawab) | auth + admin |

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/admin/bank-kuis` — tabel pertanyaan
- `/admin/bank-kuis/buat` — formulir dengan daftar pilihan jawaban yang bisa ditambah dan diurutkan ulang

**Komponen antarmuka**

- Baris pilihan jawaban yang bisa ditambah, dihapus, dan diseret urutannya
- Setiap pilihan punya kolom teks dan kolom bobot bersisian
- Penanda jumlah pertanyaan aktif di puncak halaman
- Saklar aktif atau nonaktif pada setiap baris tabel

**Keadaan layar yang wajib dirancang**

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | "Bank pertanyaan masih kosong. Tambahkan minimal enam pertanyaan agar kuis awal bisa dijalankan." |
| **Memuat** | Kerangka tabel lima baris. |
| **Galat** | Kurang dari dua pilihan: "Pertanyaan butuh minimal dua pilihan jawaban." Bobot kosong ditandai di barisnya sendiri. |
| **Berhasil** | Notifikasi "Pertanyaan tersimpan." dan baris baru muncul di tabel. |

**Perilaku responsif**

- 1440px: dua kolom, pratinjau kuis di kanan
- 768px: pratinjau pindah ke bawah formulir
- 375px: baris pilihan jawaban menumpuk, seret urutan diganti tombol panah

**Aksesibilitas**

- Seret urutan wajib punya alternatif tombol naik dan turun
- Tombol hapus pilihan punya label tersembunyi "Hapus pilihan ke-2"

**Komponen dan kelas CSS yang dipakai**

`<x-tabel>`, `<x-isian>`, `.ct-baris-seret`, `.ct-saklar`, `<x-panel-geser>`, `<x-kosong>`

#### Kasus Batas

- Admin menghapus pilihan jawaban yang sudah pernah dipilih pengguna: jawaban lama tetap tersimpan sebagai teks, tidak ikut hilang.
- Bank pertanyaan kurang dari dua pertanyaan aktif: kuis awal tidak dijalankan, pengguna baru langsung diarahkan ke dashboard.
- Bobot seluruh pilihan bernilai nol: ditolak, karena garis dasar akan selalu nol.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: question wajib
- [ ] Menolak ketika aturan dilanggar: options wajib
- [ ] Menolak ketika aturan dilanggar: setiap pilihan wajib punya label teks dan bobot angka
- [ ] Menolak ketika aturan dilanggar: emission_weight wajib
- [ ] Memastikan: pertanyaan dengan lima pilihan tersimpan dan terbaca kembali utuh
- [ ] Memastikan: pertanyaan yang sudah dijawab tidak dapat dihapus permanen
- [ ] Memastikan: pengguna biasa tidak dapat membuka halaman ini

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_quiz_questions_table_221.php` |
| Model | `app/Models/QuizQuestion221.php` |
| Controller | `app/Http/Controllers/ManajemenBankPertanyaanKuis221.php` |
| Form Request | `app/Http/Requests/SimpanManajemenBankPertanyaanKuis221.php` |
| Policy | `app/Policies/ManajemenBankPertanyaanKuisPolicy221.php` |
| View daftar | `resources/views/pencatatan-aktivitas/manajemen-bank-pertanyaan-kuis-221/index.blade.php` |
| View formulir | `resources/views/pencatatan-aktivitas/manajemen-bank-pertanyaan-kuis-221/form.blade.php` |
| Seeder | `database/seeders/ManajemenBankPertanyaanKuisSeeder221.php` |
| Feature Test | `tests/Feature/ManajemenBankPertanyaanKuisTest221.php` |

**Kriteria selesai**

- [ ] Pertanyaan dengan lima pilihan tersimpan dan terbaca kembali utuh
- [ ] Pertanyaan yang sudah dijawab tidak dapat dihapus permanen
- [ ] Pengguna biasa tidak dapat membuka halaman ini

---

## 4. Acuan Tampilan Modul Ini

Dua berkas prompt Figma menjadi acuan: versi wireframe hitam putih untuk struktur, dan versi berwarna untuk tampilan akhir.

- Frame 1 Landing bagian "Coba Kalkulator" untuk KF-24.
- Frame 4 Dashboard: tombol bulat bertanda tambah di kanan bawah membuka formulir pencatatan, dan kotak "Aktivitas Terbaru".

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
| Sprint 1 | Week 7–10 | PBI-04, PBI-05, PBI-06 |
| Sprint 2 | Week 11–15 | Penyempurnaan, pengujian, dan perbaikan cacat |

---

## 6. Catatan Khusus

- PBI-04 adalah fitur paling inti. Kerjakan paling awal setelah modul Data Master
  menyediakan faktor emisi, karena empat modul lain menunggu tabel activities terisi.
- Sebagai Front-End Lead, tetapkan komponen Blade bersama untuk tombol, kartu, dan kolom
  isian supaya tampilan antaranggota tidak berbeda-beda.
- Sebagai Product Owner, putuskan prioritas jika ada dua PBI yang bertabrakan jadwalnya.

---

## 7. Sebelum Mengajukan Permintaan Gabung

- [ ] Seluruh kriteria selesai pada setiap PBI sudah tercentang
- [ ] Keempat keadaan layar sudah dibuat untuk setiap halaman: kosong, memuat, galat, dan berhasil
- [ ] Sudah dicoba pada lebar 1440px, 768px, dan 375px
- [ ] Seluruh alur dapat diselesaikan hanya dengan papan ketik
- [ ] Seluruh teks antarmuka berbahasa Indonesia dan tanpa istilah teknis
- [ ] Nama berkas sudah berakhiran `221` dan nama kelas di dalamnya cocok
- [ ] Setiap model sudah menyebut `protected $table`
- [ ] Migration dapat dijalankan dari nol tanpa galat (`migrate:fresh --seed`)
- [ ] Tidak ada perubahan pada tabel milik anggota lain
- [ ] Feature test untuk setiap PBI sudah lulus
- [ ] Sudah diuji Renaya selaku Quality Assurance
- [ ] Sudah ditinjau Alvin selaku pemegang cabang utama

