# Modul 7 — Deployment ke Vercel (Sistem Perpustakaan Digital)

Environment Variables yang didaftarkan di Vercel (Settings > Environment Variables).
Nilai rahasia TIDAK ditulis di sini.

| Variabel | Nilai |
|---|---|
| APP_NAME | Lentera Aksara |
| APP_ENV | production |
| APP_KEY | (rahasia, dari `php artisan key:generate --show`) |
| APP_DEBUG | false |
| APP_URL | https://<project-anda>.vercel.app |
| DB_CONNECTION | pgsql |
| DB_HOST | (host Transaction pooler Supabase) |
| DB_PORT | 6543 |
| DB_DATABASE | postgres |
| DB_USERNAME | (rahasia, format postgres.<project-ref>) |
| DB_PASSWORD | (rahasia) |
| SESSION_DRIVER | database |
| DB_SSLMODE | require |
