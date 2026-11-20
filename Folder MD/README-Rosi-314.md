# README Pengembangan — Rosi Rahmawati

**Proyek** CarbonTrace — Platform Pelacakan dan Pemangkasan Jejak Karbon Harian  
**NIM** 102022400314  
**Peran** Developer  
**Modul** Akun dan Administrasi Pengguna  
**Akhiran berkas** `314` (tiga digit terakhir NIM)  
**Jumlah PBI** 3

---

## 1. Ruang Lingkup

Mengelola identitas pengguna, data rumah tangga yang menjadi dasar pembagian emisi listrik,
dan papan peringkat komunitas. Juga membangun autentikasi serta manajemen peran yang wajib
ada namun tidak dihitung sebagai PBI.

### Yang dikerjakan

| PBI    | KF    | Nama Fitur                       | Kerumitan |
| ------ | ----- | -------------------------------- | --------- |
| PBI-01 | KF-02 | Manajemen Profil Pengguna        | Menengah  |
| PBI-02 | KF-03 | Pengaturan Konsumsi Rumah Tangga | Menengah  |
| PBI-20 | KF-26 | Papan Peringkat Komunitas        | Menengah  |

### Fitur pendukung (tidak dihitung sebagai PBI)

**KF-01 Registrasi, Login, dan Logout**

Wajib ada sebagai prasyarat seluruh modul, namun menurut ketentuan dosen tidak dihitung
sebagai PBI. Yang mengerjakan mendapat nilai tambahan.

**KF-04 Manajemen Pengguna dan Peran**

Wajib ada di sistem, tidak dihitung sebagai PBI. Memakai spatie/laravel-permission dengan
dua peran: pengguna dan administrator.

---

## 2. Lingkungan dan Konvensi Penamaan

**Tumpukan teknologi:** Laravel 12, PHP 8.3, MySQL 8, Blade + Tailwind, ApexCharts, spatie/laravel-permission, barryvdh/laravel-dompdf.

Setiap berkas yang kamu buat diberi akhiran **`314`** sebelum ekstensinya, supaya terlihat siapa yang menulisnya. Nama kelas di dalam berkas harus sama persis dengan nama berkasnya.

```
app/Http/Controllers/ManajemenProfilPengguna314.php
   -> class ManajemenProfilPengguna314 extends Controller
```

> **Catatan penting.** Laravel menebak nama tabel dari nama model. Karena nama model ikut berakhiran NIM, setiap model **wajib** menyebut tabelnya sendiri, kalau tidak Eloquent akan mencari tabel yang tidak ada:
>
> ```php
> protected $table = 'nama_tabel_sebenarnya';
> ```
>
> Nama **tabel** tidak pernah diberi akhiran NIM. Tabel dipakai bersama seluruh anggota dan harus persis sama dengan ERD.

Agar tidak bentrok saat menggabungkan pekerjaan, letakkan rute milikmu di berkas terpisah `routes/modul-akun-dan-administrasi-pengguna-314.php`, lalu daftarkan sekali saja di `routes/web.php`.

---

## 3. Rincian per PBI

### 3.1 PBI-01 — Manajemen Profil Pengguna

`KF-02` · Kerumitan **Menengah** · Endpoint `/profil`

**Kenapa tingkat kerumitannya segitu**

Melibatkan unggah berkas foto, penonaktifan akun secara lunak, dan relasi satu-ke-satu ke
pengaturan rumah tangga. Tidak ada perhitungan, tetapi menyentuh tabel yang dipakai seluruh
modul lain.

**Tabel yang dimiliki PBI ini**

- `users` — id, role_id, name, email, password, photo, campus, study_program, points, streak_count, is_active, email_verified_at

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `roles`

**Rincian CRUD**

| Operasi    | Yang dilakukan                                                                                                                                       |
| ---------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Create** | Baris pada users terbentuk otomatis saat pendaftaran. Formulir profil tidak membuat baris baru.                                                      |
| **Read**   | Menampilkan halaman profil berisi nama, email, foto, asal kampus, program studi, poin, dan panjang streak berjalan.                                  |
| **Update** | Mengubah nama, foto, asal kampus, program studi, dan kata sandi. Foto lama dihapus dari penyimpanan setelah foto baru tersimpan.                     |
| **Delete** | Menonaktifkan akun dengan mengubah is_active menjadi 0 (soft delete). Baris tidak dihapus permanen karena activities dan point_logs merujuk padanya. |

**Aturan validasi**

- name wajib, maksimal 100 karakter
- email wajib, format email, unik kecuali untuk dirinya sendiri
- photo opsional, jpg/png, maksimal 2 MB, rasio bebas
- password opsional, minimal 8 karakter, wajib konfirmasi jika diisi
- campus dan study_program opsional, maksimal 120 karakter

**Logika bisnis yang harus diperhatikan**

- Kolom points dan streak_count hanya dibaca di sini. Penulisannya milik modul Gamifikasi, jangan diubah dari modul ini.
- Penonaktifan akun harus memutus sesi aktif pengguna tersebut.
- Pengguna hanya boleh membuka dan mengubah profilnya sendiri; penegakannya lewat Policy, bukan lewat pemeriksaan di dalam controller.

