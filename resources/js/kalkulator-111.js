// Kalkulator publik (KF-24): saring aktivitas per kategori, hitung lewat /api/kalkulator tanpa
// memuat ulang halaman, dan tampilkan hasilnya. Tanpa JavaScript, formulir tetap dikirim biasa.

const form = document.querySelector('[data-kalkulator]');

if (form) {
    const pilih = form.querySelector('#emission_factor_id');
    const jumlah = form.querySelector('#amount');
    const satuan = form.querySelector('[data-satuan-jumlah]');
    const tombol = form.querySelector('[type="submit"]');
    const galatUmum = form.querySelector('[data-galat-umum]');
    const kotakKosong = document.querySelector('[data-hasil-kosong]');
    const kotakIsi = document.querySelector('[data-hasil-isi]');
    const teksTombol = tombol.textContent;
    const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Satuan di ujung kolom jumlah mengikuti aktivitas yang dipilih
    const perbaruiSatuan = () => {
        satuan.textContent = pilih.selectedOptions[0]?.dataset.satuan || 'satuan';
    };

    // Langkah 1 menyaring daftar aktivitas di langkah 2
    const saring = (idKategori) => {
        pilih.querySelectorAll('optgroup').forEach((grup) => {
            const cocok = grup.dataset.kategori === idKategori;
            grup.hidden = !cocok;
            grup.disabled = !cocok;
        });
        if (pilih.selectedOptions[0]?.parentElement.dataset.kategori !== idKategori) {
            pilih.value = pilih.querySelector(`optgroup[data-kategori="${idKategori}"] option`)?.value ?? '';
        }
        perbaruiSatuan();
    };

    form.querySelectorAll('[data-pilih-kategori]').forEach((radio) => {
        radio.addEventListener('change', () => saring(radio.value));
    });
    pilih.addEventListener('change', perbaruiSatuan);

    const terpilih = form.querySelector('[data-pilih-kategori]:checked');
    if (terpilih) saring(terpilih.value);
    else perbaruiSatuan();

    const tandaiGalat = (galat = {}) => {
        form.querySelectorAll('[data-galat-untuk]').forEach((p) => {
            const pesan = galat[p.dataset.galatUntuk]?.[0];
            const kolom = form.querySelector(`#${p.dataset.galatUntuk}`);
            p.textContent = pesan ?? '';
            p.hidden = !pesan;
            if (pesan) kolom.setAttribute('aria-invalid', 'true');
            else kolom.removeAttribute('aria-invalid');
        });
        form.querySelector('[aria-invalid="true"]')?.focus();
    };

    const tampilkan = (hasil) => {
        document.querySelector('[data-hasil-angka]').textContent = hasil.emisi_teks;
        document.querySelector('[data-hasil-banding]').textContent = hasil.perbandingan;
        document.querySelector('[data-hasil-rincian]').textContent = hasil.rincian;
        kotakKosong.hidden = true;
        kotakIsi.hidden = false;
        kotakIsi.classList.remove('ct-kartu-hasil--baru');
        void kotakIsi.offsetWidth; // ulang animasi naik setiap kali hasil baru muncul
        kotakIsi.classList.add('ct-kartu-hasil--baru');
        kotakIsi.scrollIntoView({ behavior: kurangiGerak ? 'auto' : 'smooth', block: 'center' });
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        galatUmum.hidden = true;
        tombol.disabled = true;
        tombol.setAttribute('aria-busy', 'true');
        tombol.textContent = tombol.dataset.teksMemuat;

        try {
            const jawaban = await fetch(form.dataset.api, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({ emission_factor_id: pilih.value, amount: jumlah.value }),
            });
            const data = await jawaban.json();

            if (jawaban.ok) {
                tandaiGalat();
                tampilkan(data);
            } else if (jawaban.status === 422) {
                tandaiGalat(data.errors);
            } else {
                galatUmum.textContent = data.pesan ?? 'Terjadi kesalahan. Coba lagi.';
                galatUmum.hidden = false;
            }
        } catch {
            galatUmum.textContent = 'Tidak bisa terhubung ke server. Periksa koneksimu lalu coba lagi.';
            galatUmum.hidden = false;
        } finally {
            tombol.disabled = false;
            tombol.removeAttribute('aria-busy');
            tombol.textContent = teksTombol;
        }
    });

    // Hitung Lagi: kosongkan jumlah dan hasil tanpa memuat ulang halaman
    document.querySelector('[data-hitung-lagi]')?.addEventListener('click', (e) => {
        e.preventDefault();
        jumlah.value = '';
        kotakIsi.hidden = true;
        kotakKosong.hidden = false;
        tandaiGalat();
        form.scrollIntoView({ behavior: kurangiGerak ? 'auto' : 'smooth', block: 'start' });
        jumlah.focus({ preventScroll: true });
    });

    // Setelah kiriman tanpa JavaScript gagal validasi, fokus ke kolom yang bermasalah
    form.querySelector('[aria-invalid="true"]')?.focus();
}
