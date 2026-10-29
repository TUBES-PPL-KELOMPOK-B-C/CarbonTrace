# README Pengembangan — Fitur Pendukung di Luar PBI

**Proyek** CarbonTrace — Platform Pelacakan dan Pemangkasan Jejak Karbon Harian  
**Isi** Sembilan fitur yang wajib ada di sistem tetapi tidak dihitung sebagai PBI  
**Berlaku untuk** Seluruh anggota, menurut pemilik masing-masing

---

## Kenapa sembilan fitur ini dipisah

Ketentuan dosen menyebut bahwa fitur login, logout, register, dan manajemen roles tidak
dihitung sebagai PBI, meski wajib ada. Di luar itu, beberapa fitur lain juga tidak memenuhi
syarat PBI karena tidak mengelola datanya sendiri: ia hanya membaca data milik PBI lain,
atau berjalan di belakang layar tanpa antarmuka pengelolaan. Sembilan fitur itulah yang
dikumpulkan di berkas ini. Semuanya tetap wajib dikerjakan, tetap muncul pada Kebutuhan
Fungsional di proposal, dan tetap dibagi pemiliknya pada Tabel 12.

> Khusus KF-01, ketentuan dosen menyebut bahwa anggota yang mengerjakannya mendapat nilai tambahan meski fiturnya tidak dihitung sebagai PBI.

## Ringkasan dan pemiliknya

| KF | Nama Fitur | Pemilik | Akhiran | Kerumitan |
|---|---|---|---|---|
| KF-01 | Registrasi, Login, dan Logout | Rosi | `314` | Menengah |
| KF-04 | Manajemen Pengguna dan Peran | Rosi | `314` | Menengah |
| KF-20 | Perhitungan Emisi Otomatis | Renaya | `154` | Tinggi |
| KF-21 | Dashboard Jejak Karbon | Alvin | `022` | Menengah |
| KF-22 | Grafik Tren Emisi | Alvin | `022` | Menengah |
| KF-23 | Rincian Emisi per Kategori | Alvin | `022` | Menengah |
| KF-24 | Kalkulator Jejak Karbon Publik | Hadi | `221` | Menengah |
| KF-25 | Pengisian Kuis Awal dan Garis Dasar | Hadi | `221` | Menengah |
| KF-27 | Statistik Dampak Komunitas | Tio | `365` | Rendah |

Beban per anggota: Rosi 2 fitur, Alvin 3 fitur, Hadi 2 fitur, Renaya 1 fitur, Tio 1 fitur, Man Rezki tidak ada. Man Rezki tidak kebagian karena papan peringkat yang memakai datanya sudah dihitung sebagai PBI-20 dan dikerjakan Rosi.

## Konvensi penamaan

Sama seperti README per anggota: setiap berkas berakhiran tiga digit terakhir NIM pemiliknya, nama kelas sama persis dengan nama berkas, dan nama tabel tidak pernah diberi akhiran. Setiap model yang namanya berakhiran NIM wajib menyebut tabelnya sendiri lewat `protected $table`.

---

## 1. KF-01 — Registrasi, Login, dan Logout

**Pemilik** Rosi Rahmawati (102022400314) · **Akhiran** `314` · **Kerumitan** Menengah · **Endpoint** `/daftar, /masuk, /keluar`

**Status dalam penilaian**

Wajib ada di sistem. Tidak dihitung sebagai PBI, tetapi menurut ketentuan dosen yang
mengerjakannya mendapat nilai tambahan.

**Kenapa tingkat kerumitannya segitu**

Secara teknis ditopang Laravel Breeze, tetapi perlu penyesuaian: pendaftaran harus sekaligus
membuat baris profil dan memberi peran bawaan, serta menyediakan masuk lewat akun Google.

**Tabel yang dimiliki**

- `password_reset_tokens` — email, token, created_at
- `sessions` — id, user_id, ip_address, user_agent, payload, last_activity

**Tabel yang hanya dibaca**

- `users`, `roles`

**Cakupan kerja**

| Bagian | Yang dilakukan |
|---|---|
| **Registrasi** | Membuat akun baru, memberi peran bawaan "pengguna", lalu mengarahkan ke kuis awal (KF-25). |
| **Login** | Memeriksa surel dan kata sandi, menolak akun yang is_active bernilai 0, lalu mengarahkan ke dashboard. |
| **Login Google** | Memakai Laravel Socialite. Jika surelnya sudah terdaftar, akun lama dipakai; jika belum, akun baru dibuat. |
| **Logout** | Mengakhiri sesi dan memutar ulang token sesi. |
| **Lupa kata sandi** | Mengirim tautan pemulihan ke surel pengguna. |

**Aturan dan batasan**

- name wajib, maksimal 100 karakter
- email wajib, format surel, unik pada tabel users
- password wajib, minimal 8 karakter, wajib konfirmasi
- campus opsional, maksimal 120 karakter
- persetujuan ketentuan layanan wajib dicentang

**Logika yang harus diperhatikan**

- Pakai Laravel Socialite, bukan Firebase. Firebase akan menambah satu sumber identitas kedua di luar MySQL, sehingga user_id pada seluruh tabel lain menjadi tidak konsisten.
- Pendaftaran wajib membuat baris users sekaligus memberi peran bawaan lewat satu transaksi basis data, supaya tidak ada akun tanpa peran.
- Akun dengan is_active bernilai 0 ditolak saat masuk dengan pesan yang jelas, bukan dengan pesan kata sandi salah.
- Seluruh rute modul lain berada di balik middleware auth. Sepakati ini di awal supaya anggota lain tidak menulis pemeriksaan sendiri-sendiri.