#### Skema Migrasi

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->string('photo');
    $table->string('campus');
    $table->string('study_program');
    $table->integer('points')->default(0);
    $table->integer('streak_count')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamp('email_verified_at')->nullable();
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode   | URI             | Nama Rute        | Aksi                    | Akses          |
| -------- | --------------- | ---------------- | ----------------------- | -------------- |
| `GET`    | `/profil`       | `profil.index`   | Halaman profil          | auth           |
| `PUT`    | `/profil`       | `profil.update`  | Simpan perubahan profil | auth + pemilik |
| `PUT`    | `/profil/sandi` | `profil.sandi`   | Ubah kata sandi         | auth + pemilik |
| `DELETE` | `/profil`       | `profil.destroy` | Nonaktifkan akun        | auth + pemilik |

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/profil` — halaman profil, dua kolom: kartu ringkasan di kiri, formulir di kanan
- `/profil/ubah-sandi` — modal, bukan halaman terpisah
- `/profil/nonaktifkan` — modal konfirmasi bertingkat

**Komponen antarmuka**

- Kartu identitas: foto bulat 96px, nama, surel, pelat peran, pelat poin dan streak
- Pengunggah foto dengan pratinjau langsung sebelum disimpan
- Kolom isian: Nama Lengkap, Surel, Asal Kampus, Program Studi
- Tombol utama "Simpan Perubahan", tombol bahaya "Nonaktifkan Akun"

**Keadaan layar yang wajib dirancang**

| Keadaan      | Perilaku dan teks yang dilihat pengguna                                                                                                |
| ------------ | -------------------------------------------------------------------------------------------------------------------------------------- |
| **Kosong**   | Pengguna baru tanpa foto menampilkan lingkaran berisi inisial namanya, bukan ikon orang bawaan.                                        |
| **Memuat**   | Tombol simpan berubah menjadi nonaktif dengan teks "Menyimpan..." dan lingkaran berputar kecil di dalamnya.                            |
| **Galat**    | Pesan merah tepat di bawah kolom yang bermasalah, bukan satu kotak besar di atas halaman. Contoh: "Surel ini sudah dipakai akun lain." |
| **Berhasil** | Notifikasi mengambang di kanan atas, "Profil tersimpan.", hilang sendiri setelah 4 detik.                                              |

**Perilaku responsif**

- 1440px: dua kolom, kartu 360px di kiri
- 768px: kartu naik ke atas formulir, keduanya satu kolom penuh
- 375px: foto mengecil jadi 72px, tombol melebar penuh

**Aksesibilitas**

- Setiap kolom isian punya label yang terhubung, bukan hanya teks bayangan
- Pesan galat memakai aria-describedby supaya pembaca layar ikut membacakannya
- Tombol nonaktifkan akun butuh dua langkah: centang persetujuan lalu klik

**Komponen dan kelas CSS yang dipakai**

`<x-kartu>`, `<x-isian>`, `<x-tombol varian="utama">`, `<x-tombol varian="bahaya">`, `<x-modal>`, `.ct-avatar`, `.ct-pelat`, `<x-notifikasi>`

#### Kasus Batas

- Pengguna mengunggah foto lalu membatalkan sebelum menyimpan: berkas sementara wajib dibersihkan, jangan menumpuk di penyimpanan.
- Pengguna mengubah surel menjadi surel yang sudah dipakai akun nonaktif: tetap ditolak, karena baris akun nonaktif masih ada.
- Pengguna menonaktifkan akunnya sendiri saat punya sesi terbuka di perangkat lain: seluruh sesi wajib diputus, bukan hanya yang sedang dipakai.
- Administrator menonaktifkan akunnya sendiri: ditolak jika ia administrator terakhir yang aktif.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: name wajib
- [ ] Menolak ketika aturan dilanggar: email wajib
- [ ] Menolak ketika aturan dilanggar: photo opsional
- [ ] Menolak ketika aturan dilanggar: password opsional
- [ ] Menolak ketika aturan dilanggar: campus dan study_program opsional
- [ ] Memastikan: pengguna dapat mengubah seluruh data profilnya dan perubahan tersimpan
- [ ] Memastikan: foto lama tidak menumpuk di penyimpanan
- [ ] Memastikan: akun nonaktif tidak bisa masuk, tetapi riwayat aktivitasnya tetap utuh
- [ ] Memastikan: pengguna A tidak dapat membuka profil pengguna B lewat URL langsung

**Berkas yang dibuat**

| Jenis         | Berkas                                                                                         |
| ------------- | ---------------------------------------------------------------------------------------------- |
| Migration     | `database/migrations/2026_01_01_0000_create_users_table_314.php`                               |
| Model         | `app/Models/User314.php`                                                                       |
| Controller    | `app/Http/Controllers/ManajemenProfilPengguna314.php`                                          |
| Form Request  | `app/Http/Requests/SimpanManajemenProfilPengguna314.php`                                       |
| Policy        | `app/Policies/ManajemenProfilPenggunaPolicy314.php`                                            |
| View daftar   | `resources/views/akun-dan-administrasi-pengguna/manajemen-profil-pengguna-314/index.blade.php` |
| View formulir | `resources/views/akun-dan-administrasi-pengguna/manajemen-profil-pengguna-314/form.blade.php`  |
| Seeder        | `database/seeders/ManajemenProfilPenggunaSeeder314.php`                                        |
| Feature Test  | `tests/Feature/ManajemenProfilPenggunaTest314.php`                                             |

**Kriteria selesai**

- [ ] Pengguna dapat mengubah seluruh data profilnya dan perubahan tersimpan
- [ ] Foto lama tidak menumpuk di penyimpanan
- [ ] Akun nonaktif tidak bisa masuk, tetapi riwayat aktivitasnya tetap utuh
- [ ] Pengguna A tidak dapat membuka profil pengguna B lewat URL langsung

---

### 3.2 PBI-02 — Pengaturan Konsumsi Rumah Tangga

`KF-03` · Kerumitan **Menengah** · Endpoint `/pengaturan-rumah-tangga`

**Kenapa tingkat kerumitannya segitu**

Secara teknis sederhana, tetapi angkanya menjadi pembagi pada seluruh perhitungan emisi
listrik. Salah di sini membuat angka seluruh produk salah.

**Tabel yang dimiliki PBI ini**

- `user_settings` — id, user_id, household_size, power_watt, vehicle_ownership

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `users`

**Rincian CRUD**

| Operasi    | Yang dilakukan                                                                                                                              |
| ---------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| **Create** | Menambah data rumah tangga: jumlah penghuni, daya listrik terpasang, dan kepemilikan kendaraan. Satu pengguna hanya boleh punya satu baris. |
| **Read**   | Menampilkan ringkasan rumah tangga beserta penjelasan bagaimana angka itu dipakai membagi emisi listrik.                                    |
| **Update** | Memperbarui jumlah penghuni atau daya listrik. Perubahan berlaku untuk perhitungan berikutnya, tidak menghitung ulang riwayat.              |
| **Delete** | Menghapus data rumah tangga. Setelah dihapus, emisi listrik dihitung penuh tanpa pembagian.                                                 |

**Aturan validasi**

- household_size wajib, bilangan bulat, 1 sampai 20
- power_watt wajib, bilangan bulat, 450 sampai 50000
- vehicle_ownership wajib, salah satu dari: tidak ada, motor, mobil, keduanya

**Logika bisnis yang harus diperhatikan**

- Relasi ke users bersifat satu-ke-satu. Gunakan updateOrCreate, bukan create, supaya tidak muncul baris ganda.
- household_size dipakai sebagai pembagi emisi listrik. Nilai 0 harus mustahil tersimpan agar tidak terjadi pembagian dengan nol.

#### Skema Migrasi

```php
Schema::create('user_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->integer('household_size')->default(0);
    $table->integer('power_watt')->default(0);
    $table->string('vehicle_ownership');
    $table->timestamps();
});
```

#### Rute dan Endpoint

| Metode   | URI                        | Nama Rute       | Aksi                    | Akses          |
| -------- | -------------------------- | --------------- | ----------------------- | -------------- |
| `GET`    | `/pengaturan-rumah-tangga` | `rumah.index`   | Halaman pengaturan      | auth           |
| `PUT`    | `/pengaturan-rumah-tangga` | `rumah.upsert`  | Simpan (updateOrCreate) | auth + pemilik |
| `DELETE` | `/pengaturan-rumah-tangga` | `rumah.destroy` | Hapus data rumah tangga | auth + pemilik |

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/pengaturan-rumah-tangga` — satu halaman, satu kartu berisi tiga kolom isian

