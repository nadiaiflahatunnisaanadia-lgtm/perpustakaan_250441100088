<div class="tabel-wrap">
    <table class="tabel">
        <thead>
            <tr><th>Anggota</th><th>Buku</th><th>Tanggal</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse ($daftar as $p)
                <tr>
                    <td>{{ $p->anggota->nama ?? '-' }}</td>
                    <td>{{ $p->buku->judul ?? '-' }}</td>
                    <td>{{ $p->tanggal_pinjam?->translatedFormat('d M Y') }}</td>
                    <td><span class="status status-{{ $p->status }}">{{ $p->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="kosong">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