**Rute dan Endpoint**

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/masuk` | `auth.masuk` | Halaman masuk | tamu |
| `POST` | `/masuk` | `auth.proses` | Proses masuk | tamu |
| `GET` | `/daftar` | `auth.daftar` | Halaman daftar | tamu |
| `POST` | `/daftar` | `auth.simpan` | Proses pendaftaran | tamu |
| `POST` | `/keluar` | `auth.keluar` | Keluar | auth |
| `GET` | `/masuk/google` | `auth.google` | Alihkan ke Google | tamu |
| `GET` | `/masuk/google/balik` | `auth.google.balik` | Terima balikan Google | tamu |
| `GET` | `/lupa-sandi` | `auth.lupa` | Formulir lupa sandi | tamu |
| `POST` | `/lupa-sandi` | `auth.kirim` | Kirim tautan pemulihan | tamu |
| `POST` | `/atur-ulang-sandi` | `auth.atur` | Simpan sandi baru | tamu |

**Tampilan dan Pengalaman Pengguna**

*Layar yang dibuat*

- `/masuk` — Frame 2 pada wireframe
- `/daftar` — Frame 3 pada wireframe
- `/lupa-sandi` dan `/atur-ulang-sandi`

*Komponen antarmuka*

- Kartu tengah 460px dengan logo di puncaknya
- Kolom kata sandi dengan ikon mata untuk memperlihatkan isinya
- Garis pemisah bertuliskan "atau" sebelum tombol masuk dengan Google
- Penanda kekuatan kata sandi saat mendaftar
- Tautan silang di bagian bawah antara halaman masuk dan daftar

*Keadaan layar yang wajib dirancang*

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Tidak berlaku. Formulir selalu terisi kosong di awal. |
| **Memuat** | Tombol berubah nonaktif dengan teks "Memeriksa..." agar tidak tertekan dua kali. |
| **Galat** | Surel atau kata sandi salah ditampilkan satu pesan di atas formulir, tanpa menyebut mana yang salah, demi keamanan. Akun nonaktif mendapat pesan berbeda: "Akun ini dinonaktifkan. Hubungi administrator." |
| **Berhasil** | Langsung diarahkan ke kuis awal bagi pengguna baru, atau ke dashboard bagi yang sudah pernah mengisi. |

*Perilaku responsif*

- 1440px: kartu di tengah dengan latar pola
- 768px: kartu melebar jadi 90 persen lebar layar
- 375px: kartu tanpa bingkai, menyatu dengan latar, tombol melebar penuh

*Aksesibilitas*

- Ikon mata punya label "Perlihatkan kata sandi" yang berubah saat ditekan
- Galat formulir memindahkan fokus ke pesannya agar pembaca layar membacakannya
- Tombol Google punya teks, bukan hanya logo

*Komponen dan kelas CSS yang dipakai*

`<x-kartu>`, `<x-isian>`, `<x-tombol varian="utama">`, `<x-tombol varian="sekunder">`, `.ct-pemisah-atau`, `.ct-kekuatan-sandi`

**Kasus Batas**

- Surel sudah terdaftar lewat pendaftaran biasa lalu pengguna masuk dengan Google: akun lama dipakai, bukan membuat akun kedua.
- Pengguna menekan tombol masuk dua kali cepat: tombol dinonaktifkan selama permintaan berjalan.
- Akun dinonaktifkan saat pengguna sedang masuk: sesi berikutnya ditolak dengan pesan khusus, bukan pesan kata sandi salah.
- Tautan pemulihan kata sandi dipakai dua kali: yang kedua ditolak.
- Google mengembalikan surel yang belum terverifikasi: tolak dan minta pendaftaran biasa.

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Controller | `app/Http/Controllers/Autentikasi314.php` |
| Controller | `app/Http/Controllers/LoginGoogle314.php` |
| Form Request | `app/Http/Requests/SimpanRegistrasi314.php` |
| View | `resources/views/auth/masuk-314.blade.php` |
| View | `resources/views/auth/daftar-314.blade.php` |
| View | `resources/views/auth/lupa-sandi-314.blade.php` |
| Feature Test | `tests/Feature/AutentikasiTest314.php` |

**Kriteria selesai**

- [ ] Pendaftaran menghasilkan satu baris users beserta perannya
- [ ] Masuk lewat Google menghasilkan akun yang sama jika surelnya sudah ada
- [ ] Akun nonaktif ditolak dengan pesan yang tepat
- [ ] Halaman mana pun di balik auth memantulkan tamu ke halaman masuk
- [ ] Tampilan cocok dengan Frame 2 dan Frame 3 pada wireframe

---

## 2. KF-04 — Manajemen Pengguna dan Peran

**Pemilik** Rosi Rahmawati (102022400314) · **Akhiran** `314` · **Kerumitan** Menengah · **Endpoint** `/admin/pengguna`

**Status dalam penilaian**

Wajib ada di sistem. Dulu sempat dihitung sebagai PBI, lalu dikeluarkan sesuai spesifikasi
minimum yang menyebut manajemen roles tidak dihitung.

**Kenapa tingkat kerumitannya segitu**

CRUD lugas, tetapi menyentuh hak akses seluruh aplikasi. Kesalahan di sini membuat pengguna
biasa bisa membuka panel administrator.

**Tabel yang dimiliki**

- `roles` — id, name, description
- `model_has_roles` — role_id, model_type, model_id

**Tabel yang hanya dibaca**

- `users`

**Cakupan kerja**

| Bagian | Yang dilakukan |
|---|---|
| **Create** | Menambah akun beserta perannya langsung dari panel admin. |
| **Read** | Daftar pengguna dengan penyaringan menurut peran dan status aktif, serta pencarian menurut nama atau surel. |
| **Update** | Mengubah peran atau status aktif seorang pengguna. |
| **Delete** | Memblokir akun dengan mengubah is_active menjadi 0. Penghapusan permanen dilarang karena activities merujuk padanya. |

**Aturan dan batasan**

- role_id wajib dan harus ada pada tabel roles
- Administrator tidak boleh menurunkan perannya sendiri
- Administrator terakhir tidak boleh dinonaktifkan
- email unik pada tabel users

**Logika yang harus diperhatikan**

- Pakai spatie/laravel-permission dengan dua peran: pengguna dan administrator.
- Seluruh rute panel admin dilindungi middleware peran, bukan pemeriksaan manual di dalam controller.
- Dua peran bawaan dibuat lewat seeder, berikut satu akun administrator awal.
- Jumlah administrator aktif dihitung sebelum penonaktifan, supaya sistem tidak pernah kehilangan seluruh administratornya.

**Rute dan Endpoint**

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/admin/pengguna` | `pengguna.index` | Tabel pengguna | auth + admin |
| `POST` | `/admin/pengguna` | `pengguna.store` | Tambah akun | auth + admin |
| `PATCH` | `/admin/pengguna/{pengguna}/peran` | `pengguna.peran` | Ubah peran | auth + admin |
| `PATCH` | `/admin/pengguna/{pengguna}/status` | `pengguna.status` | Aktif / blokir | auth + admin |

**Tampilan dan Pengalaman Pengguna**

*Layar yang dibuat*

- `/admin/pengguna` — tabel dengan pencarian dan dua penyaring

*Komponen antarmuka*

- Penyaring peran dan status aktif berbentuk pelat yang bisa ditekan
- Menu peran di setiap baris, berubah langsung tanpa membuka halaman baru
- Saklar aktif dengan konfirmasi sebelum mematikan
- Penanda "Kamu" pada baris milik administrator yang sedang masuk
- Tombol aksi dinonaktifkan pada baris diri sendiri

*Keadaan layar yang wajib dirancang*

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Pencarian tanpa hasil: "Tidak ada pengguna yang cocok dengan kata kunci itu." disertai tombol bersihkan pencarian. |
| **Memuat** | Kerangka tabel sepuluh baris. |
| **Galat** | Menonaktifkan administrator terakhir: "Sistem harus punya minimal satu administrator aktif." |
| **Berhasil** | Perubahan peran langsung terlihat di barisnya disertai notifikasi singkat. |

