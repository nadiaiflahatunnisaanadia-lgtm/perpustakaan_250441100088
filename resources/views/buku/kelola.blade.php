@extends('layouts.app')

@section('title', 'Kelola Buku — Lentera Aksara')

@section('content')
    <style>
        [hidden] { display: none !important; }

        .panel {
            margin-bottom: 32px;
            padding: 24px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius);
        }

        .panel h2 {
            margin: 0 0 16px;
            font-family: var(--font-display);
            font-size: 22px;
            color: var(--ink);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .form-grid label {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
        }

        .form-grid input,
        .form-grid select {
            padding: 10px 12px;
            font: inherit;
            font-weight: 400;
            color: var(--ink);
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 6px;
        }

        .form-aksi {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .btn-batal {
            background: var(--paper-soft);
            color: var(--ink);
        }

        .btn-batal:hover {
            background: var(--line);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .pesan {
            margin-bottom: 24px;
            padding: 14px 18px;
            border-radius: 6px;
            font-size: 14.5px;
            line-height: 1.5;
        }

        .pesan-sukses {
            background: #e7f1e1;
            border: 1px solid #a3c592;
            color: #2f5a1d;
        }

        .pesan-gagal {
            background: #f9e5e1;
            border: 1px solid #dba296;
            color: #7a2a1d;
        }

        .tabel-wrap {
            overflow-x: auto;
        }

        .tabel-buku {
            width: 100%;
            border-collapse: collapse;
        }

        .tabel-buku th,
        .tabel-buku td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid var(--line);
        }

        .tabel-buku th {
            font-size: 12px;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--muted);
        }

        .tabel-buku .kosong {
            text-align: center;
            color: var(--muted);
        }

        .btn-kecil {
            padding: 6px 14px;
            font-size: 13px;
        }

        .btn-hapus {
            background: #a63d2f;
            color: #fff;
        }

        .btn-hapus:hover {
            background: #8a3125;
        }

        .halaman {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 20px;
            color: var(--muted);
            font-size: 14px;
        }

        @verbatim
        @media (max-width: 960px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        @endverbatim
    </style>

    <div class="container page">

        <div class="page-heading">
            <div class="page-heading-text">
                <span class="eyebrow">Pengelolaan</span>
                <h1>Kelola data buku.</h1>
                <p>
                    Tambah, ubah, dan hapus data buku. Halaman ini hanya bisa dibuka
                    admin dan petugas (Gate <em>kelola-buku</em>).
                </p>
            </div>
        </div>

        {{-- Formulir tambah / ubah buku --}}
        <section class="panel">
            <h2 id="judul-form">Tambah Buku</h2>

            <form id="form-buku" novalidate>
                <div class="form-grid">
                    <label>
                        Kategori
                        <select name="kategori_id">
                            <option value="">Pilih kategori</option>
                            @foreach ($kategori as $item)
                                <option value="{{ $item->id }}">{{ $item->nama }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        Judul
                        <input type="text" name="judul" autocomplete="off">
                    </label>

                    <label>
                        Penulis
                        <input type="text" name="penulis" autocomplete="off">
                    </label>

                    <label>
                        Tahun Terbit
                        <input type="number" name="tahun_terbit">
                    </label>
                </div>

                <div class="form-aksi">
                    <button type="submit" class="btn" id="tombol-simpan">Tambah</button>
                    <button type="button" class="btn btn-batal" id="tombol-batal" hidden>Batal</button>
                </div>
            </form>
        </section>

        @include('layouts.partials.flash')

        {{-- Pesan berhasil / gagal --}}
        <div id="pesan" role="alert" hidden></div>

        {{-- SOAL 2 (c): penghapusan lewat form DELETE dengan token CSRF (action diisi saat tombol Hapus diklik) --}}
        <form id="form-hapus" method="POST" action="{{ url('/buku') }}" hidden>
            @csrf
            @method('DELETE')
        </form>

        {{-- Tabel buku (diisi lewat Fetch API) --}}
        <section class="panel">
            <div class="tabel-wrap">
                <table class="tabel-buku">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Tahun Terbit</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="isi-tabel">
                        <tr>
                            <td colspan="5" class="kosong">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="halaman">
                <button type="button" class="btn btn-kecil btn-batal" id="tombol-sebelumnya">Sebelumnya</button>
                <span id="info-halaman"></span>
                <button type="button" class="btn btn-kecil btn-batal" id="tombol-berikutnya">Berikutnya</button>
            </div>
        </section>

    </div>

    <script>
        const API_URL = @json(url('/api/buku'));
        const BUKU_URL = @json(url('/buku'));
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

        const form = document.getElementById('form-buku');
        const formHapus = document.getElementById('form-hapus');
        const judulForm = document.getElementById('judul-form');
        const tombolSimpan = document.getElementById('tombol-simpan');
        const tombolBatal = document.getElementById('tombol-batal');
        const isiTabel = document.getElementById('isi-tabel');
        const kotakPesan = document.getElementById('pesan');
        const infoHalaman = document.getElementById('info-halaman');
        const tombolSebelumnya = document.getElementById('tombol-sebelumnya');
        const tombolBerikutnya = document.getElementById('tombol-berikutnya');

        let dataBuku = [];      // data buku yang sedang tampil di tabel
        let halaman = 1;        // halaman yang sedang tampil
        let idEdit = null;      // null = mode tambah, angka = id buku yang sedang diubah




        function tampilPesan(teks, jenis) {
            kotakPesan.textContent = teks;
            kotakPesan.className = 'pesan pesan-' + jenis;
            kotakPesan.hidden = false;
        }

        function sembunyikanPesan() {
            kotakPesan.hidden = true;
        }

        // Menyusun pesan kesalahan dari respons API yang gagal (status bukan 2xx)
        async function pesanDariRespons(response, aksi) {
            let hasil = {};

            try {
                hasil = await response.json();
            } catch (e) {
                // isi respons bukan JSON, cukup pakai status
            }

            if (response.status === 422 && hasil.errors) {
                return 'Data tidak valid. ' + Object.values(hasil.errors).flat().join(' ');
            }

            if (response.status === 401) {
                return 'Sesi login berakhir. Silakan masuk kembali.';
            }

            if (response.status === 403) {
                return 'Gagal ' + aksi + ': kamu tidak punya hak akses untuk tindakan ini.';
            }

            if (response.status === 419) {
                return 'Gagal ' + aksi + ': sesi halaman kedaluwarsa. Muat ulang halaman lalu coba lagi.';
            }

            if (response.status === 404) {
                return 'Gagal ' + aksi + ': data buku tidak ditemukan.';
            }

            return 'Gagal ' + aksi + ' (status ' + response.status + ').';
        }

        // Menyusun pesan dari error yang tertangkap di catch
        function pesanDariError(error, aksi) {
            if (error instanceof TypeError) {
                return 'Gagal ' + aksi + ': API tidak dapat diakses. Pastikan server berjalan.';
            }

            return error.message;
        }


        async function ambilBuku(page = 1) {
            try {
                const response = await fetch(API_URL + '?page=' + page, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error(await pesanDariRespons(response, 'mengambil data'));
                }

                const hasil = await response.json();

                if (hasil.data.length === 0 && page > 1 && hasil.meta.last_page < page) {
                    return await ambilBuku(hasil.meta.last_page);
                }

                halaman = hasil.meta.current_page;
                dataBuku = hasil.data;

                tampilkanTabel(hasil.data);
                tampilkanHalaman(hasil.meta);
            } catch (error) {
                tampilkanTabelGagal();
                tampilPesan(pesanDariError(error, 'mengambil data'), 'gagal');
            }
        }

        function barisKosong(teks) {
            const baris = document.createElement('tr');
            const sel = document.createElement('td');

            sel.colSpan = 5;
            sel.className = 'kosong';
            sel.textContent = teks;
            baris.append(sel);

            return baris;
        }

        function tampilkanTabel(daftar) {
            isiTabel.replaceChildren();

            if (daftar.length === 0) {
                isiTabel.append(barisKosong('Belum ada data buku.'));
                return;
            }

            for (const buku of daftar) {
                const baris = document.createElement('tr');

                // atribut di respons API Resource: kategori, judul, penulis, tahun
                for (const isi of [buku.kategori, buku.judul, buku.penulis, buku.tahun]) {
                    const sel = document.createElement('td');
                    sel.textContent = isi ?? '-';
                    baris.append(sel);
                }

                const aksi = document.createElement('td');

                const tombolUbah = document.createElement('button');
                tombolUbah.type = 'button';
                tombolUbah.className = 'btn btn-kecil';
                tombolUbah.textContent = 'Ubah';
                tombolUbah.dataset.aksi = 'ubah';
                tombolUbah.dataset.id = buku.id;

                const tombolHapus = document.createElement('button');
                tombolHapus.type = 'button';
                tombolHapus.className = 'btn btn-kecil btn-hapus';
                tombolHapus.textContent = 'Hapus';
                tombolHapus.dataset.aksi = 'hapus';
                tombolHapus.dataset.id = buku.id;

                aksi.append(tombolUbah, ' ', tombolHapus);
                baris.append(aksi);

                isiTabel.append(baris);
            }
        }

        function tampilkanTabelGagal() {
            isiTabel.replaceChildren(barisKosong('Data buku tidak dapat dimuat.'));
            infoHalaman.textContent = '';
            tombolSebelumnya.disabled = true;
            tombolBerikutnya.disabled = true;
        }

        function tampilkanHalaman(meta) {
            infoHalaman.textContent = 'Halaman ' + meta.current_page + ' dari ' + meta.last_page + ' (' + meta.total + ' buku)';
            tombolSebelumnya.disabled = meta.current_page <= 1;
            tombolBerikutnya.disabled = meta.current_page >= meta.last_page;
        }


        async function tambahBuku(data) {
            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data),
                });

                if (!response.ok) {
                    throw new Error(await pesanDariRespons(response, 'menambah buku'));
                }

                tampilPesan('Buku berhasil ditambahkan.', 'sukses');
                await ambilBuku(1);   

                return true;
            } catch (error) {
                tampilPesan(pesanDariError(error, 'menambah buku'), 'gagal');
                return false;
            }
        }



        async function ubahBuku(id, data) {
            try {
                const response = await fetch(API_URL + '/' + id, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data),
                });

                if (!response.ok) {
                    throw new Error(await pesanDariRespons(response, 'mengubah buku'));
                }

                tampilPesan('Buku berhasil diperbarui.', 'sukses');
                await ambilBuku(halaman);

                return true;
            } catch (error) {
                tampilPesan(pesanDariError(error, 'mengubah buku'), 'gagal');
                return false;
            }
        }


        // Menghapus buku lewat form DELETE yang memuat @csrf dan @method('DELETE')
        function hapusBuku(id) {
            formHapus.action = BUKU_URL + '/' + id;
            formHapus.submit();
        }


        function ambilDataForm() {
            const kategori = form.elements['kategori_id'].value;
            const tahun = form.elements['tahun_terbit'].value;

            return {
                kategori_id: kategori === '' ? '' : Number(kategori),
                judul: form.elements['judul'].value,
                penulis: form.elements['penulis'].value,
                tahun_terbit: tahun === '' ? '' : Number(tahun),
            };
        }

        function modeTambah() {
            idEdit = null;
            form.reset();
            judulForm.textContent = 'Tambah Buku';
            tombolSimpan.textContent = 'Tambah';
            tombolBatal.hidden = true;
        }

        function modeUbah(buku) {
            idEdit = buku.id;
            form.elements['kategori_id'].value = buku.kategori_id;
            form.elements['judul'].value = buku.judul;
            form.elements['penulis'].value = buku.penulis;
            form.elements['tahun_terbit'].value = buku.tahun;   // atribut API "tahun" = kolom tahun_terbit
            judulForm.textContent = 'Ubah Buku';
            tombolSimpan.textContent = 'Simpan Perubahan';
            tombolBatal.hidden = false;
            form.elements['judul'].focus();
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            sembunyikanPesan();

            tombolSimpan.disabled = true;

            const data = ambilDataForm();
            const berhasil = idEdit === null
                ? await tambahBuku(data)
                : await ubahBuku(idEdit, data);

            tombolSimpan.disabled = false;

            // formulir dikosongkan hanya jika permintaan berhasil
            if (berhasil) {
                modeTambah();
            }
        });

        tombolBatal.addEventListener('click', () => {
            sembunyikanPesan();
            modeTambah();
        });

        isiTabel.addEventListener('click', async (event) => {
            const tombol = event.target.closest('button[data-aksi]');

            if (!tombol) {
                return;
            }

            const id = Number(tombol.dataset.id);
            const buku = dataBuku.find((item) => item.id === id);

            if (!buku) {
                return;
            }

            sembunyikanPesan();

            if (tombol.dataset.aksi === 'ubah') {
                modeUbah(buku);
                return;
            }

            // Soal 4: konfirmasi sebelum menghapus
            if (!confirm('Yakin ingin menghapus buku "' + buku.judul + '"?')) {
                return;
            }

            hapusBuku(id);
        });

        tombolSebelumnya.addEventListener('click', () => ambilBuku(halaman - 1));
        tombolBerikutnya.addEventListener('click', () => ambilBuku(halaman + 1));

        ambilBuku(1);
    </script>
@endsection