**Komponen antarmuka**

- Penggeser angka untuk jumlah penghuni, 1 sampai 20, dengan angka terbaca di samping
- Kolom daya listrik dengan satuan "watt" menempel di sisi kanan kolom
- Pilihan kepemilikan kendaraan berbentuk empat kartu pilihan, bukan menu tarik
- Kotak penjelasan: "Angka ini dipakai membagi emisi listrik rumahmu. Rumah berisi 4 orang berarti emisinya dibagi 4."

**Keadaan layar yang wajib dirancang**

| Keadaan      | Perilaku dan teks yang dilihat pengguna                                                                                      |
| ------------ | ---------------------------------------------------------------------------------------------------------------------------- |
| **Kosong**   | Pengguna yang belum mengisi melihat ajakan "Isi data rumah tanggamu agar angka emisi listrik lebih tepat" beserta tombolnya. |
| **Memuat**   | Kartu diselimuti kerangka abu-abu selama data diambil.                                                                       |
| **Galat**    | Nilai di luar rentang menampilkan pesan di bawah kolom: "Jumlah penghuni antara 1 sampai 20 orang."                          |
| **Berhasil** | Notifikasi "Pengaturan tersimpan." disertai catatan kecil "Berlaku untuk pencatatan berikutnya."                             |

**Perilaku responsif**

- 1440px: tiga kolom isian bersisian
- 768px: menumpuk satu kolom
- 375px: empat kartu kendaraan jadi dua baris dua kolom

**Aksesibilitas**

- Penggeser angka tetap bisa diubah lewat papan ketik dengan tombol panah
- Kartu pilihan kendaraan memakai radio sungguhan di baliknya, bukan div

**Komponen dan kelas CSS yang dipakai**

`<x-kartu>`, `<x-isian>`, `.ct-penggeser`, `.ct-pilihan-kartu`, `.ct-catatan`, `<x-tombol varian="utama">`

#### Kasus Batas