*Perilaku responsif*

- 1440px: tabel enam kolom
- 768px: kolom tanggal daftar disembunyikan
- 375px: kartu per pengguna dengan menu aksi di pojok

*Aksesibilitas*

- Perubahan peran lewat menu diumumkan lewat aria-live
- Baris diri sendiri diberi penanda tertulis, bukan hanya latar berbeda

*Komponen dan kelas CSS yang dipakai*

`<x-tabel>`, `.ct-pelat`, `.ct-saklar`, `<x-modal>`, `<x-kosong>`

**Kasus Batas**

- Administrator terakhir mencoba menurunkan perannya sendiri: ditolak.
- Peran pengguna diubah saat ia sedang membuka panel admin: permintaan berikutnya ditolak tanpa perlu masuk ulang.
- Pencarian dengan kata kunci yang mengandung tanda kutip: tidak boleh menimbulkan galat kueri.
- Pengguna diblokir saat punya catatan aktivitas: catatan tetap utuh, hanya aksesnya yang ditutup.

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_roles_table_314.php` |
| Model | `app/Models/Role314.php` |
| Controller | `app/Http/Controllers/ManajemenPenggunaDanPeran314.php` |
| Form Request | `app/Http/Requests/SimpanPengguna314.php` |
| Middleware | `app/Http/Middleware/PastikanAdministrator314.php` |
| Seeder | `database/seeders/RoleSeeder314.php` |
| View | `resources/views/akun/manajemen-pengguna-314/index.blade.php` |
| Feature Test | `tests/Feature/ManajemenPenggunaTest314.php` |

**Kriteria selesai**

- [ ] Pengguna biasa menerima galat 403 saat membuka rute admin
- [ ] Administrator terakhir tidak dapat dinonaktifkan
- [ ] Seeder menghasilkan dua peran dan satu akun administrator
- [ ] Perubahan peran langsung berlaku tanpa perlu masuk ulang

---

## 3. KF-20 — Perhitungan Emisi Otomatis

**Pemilik** Renaya Marvela Adoe (102022400154) · **Akhiran** `154` · **Kerumitan** Tinggi · **Endpoint** `(tidak ada rute; dipanggil sebagai kelas Service)`

**Status dalam penilaian**

Tidak dihitung sebagai PBI karena tidak punya antarmuka pengelolaan sendiri. Namun inilah
mesin yang dipakai PBI-04, PBI-05, dan KF-24.

**Kenapa tingkat kerumitannya segitu**

Satu-satunya tempat rumus emisi boleh ditulis. Harus menangani empat kategori dengan rumus
berbeda, pembagian rumah tangga untuk listrik, dan konversi satuan. Salah sedikit, seluruh
angka produk salah.

**Tabel yang dimiliki**

- Tidak ada. Fitur ini hanya membaca data milik PBI lain.

**Tabel yang hanya dibaca**

- `emission_factors`, `categories`, `appliances`, `portion_units`, `user_settings`

**Cakupan kerja**

| Bagian | Yang dilakukan |
|---|---|
| **hitung()** | Menerima faktor emisi, jumlah, dan pengguna, lalu mengembalikan angka kilogram CO2e dalam tipe desimal. |
| **hitungListrik()** | kWh = (daya watt x jam) dibagi 1000, lalu dikalikan faktor, lalu dibagi jumlah penghuni rumah. |
| **hitungMakanan()** | kilogram = porsi x kg_conversion, lalu dikalikan faktor. |
| **hitungTransportasi()** | kilometer dikalikan faktor secara langsung. |
| **hitungSampah()** | kilogram dikalikan faktor secara langsung. |

**Aturan dan batasan**

- Faktor emisi wajib berstatus aktif, jika tidak lemparkan pengecualian
- Jumlah wajib lebih besar dari 0
- Jumlah penghuni rumah tidak boleh 0 agar tidak terjadi pembagian dengan nol
- Satuan masukan wajib cocok dengan base_unit kategorinya

**Logika yang harus diperhatikan**

- Seluruh hasil bertipe decimal dengan dua angka desimal. Jangan sekali pun memakai bilangan bulat.
- Kelas ini tidak boleh menyentuh tabel activities. Ia hanya menerima angka dan mengembalikan angka, supaya mudah diuji.
- Jika pengguna belum mengisi data rumah tangga, emisi listrik dihitung penuh tanpa pembagian, bukan gagal.
- Kalkulator publik (KF-24) memanggil kelas yang sama tanpa pengguna, sehingga parameter pengguna harus boleh kosong.

**Tampilan dan Pengalaman Pengguna**

*Layar yang dibuat*

- Tidak ada tampilan. Ini kelas Service yang berjalan di belakang layar.

*Komponen antarmuka*

- Tidak ada komponen antarmuka.
- Namun bentuk angka yang dikembalikan menentukan tampilan di seluruh aplikasi: selalu dua angka desimal, memakai koma sebagai pemisah desimal sesuai kebiasaan Indonesia, dan selalu disertai satuan kg CO2e.

*Keadaan layar yang wajib dirancang*

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Mengembalikan nol, bukan null, supaya halaman pemanggil tidak perlu menangani keadaan kosong sendiri-sendiri. |
| **Memuat** | Tidak berlaku. |
| **Galat** | Melemparkan pengecualian bernama jelas agar halaman pemanggil bisa menampilkan pesan yang tepat, bukan galat 500. |
| **Berhasil** | Mengembalikan objek berisi angka, satuan, dan rincian perhitungannya, supaya halaman bisa menampilkan cara hitungnya jika pengguna ingin tahu. |

*Perilaku responsif*

- Tidak berlaku.

*Aksesibilitas*

- Sediakan rincian langkah perhitungan dalam bentuk teks, supaya PBI-04 bisa menampilkan "2 km x 0,12 kg = 0,24 kg" kepada pengguna yang ingin memahami angkanya.

*Komponen dan kelas CSS yang dipakai*

Tidak ada. Kelas Service tanpa tampilan.

**Kasus Batas**

- Faktor emisi diarsipkan di tengah perhitungan: lemparkan pengecualian bernama jelas agar pemanggil bisa menampilkan pesan tepat.
- Jumlah penghuni rumah bernilai nol karena data lama: perlakukan sebagai satu, jangan membagi dengan nol.
- Pengguna kosong karena dipanggil kalkulator publik: lewati pembagian rumah tangga, jangan gagal.
- Hasil perkalian sangat kecil, misalnya 0,0004: kembalikan apa adanya dengan dua desimal menjadi 0,00, tetapi simpan nilai penuhnya di rincian.

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Service | `app/Services/PerhitunganEmisi154.php` |
| Exception | `app/Exceptions/FaktorEmisiTidakAktif154.php` |
| Unit Test | `tests/Unit/PerhitunganEmisiTest154.php` |

**Kriteria selesai**

- [ ] AC 900 watt selama 8 jam pada rumah berisi 4 orang menghasilkan angka yang benar
- [ ] Nilai 1,5 kg tidak pernah terpotong menjadi 1 kg
- [ ] Pengguna tanpa data rumah tangga tetap mendapat angka, bukan galat
- [ ] Unit test mencakup keempat kategori dan lulus semua

---

## 4. KF-21 — Dashboard Jejak Karbon

**Pemilik** Alvin Hibatullah (102022430022) · **Akhiran** `022` · **Kerumitan** Menengah · **Endpoint** `/dashboard`

**Status dalam penilaian**

Tidak dihitung sebagai PBI karena hanya menampilkan hasil PBI milik modul lain dan tidak
mengelola data sendiri.

**Kenapa tingkat kerumitannya segitu**

Satu halaman yang menarik data dari lima modul sekaligus. Kerumitannya ada pada jumlah kueri
dan pada menjaga halaman tetap cepat.

**Tabel yang dimiliki**

- Tidak ada. Fitur ini hanya membaca data milik PBI lain.

**Tabel yang hanya dibaca**

- `activities`, `targets`, `users`, `point_logs`, `categories`, `announcements`

**Cakupan kerja**

| Bagian | Yang dilakukan |
|---|---|
| **Kartu statistik** | Empat angka di baris pertama: emisi hari ini, emisi bulan ini, sisa kuota target, dan jumlah EcoPoint. |
| **Grafik** | Memuat KF-22 grafik tren dan KF-23 rincian kategori. |
| **Aktivitas terbaru** | Lima catatan terakhir milik pengguna. |
| **Banner pengumuman** | Satu pengumuman terbaru yang sedang berlaku, dari KF-19. |
| **Tombol pintas** | Tombol bulat bertanda tambah di kanan bawah untuk membuka formulir pencatatan milik PBI-04. |

**Aturan dan batasan**

- Hanya dapat dibuka pengguna yang sudah masuk
- Pengguna baru tanpa data apa pun tetap melihat halaman utuh dengan angka nol

**Logika yang harus diperhatikan**

- Seluruh angka dihitung lewat satu kelas Service, bukan tersebar di Blade.
- Gunakan eager loading dan batasi jumlah kueri. Targetkan di bawah sepuluh kueri untuk satu pemuatan halaman.
- Halaman ini milik modul Target, tetapi isinya milik modul lain. Jangan menulis apa pun ke tabel milik anggota lain dari sini.
- Keadaan kosong wajib dirancang: pengguna hari pertama harus melihat ajakan mencatat, bukan grafik kosong tanpa penjelasan.

**Rute dan Endpoint**

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/dashboard` | `dashboard.index` | Halaman dashboard | auth |

