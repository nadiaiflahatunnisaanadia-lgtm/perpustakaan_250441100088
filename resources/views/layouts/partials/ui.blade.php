<style>
    [hidden] { display: none !important; }

    /* ---------- Navigasi & akun ---------- */
    .nav-wrap { flex-wrap: wrap; }
    .main-nav { flex-wrap: wrap; gap: 8px 22px; }
    .nav-user { display: flex; align-items: center; gap: 12px; color: #d8cbb3; font-size: 13.5px; }
    .nav-user .peran {
        padding: 2px 10px; border: 1px solid var(--gold); border-radius: 999px;
        color: var(--gold); font-size: 11.5px; letter-spacing: .5px; text-transform: uppercase;
    }
    .nav-user form { margin: 0; }
    .btn-garis {
        background: transparent; color: #f4ebdc; border: 1px solid #6c5d49;
        padding: 7px 16px; font-size: 13.5px;
    }
    .btn-garis:hover { background: rgba(255,255,255,.08); }

    /* ---------- Komponen umum ---------- */
    .panel {
        margin-bottom: 32px; padding: 24px; background: var(--card);
        border: 1px solid var(--line); border-radius: var(--radius);
    }
    .panel h2 { margin: 0 0 16px; font-family: var(--font-display); font-size: 22px; color: var(--ink); }
    .btn-kecil { padding: 6px 14px; font-size: 13px; }
    .btn-batal { background: var(--paper-soft); color: var(--ink); }
    .btn-batal:hover { background: var(--line); }
    .btn-hapus { background: #a63d2f; color: #fff; }
    .btn-hapus:hover { background: #8a3125; }
    .btn-ok { background: #3f7a3a; color: #fff; }
    .btn-ok:hover { background: #336430; }
    .btn:disabled { opacity: .6; cursor: not-allowed; }
    .inline-form { display: inline; margin: 0; }
    .aksi-baris { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }

    .pesan {
        margin-bottom: 24px; padding: 14px 18px; border-radius: 6px;
        font-size: 14.5px; line-height: 1.5;
    }
    .pesan-sukses { background: #e7f1e1; border: 1px solid #a3c592; color: #2f5a1d; }
    .pesan-gagal  { background: #f9e5e1; border: 1px solid #dba296; color: #7a2a1d; }

    .tabel-wrap { overflow-x: auto; }
    .tabel { width: 100%; border-collapse: collapse; }
    .tabel th, .tabel td { padding: 12px 14px; text-align: left; border-bottom: 1px solid var(--line); vertical-align: middle; }
    .tabel th { font-size: 12px; letter-spacing: 1px; text-transform: uppercase; color: var(--muted); }
    .tabel .kosong { text-align: center; color: var(--muted); }
    .tabel a { color: var(--soga); }

    .status {
        display: inline-block; padding: 3px 10px; border-radius: 999px;
        font-size: 12px; font-weight: 600; text-transform: capitalize;
    }
    .status-diajukan     { background: #f6ecd2; color: #7a5b17; }
    .status-dipinjam     { background: #dde8f1; color: #26455f; }
    .status-dikembalikan { background: #e7f1e1; color: #2f5a1d; }
    .status-ditolak      { background: #f9e5e1; color: #7a2a1d; }

    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 16px; margin-bottom: 32px; }
    .stat {
        padding: 20px; background: var(--card); border: 1px solid var(--line); border-radius: var(--radius);
    }
    .stat dt { margin: 0 0 6px; color: var(--muted); font-size: 13px; }
    .stat dd { margin: 0; font-family: var(--font-display); font-size: 34px; font-weight: 600; color: var(--soga); line-height: 1; }

    /* ---------- Formulir ---------- */
    .form-stack { display: grid; gap: 16px; max-width: 520px; }
    .form-stack.lebar { max-width: 720px; }
    .field { display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: var(--muted); }
    .field input, .field select {
        padding: 10px 12px; font: inherit; font-weight: 400; color: var(--ink);
        background: #fff; border: 1px solid var(--line); border-radius: 6px;
    }
    .field input:focus, .field select:focus { outline: 2px solid var(--gold); outline-offset: 1px; }
    .field .galat { color: #a63d2f; font-weight: 500; font-size: 13px; }
    .field small { font-weight: 400; color: var(--muted); }
    .cek { display: flex; align-items: center; gap: 8px; font-size: 14px; color: var(--muted); }

    .filter-bar { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; margin-bottom: 24px; }
    .filter-bar .field { min-width: 180px; }

    /* ---------- Halaman auth ---------- */
    .auth-wrap { display: flex; justify-content: center; padding: 56px 0 80px; }
    .auth-card {
        width: 100%; max-width: 460px; padding: 36px; background: var(--card);
        border: 1px solid var(--line); border-radius: var(--radius);
    }
    .auth-card h1 { margin: 0 0 6px; font-family: var(--font-display); font-size: 30px; }
    .auth-card > p { margin: 0 0 24px; color: var(--muted); line-height: 1.6; }
    .auth-card .btn { width: 100%; text-align: center; }
    .auth-alt { margin-top: 20px; font-size: 14px; color: var(--muted); text-align: center; }
    .auth-alt a { color: var(--soga); font-weight: 600; }

    .aksi-kartu { margin-top: 14px; }
    .aksi-kartu .btn { width: 100%; text-align: center; }

    .aksi-kelola { display: flex; gap: 8px; margin-top: 10px; }
    .aksi-kelola form { margin: 0; }

    .paginasi { margin-top: 20px; display: flex; gap: 8px; align-items: center; color: var(--muted); font-size: 14px; }
    .paginasi a, .paginasi span.cur {
        padding: 6px 12px; border: 1px solid var(--line); border-radius: 6px; text-decoration: none; color: var(--ink);
    }
    .paginasi span.cur { background: var(--gold); border-color: var(--gold); font-weight: 600; }

    @media (max-width: 640px) { .nav-user { width: 100%; justify-content: space-between; } }
</style>