- Pengguna mengubah jumlah penghuni dari 4 menjadi 1 di tengah bulan: catatan lama tidak dihitung ulang, hanya catatan berikutnya yang memakai angka baru. Beri catatan kecil di layar agar pengguna tidak bingung angkanya tidak berubah.
- Pengguna menghapus data rumah tangga: emisi listrik berikutnya dihitung penuh tanpa pembagian, bukan gagal.
- Dua permintaan simpan tiba hampir bersamaan: updateOrCreate wajib dibungkus transaksi agar tidak muncul dua baris.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: household_size wajib
- [ ] Menolak ketika aturan dilanggar: power_watt wajib
- [ ] Menolak ketika aturan dilanggar: vehicle_ownership wajib
- [ ] Memastikan: satu pengguna tidak mungkin punya dua baris user_settings
- [ ] Memastikan: nilai di luar rentang ditolak dengan pesan dalam bahasa Indonesia
- [ ] Memastikan: modul Pencatatan dapat membaca angka ini lewat relasi $user->setting

**Berkas yang dibuat**

| Jenis         | Berkas                                                                                                |
| ------------- | ----------------------------------------------------------------------------------------------------- |
| Migration     | `database/migrations/2026_01_01_0000_create_user_settings_table_314.php`                              |
| Model         | `app/Models/UserSetting314.php`                                                                       |
| Controller    | `app/Http/Controllers/PengaturanKonsumsiRumahTangga314.php`                                           |
| Form Request  | `app/Http/Requests/SimpanPengaturanKonsumsiRumahTangga314.php`                                        |
| Policy        | `app/Policies/PengaturanKonsumsiRumahTanggaPolicy314.php`                                             |
| View daftar   | `resources/views/akun-dan-administrasi-pengguna/pengaturan-konsumsi-rumah-tangga-314/index.blade.php` |
| View formulir | `resources/views/akun-dan-administrasi-pengguna/pengaturan-konsumsi-rumah-tangga-314/form.blade.php`  |
| Seeder        | `database/seeders/PengaturanKonsumsiRumahTanggaSeeder314.php`                                         |
| Feature Test  | `tests/Feature/PengaturanKonsumsiRumahTanggaTest314.php`                                              |

**Kriteria selesai**

- [ ] Satu pengguna tidak mungkin punya dua baris user_settings
- [ ] Nilai di luar rentang ditolak dengan pesan dalam bahasa Indonesia
- [ ] Modul Pencatatan dapat membaca angka ini lewat relasi $user->setting

---

### 3.3 PBI-20 — Papan Peringkat Komunitas

`KF-26` · Kerumitan **Menengah** · Endpoint `/papan-peringkat`

**Kenapa tingkat kerumitannya segitu**

Tidak punya tabel sendiri, tetapi seluruh isinya hasil kueri agregasi lintas tiga tabel
dengan tiga pilihan periode. Kerumitannya ada di kueri dan di performa, bukan di formulir.

**Tabel yang dimiliki PBI ini**

- Tidak ada. PBI ini hanya membaca data milik PBI lain.

**Tabel yang hanya dibaca (milik anggota lain, jangan diubah strukturnya)**

- `users`, `point_logs`, `challenge_user`

**Rincian CRUD**

| Operasi    | Yang dilakukan                                                                                                                                                                            |
| ---------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Create** | Tidak ada. Peringkat dihasilkan dari poin dan keikutsertaan tantangan yang ditulis modul Gamifikasi.                                                                                      |
| **Read**   | Daftar peringkat menurut poin, streak beruntun, atau penurunan emisi, dengan penyaringan periode mingguan, bulanan, dan sepanjang waktu, serta penanda posisi pengguna yang sedang masuk. |
| **Update** | Tidak ada. Urutan diperbarui sendiri setiap kali poin berubah.                                                                                                                            |
| **Delete** | Tidak ada. Papan peringkat tidak menyimpan data miliknya sendiri.                                                                                                                         |

**Aturan validasi**

- Parameter periode hanya menerima: minggu, bulan, semua
- Parameter urutan hanya menerima: poin, streak, penurunan
- Nomor halaman bilangan bulat positif

**Logika bisnis yang harus diperhatikan**

- Kueri agregasi wajib memakai groupBy di sisi basis data. Jangan mengambil seluruh baris lalu mengurutkannya di PHP.
- Hasil disimpan di cache selama lima menit agar halaman tidak menghitung ulang pada setiap pemuatan.
- Pengguna nonaktif tidak ikut ditampilkan.
- Posisi pengguna yang sedang masuk tetap ditampilkan meski berada di luar sepuluh besar.

#### Rute dan Endpoint

| Metode | URI                    | Nama Rute         | Aksi                    | Akses |
| ------ | ---------------------- | ----------------- | ----------------------- | ----- |
| `GET`  | `/papan-peringkat`     | `peringkat.index` | Halaman papan peringkat | auth  |
| `GET`  | `/api/papan-peringkat` | `peringkat.data`  | Data peringkat (JSON)   | auth  |

#### API

`GET /api/papan-peringkat?periode=bulan&urut=poin&halaman=1`

Dipakai tabel papan peringkat saat tab periode diganti, supaya halaman tidak dimuat ulang.

```jsonc
{
    "periode": "bulan",
    "urut": "poin",
    "data": [
        {
            "peringkat": 1,
            "nama": "Rani P.",
            "foto": "/storage/...",
            "poin": 2140,
            "streak": 21,
            "penurunan": 18.4,
        },
    ],
    "saya": { "peringkat": 37, "poin": 640, "streak": 5, "penurunan": 6.1 },
    "total_peserta": 128,
}
```