**Tampilan dan Pengalaman Pengguna**

*Layar yang dibuat*

- `/dashboard` — Frame 4 pada wireframe

*Komponen antarmuka*

- Sidebar kiri 240px berisi delapan menu berikon
- Bilah atas berisi judul halaman, lonceng pemberitahuan, dan avatar
- Empat kartu statistik di baris pertama
- Grafik tren dan grafik donat di baris kedua
- Daftar lima aktivitas terbaru di baris ketiga
- Tombol bulat bertanda tambah menempel di kanan bawah

*Keadaan layar yang wajib dirancang*

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Pengguna hari pertama melihat kartu bernilai nol disertai satu kartu ajakan besar: "Catat aktivitas pertamamu untuk melihat angkanya." Grafik diganti ilustrasi garis sederhana, bukan grafik kosong tanpa penjelasan. |
| **Memuat** | Setiap bagian memuat sendiri-sendiri. Kartu statistik muncul duluan, grafik menyusul. Jangan menahan seluruh halaman menunggu bagian paling lambat. |
| **Galat** | Jika satu bagian gagal, hanya bagian itu yang menampilkan "Gagal memuat" beserta tombol coba lagi. Bagian lain tetap tampil. |
| **Berhasil** | Tidak ada notifikasi. Halaman ini hanya menampilkan. |

*Perilaku responsif*

- 1440px: sidebar terbuka, isi tiga kolom
- 768px: sidebar menyusut jadi ikon saja, isi dua kolom
- 375px: sidebar jadi menu bawah berisi lima ikon utama, isi satu kolom, tombol tambah tetap menempel di kanan bawah

*Aksesibilitas*

- Sidebar punya penanda halaman yang sedang aktif, tidak hanya lewat warna
- Urutan fokus papan ketik mengikuti urutan baca, bukan urutan di kode
- Setiap grafik didampingi tabel angka yang terbaca pembaca layar

*Komponen dan kelas CSS yang dipakai*

`.ct-sidebar`, `.ct-bilah-atas`, `<x-kartu-statistik>`, `<x-kerangka>`, `<x-kosong>`, `<x-tombol-tambang>`

**Kasus Batas**

- Pengguna hari pertama tanpa data sama sekali: seluruh kartu bernilai nol dan grafik diganti ajakan, bukan grafik kosong.
- Satu bagian gagal dimuat: hanya bagian itu yang menampilkan galat, bagian lain tetap tampil.
- Pengguna tanpa target berjalan: kartu sisa kuota diganti tombol "Tetapkan Target", bukan angka nol yang membingungkan.
- Data sangat banyak, misalnya 5000 catatan: jumlah kueri tetap di bawah sepuluh, periksa dengan Laravel Debugbar.

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Controller | `app/Http/Controllers/Dashboard022.php` |
| Service | `app/Services/RingkasanDashboard022.php` |
| View | `resources/views/dashboard/index-022.blade.php` |
| Komponen | `resources/views/components/kartu-statistik-022.blade.php` |
| Feature Test | `tests/Feature/DashboardTest022.php` |

**Kriteria selesai**

- [ ] Keempat kartu statistik menampilkan angka yang cocok dengan data uji
- [ ] Pengguna baru melihat halaman utuh tanpa galat
- [ ] Jumlah kueri di bawah sepuluh, diperiksa dengan Laravel Debugbar
- [ ] Tata letak cocok dengan Frame 4 pada wireframe

---

## 5. KF-22 — Grafik Tren Emisi

**Pemilik** Alvin Hibatullah (102022430022) · **Akhiran** `022` · **Kerumitan** Menengah · **Endpoint** `/dashboard/tren (mengembalikan JSON)`

**Status dalam penilaian**

Tidak dihitung sebagai PBI. Turunan tampilan dari data PBI-04.

**Kenapa tingkat kerumitannya segitu**

Perlu mengisi tanggal yang kosong agar garisnya tidak putus, dan perlu dua bentuk penyajian
karena PDF tidak bisa memakai pustaka grafik.

**Tabel yang dimiliki**

