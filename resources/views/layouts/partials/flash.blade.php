@if (session('sukses') || session('status'))
    <div class="pesan pesan-sukses" role="status">{{ session('sukses') ?? session('status') }}</div>
@endif
@if (session('gagal'))
    <div class="pesan pesan-gagal" role="alert">{{ session('gagal') }}</div>
@endif