#### Tampilan dan Pengalaman Pengguna

**Layar yang dibuat**

- `/papan-peringkat` — satu halaman, tiga tab periode di atas tabel

**Komponen antarmuka**

- Tiga tab: Mingguan, Bulanan, Sepanjang Waktu
- Menu urutan: Poin, Streak, Penurunan Emisi
- Tiga besar ditampilkan sebagai podium dengan foto bulat, sisanya tabel
- Baris milik pengguna sendiri diberi latar aksen dan tetap menempel di bagian bawah layar meski posisinya jauh di bawah

**Keadaan layar yang wajib dirancang**

| Keadaan      | Perilaku dan teks yang dilihat pengguna                                                                              |
| ------------ | -------------------------------------------------------------------------------------------------------------------- |
| **Kosong**   | Belum ada pengguna berpoin: "Papan peringkat masih kosong. Catat aktivitas pertamamu untuk mulai mengumpulkan poin." |
| **Memuat**   | Sepuluh baris kerangka abu-abu, tinggi sama dengan baris asli, supaya halaman tidak melompat saat data masuk.        |
| **Galat**    | Kotak di tengah dengan tombol "Coba lagi", bukan halaman putih.                                                      |
| **Berhasil** | Tidak ada notifikasi. Pergantian tab langsung mengganti isi tabel tanpa memuat ulang halaman.                        |

**Perilaku responsif**

- 1440px: podium tiga kolom, tabel enam kolom
- 768px: podium jadi satu baris mendatar yang bisa digeser
- 375px: tabel menyusut jadi kartu per pengguna, kolom streak disembunyikan

**Aksesibilitas**

- Tab memakai peran tablist dan dapat dipindah dengan tombol panah
- Peringkat dibacakan sebagai "Peringkat 3 dari 128", bukan hanya angka
- Warna podium tidak menjadi satu-satunya pembeda; ada angka peringkat tertulis

**Komponen dan kelas CSS yang dipakai**

`<x-tab>`, `<x-tabel>`, `.ct-podium`, `.ct-baris-saya`, `<x-kerangka jenis="tabel">`, `<x-kosong>`

#### Kasus Batas

- Dua pengguna berpoin sama: urutan kedua ditentukan streak, lalu tanggal bergabung, supaya peringkat tidak berubah-ubah setiap kali halaman dimuat.
- Pengguna yang dinonaktifkan di tengah periode: hilang dari papan peringkat, tetapi poinnya tidak dihapus.
- Pengguna baru tanpa poin membuka halaman: tetap melihat posisinya sebagai peringkat terakhir, bukan baris kosong.
- Cache lima menit membuat poin yang baru diperoleh belum terlihat: beri catatan kecil "Diperbarui beberapa menit lalu" agar tidak dianggap bug.

#### Pengujian yang Wajib Ada

- [ ] Menolak ketika aturan dilanggar: Parameter periode hanya menerima: minggu
- [ ] Menolak ketika aturan dilanggar: Parameter urutan hanya menerima: poin
- [ ] Menolak ketika aturan dilanggar: Nomor halaman bilangan bulat positif
- [ ] Memastikan: tiga pilihan periode menghasilkan urutan yang berbeda dan benar
- [ ] Memastikan: halaman tetap terbuka di bawah dua detik dengan 500 baris data uji
- [ ] Memastikan: pengguna yang belum punya poin tidak membuat halaman galat

**Berkas yang dibuat**

| Jenis         | Berkas                                                                                         |
| ------------- | ---------------------------------------------------------------------------------------------- |
| Controller    | `app/Http/Controllers/PapanPeringkatKomunitas314.php`                                          |
| Form Request  | `app/Http/Requests/SimpanPapanPeringkatKomunitas314.php`                                       |
| Policy        | `app/Policies/PapanPeringkatKomunitasPolicy314.php`                                            |
| View daftar   | `resources/views/akun-dan-administrasi-pengguna/papan-peringkat-komunitas-314/index.blade.php` |
| View formulir | `resources/views/akun-dan-administrasi-pengguna/papan-peringkat-komunitas-314/form.blade.php`  |
| Seeder        | `database/seeders/PapanPeringkatKomunitasSeeder314.php`                                        |
| Feature Test  | `tests/Feature/PapanPeringkatKomunitasTest314.php`                                             |

**Kriteria selesai**

- [ ] Tiga pilihan periode menghasilkan urutan yang berbeda dan benar
- [ ] Halaman tetap terbuka di bawah dua detik dengan 500 baris data uji
- [ ] Pengguna yang belum punya poin tidak membuat halaman galat

---

## 4. Acuan Tampilan Modul Ini

Dua berkas prompt Figma menjadi acuan: versi wireframe hitam putih untuk struktur, dan versi berwarna untuk tampilan akhir.