- Tidak ada. Fitur ini hanya membaca data milik PBI lain.

**Tabel yang hanya dibaca**

- `activities`

**Cakupan kerja**

| Bagian | Yang dilakukan |
|---|---|
| **Tren harian** | Jumlah emisi per hari pada tujuh atau tiga puluh hari terakhir. |
| **Pembanding** | Garis putus-putus menunjukkan rata-rata periode sebelumnya. |
| **Penyaring** | Pilihan rentang: 7 hari, 30 hari, atau periode target berjalan. |

**Aturan dan batasan**

- Parameter rentang hanya menerima: 7, 30, target
- Tanggal tanpa catatan diisi nilai nol, bukan dilewati

**Logika yang harus diperhatikan**

- Kueri memakai groupBy tanggal di sisi basis data.
- Tanggal kosong wajib diisi nol di sisi PHP, jika tidak garis grafik akan melompat dan menyesatkan pembaca.
- ApexCharts hanya untuk tampilan di layar. Untuk PDF pada PBI-12, grafik yang sama digambar ulang sebagai SVG di sisi server, karena dompdf tidak menjalankan JavaScript.

**Rute dan Endpoint**

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/api/dashboard/tren` | `dashboard.tren` | Data tren emisi (JSON) | auth |

**API**

`GET /api/dashboard/tren?rentang=7`

Mengisi grafik garis di dashboard. Tanggal tanpa catatan dikembalikan bernilai nol, bukan
dilewati, supaya garisnya tidak melompat.

```jsonc
{
  "rentang": 7,
  "titik": [
    { "tanggal": "2026-10-20", "emisi": 3.10 },
    { "tanggal": "2026-10-21", "emisi": 0.00 }
  ],
  "pembanding": 3.88,
  "satuan": "kg CO2e"
}
```

**Tampilan dan Pengalaman Pengguna**

*Layar yang dibuat*

- Tertanam di dashboard, bukan halaman sendiri

*Komponen antarmuka*

- Grafik garis tujuh atau tiga puluh hari dengan sumbu tanggal
- Garis putus-putus sebagai pembanding periode sebelumnya
- Tiga tombol rentang: 7 hari, 30 hari, Periode Target
- Kotak informasi saat kursor menyentuh titik: tanggal dan angka emisinya

*Keadaan layar yang wajib dirancang*

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Garis datar di nol disertai teks di tengah grafik: "Belum ada data untuk rentang ini." |
| **Memuat** | Kerangka berbentuk grafik, bukan kotak abu-abu polos, supaya ukurannya tidak berubah saat data masuk. |
| **Galat** | "Gagal memuat grafik" beserta tombol coba lagi, di dalam kotak grafik saja. |
| **Berhasil** | Pergantian rentang menganimasikan garis selama 300 milidetik. |

*Perilaku responsif*

- 1440px: tinggi 280px, seluruh label tanggal terlihat
- 768px: tinggi 240px, label tanggal diselang-seling
- 375px: tinggi 200px, hanya tanggal pertama dan terakhir yang diberi label

*Aksesibilitas*

- Tabel angka tersembunyi secara visual tetapi terbaca pembaca layar
- Warna garis bukan satu-satunya pembeda antara data dan pembanding; yang satu utuh, yang satu putus-putus
- Animasi dimatikan jika pengguna memilih kurangi gerak

*Komponen dan kelas CSS yang dipakai*

`.ct-grafik-garis`, `<x-kerangka jenis="grafik">`, `.ct-tabel-tersembunyi`, `<x-tab>`

**Kasus Batas**

- Rentang tujuh hari dengan hanya satu hari bercatatan: enam hari lain bernilai nol, garis tetap tersambung.
- Pengguna mengganti rentang berkali-kali dengan cepat: batalkan permintaan sebelumnya agar tidak ada data lama yang menimpa data baru.
- Periode target lebih panjang dari 30 hari: label tanggal diselang-seling agar tidak bertumpuk.

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Controller | `app/Http/Controllers/GrafikTrenEmisi022.php` |
| Service | `app/Services/DataTrenEmisi022.php` |
| View | `resources/views/components/grafik-tren-022.blade.php` |
| Feature Test | `tests/Feature/GrafikTrenTest022.php` |

**Kriteria selesai**

- [ ] Hari tanpa catatan tampil sebagai nol, bukan terlewat
- [ ] Pergantian rentang tidak memuat ulang seluruh halaman
- [ ] Versi SVG untuk PDF menampilkan angka yang sama dengan versi layar

---

## 6. KF-23 — Rincian Emisi per Kategori

**Pemilik** Alvin Hibatullah (102022430022) · **Akhiran** `022` · **Kerumitan** Menengah · **Endpoint** `/dashboard/kategori (mengembalikan JSON)`

**Status dalam penilaian**

Tidak dihitung sebagai PBI. Tampilan baca saja dari data PBI-04.

**Kenapa tingkat kerumitannya segitu**

Paling ringan di antara ketiga fitur pendukung modul ini. Kerumitannya hanya pada
pengelompokan dan pada mengambil warna dari data kategori.

**Tabel yang dimiliki**

- Tidak ada. Fitur ini hanya membaca data milik PBI lain.

**Tabel yang hanya dibaca**

- `activities`, `emission_factors`, `categories`

**Cakupan kerja**

| Bagian | Yang dilakukan |
|---|---|
| **Grafik donat** | Empat kategori beserta persentasenya. |
| **Legenda** | Empat baris berisi kotak warna, nama kategori, dan angka kilogram. |
| **Tautan rincian** | Klik satu kategori membuka daftar aktivitas kategori itu. |

**Aturan dan batasan**

- Rentang tanggal wajib, maksimal 366 hari
- Kategori tanpa catatan tetap ditampilkan dengan nilai nol

**Logika yang harus diperhatikan**

- Pengelompokan dilakukan lewat relasi activities ke emission_factors lalu ke categories, bukan dengan menyimpan category_id di activities.
- Warna donat diambil dari kolom color pada tabel categories milik Renaya, bukan ditulis tetap di kode.
- Persentase dibulatkan ke satu angka desimal dan jumlahnya wajib 100.

**Rute dan Endpoint**

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/api/dashboard/kategori` | `dashboard.kategori` | Rincian per kategori (JSON) | auth |

**API**

`GET /api/dashboard/kategori?mulai=2026-10-01&selesai=2026-10-31`

Mengisi grafik donat dan legendanya. Warna diambil dari tabel categories, bukan ditulis
tetap di kode.

```jsonc
{
  "total": 112.40,
  "segmen": [
    { "kategori_id": 1, "nama": "Transportasi", "emisi": 68.20, "persen": 60.7, "warna": "#10B981" },
    { "kategori_id": 2, "nama": "Listrik",      "emisi": 44.20, "persen": 39.3, "warna": "#38BDF8" }
  ]
}
```

