<div class="tabel-wrap">
    <table class="tabel">
        <thead>
            <tr><th>Buku</th><th>Kategori</th><th>Tgl pinjam</th><th>Tgl kembali</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse ($daftar as $p)
                <tr>
                    <td>{{ $p->buku->judul ?? '-' }}</td>
                    <td>{{ $p->buku->kategori->nama ?? '-' }}</td>
                    <td>{{ $p->tanggal_pinjam?->translatedFormat('d M Y') }}</td>
                    <td>{{ $p->tanggal_kembali?->translatedFormat('d M Y') ?? '-' }}</td>
                    <td><span class="status status-{{ $p->status }}">{{ $p->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="kosong">{{ $kosong }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