- Frame 2 Login dan Frame 3 Daftar pada wireframe.
- Frame 4 Dashboard: menu sidebar "Profil" dan "Papan Peringkat".

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
    --ct-bg: #06130e; /* latar halaman */
    --ct-bg-alt: #0a0f0d; /* bagian selang-seling */
    --ct-surface: #111a17; /* kartu, tabel, panel */
    --ct-surface-raised: #1a2622; /* modal, menu, panel geser */
    --ct-border: #1e2b26; /* bingkai dan pemisah */

    /* makna */
    --ct-primary: #10b981; /* tombol utama, tautan, penanda aktif */
    --ct-accent: #a3e635; /* gradien, sorotan angka */
    --ct-warning: #f59e0b; /* target terpakai 70-99% */
    --ct-danger: #f43f5e; /* galat, hapus, target terlampaui */
    --ct-info: #38bdf8; /* catatan netral */

    /* teks */
    --ct-text: #e8edeb;
    --ct-text-muted: #8ca099;

    /* jarak, kelipatan 4 */
    --ct-s1: 4px;
    --ct-s2: 8px;
    --ct-s3: 12px;
    --ct-s4: 16px;
    --ct-s5: 24px;
    --ct-s6: 32px;
    --ct-s7: 48px;
    --ct-s8: 64px;

    /* sudut */
    --ct-r-card: 16px;
    --ct-r-ctrl: 12px;
    --ct-r-pill: 999px;

    /* lainnya */
    --ct-font: "Plus Jakarta Sans", system-ui, sans-serif;
    --ct-ring: 0 0 0 3px rgb(16 185 129 / 0.45); /* bingkai fokus */
    --ct-max: 1200px; /* lebar isi maksimal */
    --ct-prose: 680px; /* lebar paragraf maksimal */
}

* {
    box-sizing: border-box;
}
body {
    margin: 0;
    background: var(--ct-bg);
    color: var(--ct-text);
    font-family: var(--ct-font);
    font-size: 16px;
    line-height: 1.6;
}
:focus-visible {
    outline: none;
    box-shadow: var(--ct-ring);
    border-radius: var(--ct-r-ctrl);
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation: none !important;
        transition: none !important;
    }
}
```

### Skala tipografi

| Peran         | Ukuran | Ketebalan | Tinggi baris |
| ------------- | ------ | --------- | ------------ |
| Judul besar   | 48px   | 800       | 1,1          |
| Judul halaman | 32px   | 700       | 1,2          |
| Judul bagian  | 24px   | 700       | 1,3          |
| Judul kartu   | 18px   | 700       | 1,4          |
| Isi           | 16px   | 400       | 1,6          |
| Keterangan    | 14px   | 400       | 1,5          |
| Label kecil   | 12px   | 500       | 1,4          |
| Angka besar   | 40px   | 800       | 1,0          |

Paragraf tidak boleh lebih lebar dari `--ct-prose`.

### Jarak baku

| Konteks                      | Nilai     |
| ---------------------------- | --------- |
| Antarelemen dalam satu kartu | `--ct-s3` |
| Isi kartu ke bingkainya      | `--ct-s5` |
| Antarkartu                   | `--ct-s5` |
| Antarbagian halaman          | `--ct-s8` |
| Tepi halaman di layar lebar  | 120px     |
| Tepi halaman di ponsel       | `--ct-s4` |

### CSS komponen

```css
/* ---------- tombol ---------- */
.ct-tombol {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--ct-s2);
    height: 48px;
    padding: 0 var(--ct-s5);
    border-radius: var(--ct-r-ctrl);
    font-weight: 600;
    font-size: 15px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: filter 0.15s ease;
}
.ct-tombol--utama {
    background: linear-gradient(90deg, var(--ct-primary), var(--ct-accent));
    color: var(--ct-bg);
}
.ct-tombol--sekunder {
    background: var(--ct-surface);
    border-color: var(--ct-border);
    color: var(--ct-text);
}
.ct-tombol--hantu {
    background: transparent;
    color: var(--ct-primary);
    padding: 0 var(--ct-s3);
}
.ct-tombol--bahaya {
    background: var(--ct-danger);
    color: #fff;
}
.ct-tombol:hover:not(:disabled) {
    filter: brightness(1.08);
}
.ct-tombol:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
/* saat memuat: ukuran TIDAK berubah, hanya isinya */
.ct-tombol[aria-busy="true"] {
    pointer-events: none;
}
@media (max-width: 640px) {
    .ct-tombol {
        height: 52px;
        width: 100%;
    }
}

/* ---------- kolom isian ---------- */
.ct-isian-grup {
    display: flex;
    flex-direction: column;
    gap: var(--ct-s2);
}
.ct-label {
    font-size: 14px;
    color: var(--ct-text-muted);
}
.ct-isian {
    height: 48px;
    padding: 0 var(--ct-s4);
    border-radius: var(--ct-r-ctrl);
    background: var(--ct-surface);
    border: 1px solid var(--ct-border);
    color: var(--ct-text);
    font-family: inherit;
    font-size: 15px;
    width: 100%;
}
.ct-isian::placeholder {
    color: var(--ct-text-muted);
    opacity: 0.7;
}
.ct-isian[aria-invalid="true"] {
    border-color: var(--ct-danger);
}
.ct-galat {
    font-size: 13px;
    color: var(--ct-danger);
} /* tepat di bawah kolomnya */
.ct-isian-satuan {
    position: relative;
}
.ct-isian-satuan > span {
    position: absolute;
    right: var(--ct-s4);
    top: 50%;
    transform: translateY(-50%);
    color: var(--ct-text-muted);
    font-size: 14px;
    pointer-events: none;
}

/* ---------- kartu ---------- */
.ct-kartu {
    background: var(--ct-surface);
    border: 1px solid var(--ct-border);
    border-radius: var(--ct-r-card);
    padding: var(--ct-s5);
}
.ct-kartu__judul {
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 var(--ct-s3);
}