**Tampilan dan Pengalaman Pengguna**

*Layar yang dibuat*

- Tertanam di dashboard, bukan halaman sendiri

*Komponen antarmuka*

- Grafik donat empat segmen dengan total di tengah lingkaran
- Legenda empat baris: kotak warna, nama kategori, kilogram, persentase
- Klik satu segmen membuka daftar aktivitas kategori itu

*Keadaan layar yang wajib dirancang*

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Donat abu-abu utuh dengan teks "Belum ada data" di tengahnya, bukan lingkaran hilang. |
| **Memuat** | Kerangka berbentuk lingkaran dengan legenda kerangka. |
| **Galat** | Pesan di dalam kotak grafik beserta tombol coba lagi. |
| **Berhasil** | Segmen beranimasi tumbuh dari nol selama 400 milidetik. |

*Perilaku responsif*

- 1440px: donat di kiri, legenda di kanan
- 768px: legenda turun ke bawah donat
- 375px: donat mengecil, legenda jadi dua kolom

*Aksesibilitas*

- Persentase ditulis di legenda, tidak hanya tergambar di donat
- Setiap segmen dapat dicapai dengan papan ketik dan menyebutkan isinya
- Empat warna kategori diperiksa agar tetap terbedakan oleh penderita buta warna

*Komponen dan kelas CSS yang dipakai*

`.ct-grafik-donat`, `.ct-legenda`, `<x-kerangka jenis="grafik">`, `.ct-tabel-tersembunyi`

**Kasus Batas**

- Seluruh emisi berasal dari satu kategori: donat berupa satu lingkaran penuh, legenda tetap menampilkan empat kategori dengan nilai nol.
- Total emisi nol: tampilkan donat abu-abu utuh dengan teks di tengah, bukan lingkaran hilang.
- Pembulatan membuat jumlah persentase menjadi 99,9: sesuaikan segmen terbesar agar totalnya tepat 100.

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Controller | `app/Http/Controllers/RincianPerKategori022.php` |
| Service | `app/Services/DataRincianKategori022.php` |
| View | `resources/views/components/grafik-donat-022.blade.php` |
| Feature Test | `tests/Feature/RincianKategoriTest022.php` |

**Kriteria selesai**

- [ ] Jumlah seluruh persentase tepat 100
- [ ] Warna donat berubah jika admin mengubah warna kategori
- [ ] Kategori tanpa catatan tetap muncul di legenda

---

## 7. KF-24 — Kalkulator Jejak Karbon Publik

**Pemilik** Hadi Dwi Pranoto (102022400221) · **Akhiran** `221` · **Kerumitan** Menengah · **Endpoint** `/kalkulator`

**Status dalam penilaian**

Tidak dihitung sebagai PBI karena hanya membaca faktor emisi dan menampilkan hasil, tanpa
menyimpan data apa pun.

**Kenapa tingkat kerumitannya segitu**

Satu-satunya halaman berhitung yang terbuka untuk pengunjung tanpa akun, sehingga perlu
pembatasan laju permintaan dan penanganan tanpa pengguna.

**Tabel yang dimiliki**

- Tidak ada. Fitur ini hanya membaca data milik PBI lain.

**Tabel yang hanya dibaca**

- `emission_factors`, `categories`, `appliances`, `portion_units`

**Cakupan kerja**

| Bagian | Yang dilakukan |
|---|---|
| **Formulir** | Pengunjung memilih kategori, mengisi jumlah, lalu melihat hasilnya tanpa perlu masuk. |
| **Hasil** | Angka kilogram CO2e beserta perbandingan sederhana, misalnya setara berapa kilometer berkendara. |
| **Ajakan** | Tombol "Daftar Gratis" untuk menyimpan hasilnya. |

**Aturan dan batasan**

- Kategori wajib dipilih
- Jumlah wajib, desimal lebih besar dari 0, maksimal 100000
- Pembatasan laju 30 permintaan per menit per alamat IP

**Logika yang harus diperhatikan**

- Memanggil kelas Service KF-20 milik Renaya dengan parameter pengguna kosong.
- Tidak menulis satu baris pun ke basis data. Hasil hanya hidup di sesi.
- Karena terbuka untuk umum, wajib ada pembatasan laju agar tidak dipakai menguras basis data.
- Jika pengunjung kemudian mendaftar, hasil di sesi ditawarkan untuk disimpan sebagai catatan pertamanya.

**Rute dan Endpoint**

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/kalkulator` | `kalkulator.index` | Halaman kalkulator | publik |
| `POST` | `/kalkulator/hitung` | `kalkulator.hitung` | Hitung (kirim biasa, tanpa JavaScript) | publik, batas 30/menit |
| `POST` | `/api/kalkulator` | `kalkulator.api` | Hitung (JSON) | publik, batas 30/menit |

**API**

`POST /api/kalkulator`

Versi JSON dari kalkulator publik. Tidak memerlukan akun dan tidak menulis ke basis data.
Dibatasi 30 permintaan per menit per alamat IP.

```jsonc
// kirim
{ "emission_factor_id": 7, "amount": 4 }

// terima
{
  "emisi": 2.72,
  "satuan": "kg CO2e",
  "rincian": "4 kWh x 0,681 kg CO2e/kWh",
  "perbandingan": "Setara 22,7 km berkendara motor"
}
```

**Tampilan dan Pengalaman Pengguna**

*Layar yang dibuat*

- `/kalkulator` — halaman publik, dapat dibuka tanpa akun

*Komponen antarmuka*

- Formulir satu kolom di tengah, lebar maksimal 560px
- Empat kartu kategori berikon sebagai langkah pertama
- Hasil muncul di bawah formulir, bukan di halaman baru
- Kartu hasil besar: angka kg CO2e, satu kalimat perbandingan, dan ajakan daftar
- Tombol "Hitung Lagi" untuk mengulang tanpa memuat ulang halaman

*Keadaan layar yang wajib dirancang*

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Sebelum menghitung, tempat hasil diisi contoh samar: "Hasilmu akan muncul di sini." |
| **Memuat** | Tombol hitung berubah jadi "Menghitung..." selama kurang dari satu detik. |
| **Galat** | Melebihi batas laju: "Terlalu banyak percobaan. Tunggu satu menit." Jumlah kosong ditandai di kolomnya. |
| **Berhasil** | Kartu hasil muncul dengan animasi naik singkat, halaman menggulir sendiri ke hasilnya. |

*Perilaku responsif*

- 1440px: formulir di tengah dengan latar pola
- 768px: sama, pola disederhanakan
- 375px: kartu kategori dua kolom, tombol hitung menempel di bawah layar

*Aksesibilitas*

- Hasil memakai aria-live supaya langsung dibacakan tanpa perlu dicari
- Halaman tetap berguna tanpa JavaScript: formulir dikirim biasa dan hasilnya muncul di halaman berikutnya

*Komponen dan kelas CSS yang dipakai*

`<x-kartu>`, `.ct-pilihan-kartu`, `<x-isian>`, `.ct-kartu-hasil`, `<x-tombol varian="utama">`

**Kasus Batas**

- Pengunjung mematikan JavaScript: formulir tetap bisa dikirim biasa dan hasilnya muncul di halaman berikutnya.
- Permintaan ke-31 dalam satu menit: ditolak dengan pesan yang ramah, bukan galat 429 mentah.
- Pengunjung menghitung lalu mendaftar: hasil di sesi ditawarkan untuk disimpan sebagai catatan pertamanya.
- Belum ada faktor emisi sama sekali: halaman menampilkan pesan bahwa kalkulator belum siap, bukan menu pilihan kosong.

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Controller | `app/Http/Controllers/KalkulatorPublik221.php` |
| Form Request | `app/Http/Requests/HitungKalkulator221.php` |
| View | `resources/views/publik/kalkulator-221.blade.php` |
| Feature Test | `tests/Feature/KalkulatorPublikTest221.php` |

**Kriteria selesai**

- [ ] Pengunjung tanpa akun mendapat angka yang benar
- [ ] Tidak ada baris baru di basis data setelah menghitung
- [ ] Permintaan ke-31 dalam satu menit ditolak
- [ ] Angkanya sama persis dengan hasil pencatatan untuk masukan yang sama

---

## 8. KF-25 — Pengisian Kuis Awal dan Garis Dasar

**Pemilik** Hadi Dwi Pranoto (102022400221) · **Akhiran** `221` · **Kerumitan** Menengah · **Endpoint** `/kuis-awal`

**Status dalam penilaian**

Tidak dihitung sebagai PBI karena merupakan pemakaian dari PBI-06, bukan pengelolaan data
baru.

**Kenapa tingkat kerumitannya segitu**

Alur bertahap dengan penyimpanan jawaban sementara, lalu satu perhitungan garis dasar di
akhir. Kerumitannya pada menjaga alur tidak putus di tengah.

**Tabel yang dimiliki**

- `onboarding_results` — id, user_id, quiz_question_id, answer, baseline_emission

**Tabel yang hanya dibaca**

- `quiz_questions`, `users`

**Cakupan kerja**

| Bagian | Yang dilakukan |
|---|---|
| **Mulai** | Dijalankan sekali setelah pendaftaran, dapat dilewati dan diulang nanti. |
| **Jawab** | Satu pertanyaan per layar, jawaban disimpan setiap kali berpindah. |
| **Hasil** | Menghitung garis dasar emisi dari bobot jawaban, lalu menampilkannya sebagai angka awal pengguna. |

**Aturan dan batasan**

- Jawaban wajib dipilih sebelum lanjut
- Satu pengguna hanya punya satu baris jawaban per pertanyaan
- Kuis hanya dapat diisi pengguna yang sudah masuk

**Logika yang harus diperhatikan**

- Jawaban ditulis dengan updateOrCreate agar mengulang kuis tidak menimbulkan baris ganda.
- baseline_emission disimpan di baris jawaban terakhir dan menjadi angka pembanding awal pada dashboard.
- Pengguna yang melewati kuis tetap bisa memakai aplikasi; garis dasarnya kosong sampai ia mengisinya.
- Pertanyaan yang dinonaktifkan admin tidak muncul, tetapi jawaban lama tetap tersimpan.

**Rute dan Endpoint**

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/kuis-awal` | `kuis.mulai` | Layar pertanyaan | auth |
| `POST` | `/kuis-awal/jawab` | `kuis.jawab` | Simpan satu jawaban | auth |
| `GET` | `/kuis-awal/hasil` | `kuis.hasil` | Layar garis dasar | auth |
| `POST` | `/kuis-awal/lewati` | `kuis.lewati` | Lewati kuis | auth |

**Tampilan dan Pengalaman Pengguna**

*Layar yang dibuat*

- `/kuis-awal` — alur bertahap, satu pertanyaan per layar

*Komponen antarmuka*

- Batang kemajuan di puncak: "Pertanyaan 3 dari 6"
- Pilihan jawaban berbentuk kartu besar yang mudah ditekan, bukan radio kecil
- Tombol "Lewati dulu" selalu tersedia di pojok, tidak disembunyikan
- Layar hasil: angka garis dasar, penjelasan artinya, dan tombol menuju dashboard

*Keadaan layar yang wajib dirancang*

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Jika admin belum mengisi bank pertanyaan: "Kuis belum tersedia. Kamu bisa langsung mulai mencatat." dengan tombol ke dashboard. |
| **Memuat** | Perpindahan antarpertanyaan memakai animasi geser singkat, bukan layar putih. |
| **Galat** | Lanjut tanpa memilih: tombol lanjut tetap nonaktif dan diberi penjelasan "Pilih satu jawaban dulu." |
| **Berhasil** | Layar hasil menampilkan angka garis dasar dengan animasi menghitung naik dari nol. |

*Perilaku responsif*

- 1440px: kartu pertanyaan di tengah, lebar 640px
- 768px: lebar 90 persen
- 375px: kartu jawaban menumpuk, tombol lanjut menempel di bawah layar

*Aksesibilitas*

- Batang kemajuan memakai aria-valuenow dan punya padanan teks
- Fokus berpindah ke judul pertanyaan baru setiap kali berganti layar
- Alur dapat diselesaikan sepenuhnya dengan papan ketik

*Komponen dan kelas CSS yang dipakai*

`.ct-kemajuan`, `.ct-pilihan-kartu`, `<x-tombol varian="hantu">`, `.ct-angka-besar`

**Kasus Batas**

- Pengguna menutup peramban di tengah kuis: jawaban yang sudah masuk tetap tersimpan, kuis dilanjutkan dari pertanyaan berikutnya.
- Admin menonaktifkan pertanyaan di tengah pengisian: pertanyaan itu dilewati, jawaban yang sudah masuk tidak dihapus.
- Pengguna mengulang kuis: jawaban lama diperbarui, bukan ditambah baris baru.
- Pengguna melewati kuis: garis dasarnya kosong, dashboard tidak boleh menampilkan pembanding yang menyesatkan.

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Migration | `database/migrations/2026_01_01_0000_create_onboarding_results_table_221.php` |
| Model | `app/Models/OnboardingResult221.php` |
| Controller | `app/Http/Controllers/KuisAwal221.php` |
| Service | `app/Services/HitungGarisDasar221.php` |
| View | `resources/views/kuis/langkah-221.blade.php` |
| Feature Test | `tests/Feature/KuisAwalTest221.php` |

**Kriteria selesai**