/* ---------- pelat ---------- */
.ct-pelat {
    display: inline-flex;
    align-items: center;
    gap: var(--ct-s1);
    padding: 2px var(--ct-s3);
    border-radius: var(--ct-r-pill);
    font-size: 12px;
    font-weight: 500;
    border: 1px solid var(--ct-border);
}
.ct-pelat--aman {
    color: var(--ct-primary);
}
.ct-pelat--hampir {
    color: var(--ct-warning);
}
.ct-pelat--terlampaui {
    color: var(--ct-danger);
}

/* ---------- tabel ---------- */
.ct-tabel {
    width: 100%;
    border-collapse: collapse;
}
.ct-tabel th {
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.04em;
    color: var(--ct-text-muted);
    padding: var(--ct-s3) var(--ct-s4);
    border-bottom: 1px solid var(--ct-border);
}
.ct-tabel td {
    padding: var(--ct-s4);
    border-bottom: 1px solid var(--ct-border);
}
.ct-tabel .angka {
    text-align: right;
    font-variant-numeric: tabular-nums;
}
/* di ponsel tabel menjadi kartu, TIDAK digeser mendatar */
@media (max-width: 640px) {
    .ct-tabel,
    .ct-tabel tbody,
    .ct-tabel tr,
    .ct-tabel td {
        display: block;
        width: 100%;
    }
    .ct-tabel thead {
        display: none;
    }
    .ct-tabel tr {
        border: 1px solid var(--ct-border);
        border-radius: var(--ct-r-card);
        margin-bottom: var(--ct-s3);
        padding: var(--ct-s3);
    }
    .ct-tabel td {
        border: 0;
        display: flex;
        justify-content: space-between;
    }
    .ct-tabel td::before {
        content: attr(data-label);
        color: var(--ct-text-muted);
    }
}

/* ---------- kerangka memuat ---------- */
/* ukurannya WAJIB sama dengan isi aslinya supaya halaman tidak melompat */
.ct-kerangka {
    background: linear-gradient(
        90deg,
        var(--ct-surface),
        var(--ct-surface-raised),
        var(--ct-surface)
    );
    background-size: 200% 100%;
    animation: ct-denyut 1.4s ease-in-out infinite;
    border-radius: var(--ct-r-ctrl);
}
@keyframes ct-denyut {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

/* ---------- keadaan kosong ---------- */
.ct-kosong {
    text-align: center;
    padding: var(--ct-s8) var(--ct-s5);
}
.ct-kosong__ikon {
    color: var(--ct-text-muted);
    opacity: 0.6;
    margin-bottom: var(--ct-s4);
}
.ct-kosong__teks {
    color: var(--ct-text-muted);
    margin-bottom: var(--ct-s5);
}

/* ---------- cincin kemajuan (target) ---------- */
.ct-cincin {
    --nilai: 0;
    width: 240px;
    aspect-ratio: 1;
    border-radius: 50%;
    background: conic-gradient(
        var(--ct-warna) calc(var(--nilai) * 1%),
        var(--ct-border) 0
    );
    display: grid;
    place-items: center;
}
.ct-cincin::after {
    content: "";
    width: 76%;
    aspect-ratio: 1;
    border-radius: 50%;
    background: var(--ct-surface);
    grid-area: 1/1;
}
.ct-cincin__isi {
    grid-area: 1/1;
    z-index: 1;
    text-align: center;
}
@media (max-width: 1024px) {
    .ct-cincin {
        width: 180px;
    }
}
@media (max-width: 640px) {
    .ct-cincin {
        width: 140px;
    }
}

/* ---------- batang kemajuan ---------- */
.ct-kemajuan {
    height: 8px;
    background: var(--ct-border);
    border-radius: var(--ct-r-pill);
    overflow: hidden;
}
.ct-kemajuan__isi {
    height: 100%;
    background: var(--ct-primary);
    border-radius: inherit;
}

/* ---------- tabel pendamping grafik (untuk pembaca layar) ---------- */
.ct-tabel-tersembunyi {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0 0 0 0);
    white-space: nowrap;
}
```

### Komponen Blade bersama

Dibuat **sekali** oleh Hadi selaku Front-End Lead pada minggu pertama Sprint 1,
sebelum siapa pun mulai membuat halaman. Letaknya `resources/views/components/`.

| Komponen              | Atribut                                             | Catatan                                           |
| --------------------- | --------------------------------------------------- | ------------------------------------------------- |
| `<x-tombol>`          | `varian` (utama, sekunder, hantu, bahaya), `memuat` | Ukuran tidak berubah saat memuat                  |
| `<x-isian>`           | `label`, `nama`, `satuan`, `galat`                  | Label selalu di atas, bukan teks bayangan         |
| `<x-kartu>`           | `judul`                                             |                                                   |
| `<x-tabel>`           | `kepala`                                            | Otomatis jadi kartu di 640px                      |
| `<x-pelat>`           | `varian`                                            | Selalu berisi tulisan, bukan warna saja           |
| `<x-modal>`           | `judul`, `bahaya`                                   | Menahan fokus, tutup dengan Esc                   |
| `<x-panel-geser>`     | `judul`, `lebar`                                    | Untuk formulir, daftar di belakang tetap terlihat |
| `<x-notifikasi>`      | `jenis`                                             | Kanan atas, hilang 4 detik, maksimal satu         |
| `<x-kosong>`          | `ikon`, `pesan`, `aksi`                             | Wajib untuk setiap daftar, tabel, dan grafik      |
| `<x-kerangka>`        | `jenis` (teks, kartu, tabel, grafik, angka)         | Seukuran isi aslinya                              |
| `<x-tab>`             | `item`, `aktif`                                     | Dipindah dengan tombol panah                      |
| `<x-kartu-statistik>` | `label`, `angka`, `satuan`                          |                                                   |

### Empat keadaan yang wajib ada di setiap layar

| Keadaan      | Aturan                                                                                                                                                                      |
| ------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Kosong**   | Ikon atau ilustrasi, satu kalimat yang menjelaskan kenapa kosong dan apa untungnya diisi, satu tombol menuju aksi pengisinya. Jangan pernah menulis "Data tidak ditemukan". |
| **Memuat**   | Kerangka abu-abu seukuran isi aslinya, bukan lingkaran berputar di tengah halaman. Tiap bagian memuat sendiri-sendiri.                                                      |
| **Galat**    | Galat satu bagian hanya merusak bagian itu. Selalu ada tombol "Coba lagi". Galat formulir muncul tepat di bawah kolomnya, bukan dikumpulkan di atas.                        |
| **Berhasil** | Umpan balik yang terlihat: notifikasi, baris baru yang disorot singkat, atau angka yang berubah.                                                                            |

### Tata letak dan navigasi

| Kerangka | Dipakai untuk                                          | Isi                                                                            |
| -------- | ------------------------------------------------------ | ------------------------------------------------------------------------------ |
| Publik   | Landing, kalkulator, edukasi, komunitas, masuk, daftar | Navbar pelat mengambang, isi di tengah maksimal `--ct-max`, footer empat kolom |
| Aplikasi | Seluruh halaman setelah masuk                          | Sidebar kiri 240px, bilah atas 72px, isi di kanan                              |

Sidebar berisi delapan menu berurutan: Dashboard, Catat Aktivitas, Riwayat,
Target, Tantangan, Papan Peringkat, Edukasi, Profil. Menu admin menjadi kelompok
terpisah di bawah, hanya bagi yang berperan administrator. Halaman yang sedang
dibuka ditandai garis warna utama di sisi kiri **dan** teks lebih terang, dua
penanda, bukan satu.

| Lebar          | Perilaku                                                                              |
| -------------- | ------------------------------------------------------------------------------------- |
| 1440px ke atas | Tata letak penuh, sidebar terbuka                                                     |
| 1024px         | Isi menyempit, sidebar tetap terbuka                                                  |
| 768px          | Sidebar menyusut jadi ikon saja                                                       |
| 375px          | Sidebar jadi menu bawah lima ikon, isi satu kolom, aksi utama menempel di bawah layar |

### Aturan penulisan teks antarmuka

Seluruh teks berbahasa Indonesia, tanpa campuran.

| Jangan tulis              | Tulis                             |
| ------------------------- | --------------------------------- |
| Submit                    | Simpan                            |
| Delete                    | Hapus                             |
| Loading...                | Memuat...                         |
| Data tidak valid          | Jumlah harus lebih besar dari nol |
| Error 500                 | Gagal memuat. Coba lagi.          |
| Record berhasil di-create | Catatan tersimpan                 |
| Emission factor           | Faktor emisi                      |

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

| Sprint   | Minggu     | Yang dikerjakan |
| -------- | ---------- | --------------- |
| Sprint 1 | Week 7–10  | PBI-01, PBI-02  |
| Sprint 2 | Week 11–15 | PBI-20          |

---

## 6. Catatan Khusus

- Kerjakan KF-01 dan KF-04 paling awal di Sprint 1. Seluruh anggota lain tidak bisa menguji
  apa pun sebelum bisa masuk ke aplikasi.
- PBI-20 bergantung pada point_logs milik Man Rezki. Sepakati bentuk kolomnya sejak awal,
  lalu kerjakan PBI-20 di Sprint 2.
- Tabel users disentuh seluruh modul. Jangan mengubah strukturnya tanpa memberi tahu Alvin
  sebagai pemegang cabang utama.

---

## 7. Sebelum Mengajukan Permintaan Gabung

- [ ] Seluruh kriteria selesai pada setiap PBI sudah tercentang
- [ ] Keempat keadaan layar sudah dibuat untuk setiap halaman: kosong, memuat, galat, dan berhasil
- [ ] Sudah dicoba pada lebar 1440px, 768px, dan 375px
- [ ] Seluruh alur dapat diselesaikan hanya dengan papan ketik
- [ ] Seluruh teks antarmuka berbahasa Indonesia dan tanpa istilah teknis
- [ ] Nama berkas sudah berakhiran `314` dan nama kelas di dalamnya cocok
- [ ] Setiap model sudah menyebut `protected $table`
- [ ] Migration dapat dijalankan dari nol tanpa galat (`migrate:fresh --seed`)
- [ ] Tidak ada perubahan pada tabel milik anggota lain
- [ ] Feature test untuk setiap PBI sudah lulus
- [ ] Sudah diuji Renaya selaku Quality Assurance
- [ ] Sudah ditinjau Alvin selaku pemegang cabang utama