- [ ] Mengulang kuis tidak menghasilkan baris ganda
- [ ] Melewati kuis tidak membuat aplikasi galat
- [ ] Garis dasar muncul di dashboard setelah kuis selesai
- [ ] Menonaktifkan satu pertanyaan tidak mengubah hasil yang sudah terhitung

---

## 9. KF-27 — Statistik Dampak Komunitas

**Pemilik** Tio Maria Anggita Sinaga (102022400365) · **Akhiran** `365` · **Kerumitan** Rendah · **Endpoint** `/komunitas`

**Status dalam penilaian**

Tidak dihitung sebagai PBI karena hanya menjumlahkan data seluruh pengguna dan bersifat baca
saja.

**Kenapa tingkat kerumitannya segitu**

Paling ringan di antara sembilan fitur ini. Hanya beberapa kueri agregasi tanpa formulir dan
tanpa tabel sendiri.

**Tabel yang dimiliki**

- Tidak ada. Fitur ini hanya membaca data milik PBI lain.

**Tabel yang hanya dibaca**

- `activities`, `users`, `challenge_user`, `point_logs`

**Cakupan kerja**

| Bagian | Yang dilakukan |
|---|---|
| **Angka ringkas** | Total penurunan emisi seluruh pengguna, jumlah pengguna aktif, dan jumlah tantangan yang diselesaikan. |
| **Perbandingan** | Menerjemahkan total penurunan menjadi padanan yang mudah dibayangkan, misalnya setara sekian pohon. |
| **Tampil di beranda** | Empat angka ini juga dipakai bagian Angka pada halaman muka publik. |

**Aturan dan batasan**

- Tidak ada masukan dari pengguna
- Angka nol ditampilkan apa adanya, bukan disembunyikan

**Logika yang harus diperhatikan**

- Seluruhnya kueri agregasi. Simpan hasilnya di cache selama satu jam.
- Data pengguna nonaktif tidak ikut dijumlahkan.
- Tidak menampilkan nama atau data pribadi siapa pun, hanya angka gabungan.
- Jika belum ada data sama sekali, tampilkan nol, bukan halaman kosong.

**Rute dan Endpoint**

| Metode | URI | Nama Rute | Aksi | Akses |
|---|---|---|---|---|
| `GET` | `/komunitas` | `komunitas.index` | Halaman statistik | publik |
| `GET` | `/api/komunitas/statistik` | `komunitas.data` | Angka gabungan (JSON) | publik |

**API**

`GET /api/komunitas/statistik`

Angka gabungan seluruh pengguna. Tidak memuat nama atau data pribadi siapa pun. Hasilnya
disimpan di cache selama satu jam.

```jsonc
{
  "total_penurunan": 4821.60,
  "pengguna_aktif": 128,
  "tantangan_selesai": 64,
  "perbandingan": "Setara 1.240 pohon menyerap karbon selama setahun",
  "diperbarui": "2026-10-26T08:00:00+07:00"
}
```

**Tampilan dan Pengalaman Pengguna**

*Layar yang dibuat*

- `/komunitas` — halaman publik
- Empat angka yang sama dipakai bagian Angka di halaman muka

*Komponen antarmuka*

- Empat angka besar bersisian dengan keterangan kecil di bawahnya
- Satu kalimat perbandingan: "Setara dengan 1.240 pohon menyerap karbon selama setahun."
- Catatan kecil: "Data gabungan seluruh pengguna, tanpa nama siapa pun."

*Keadaan layar yang wajib dirancang*

| Keadaan | Perilaku dan teks yang dilihat pengguna |
|---|---|
| **Kosong** | Menampilkan angka nol apa adanya disertai "Angka akan tumbuh seiring pengguna mencatat." Jangan menyembunyikan bagian ini. |
| **Memuat** | Angka menampilkan kerangka selebar perkiraan angkanya. |
| **Galat** | Menampilkan angka terakhir dari cache beserta catatan kecil "Data per 2 jam lalu." |
| **Berhasil** | Angka beranimasi menghitung naik saat bagian ini masuk layar. |

*Perilaku responsif*

- 1440px: empat kolom
- 768px: dua baris dua kolom
- 375px: dua baris dua kolom dengan angka lebih kecil

*Aksesibilitas*

- Animasi menghitung naik dimatikan jika pengguna memilih kurangi gerak
- Angka ditulis lengkap di markup, animasi hanya lapisan tampilan, supaya pembaca layar langsung mendapat angka akhir

*Komponen dan kelas CSS yang dipakai*

`.ct-angka-besar`, `<x-kartu>`, `.ct-catatan`, `<x-kerangka jenis="angka">`

**Kasus Batas**

- Basis data masih kosong: seluruh angka nol dan halaman tetap tampil utuh.
- Cache satu jam membuat angka tertinggal: beri catatan kecil kapan terakhir diperbarui.
- Satu pengguna menyumbang hampir seluruh penurunan: tetap ditampilkan sebagai angka gabungan, tanpa menyebut siapa pun.

**Berkas yang dibuat**

| Jenis | Berkas |
|---|---|
| Controller | `app/Http/Controllers/StatistikKomunitas365.php` |
| Service | `app/Services/RingkasanKomunitas365.php` |
| View | `resources/views/komunitas/index-365.blade.php` |
| Feature Test | `tests/Feature/StatistikKomunitasTest365.php` |

**Kriteria selesai**

- [ ] Angka cocok dengan penjumlahan manual pada data uji
- [ ] Halaman tetap terbuka saat basis data masih kosong
- [ ] Tidak ada nama pengguna yang bocor di halaman ini

---

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

## Urutan pengerjaan

| Sprint | Minggu | Fitur |
|---|---|---|
| Sprint 1 | Week 7–10 | KF-01, KF-04, KF-20, KF-25 |
| Sprint 2 | Week 11–15 | KF-21, KF-22, KF-23, KF-24, KF-27 |

KF-01 dan KF-04 wajib selesai paling awal: tanpa keduanya, tidak ada anggota lain yang bisa
menguji apa pun. KF-20 menyusul segera setelah modul Data Master menyediakan faktor emisi,
karena pencatatan aktivitas menunggu mesin hitung ini. Lima fitur sisanya bersifat tampilan
dan baru masuk akal dikerjakan setelah ada data nyata untuk ditampilkan.

---

## Pemeriksaan terakhir sebelum digabungkan

- [ ] Seluruh kriteria selesai pada setiap fitur sudah tercentang
- [ ] Nama berkas berakhiran NIM pemiliknya dan nama kelas cocok
- [ ] Tidak ada rumus emisi yang ditulis di luar kelas Service KF-20
- [ ] Seluruh rute yang perlu akun berada di balik middleware auth
- [ ] Seluruh rute panel admin berada di balik middleware peran
- [ ] Tidak ada data pribadi yang bocor pada halaman komunitas
- [ ] Sudah diuji Renaya selaku Quality Assurance
- [ ] Sudah ditinjau Alvin selaku pemegang cabang utama

