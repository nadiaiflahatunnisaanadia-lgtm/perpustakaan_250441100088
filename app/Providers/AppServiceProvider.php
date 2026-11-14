<?php

namespace App\Providers;

use App\Models\Peminjaman;
use App\Models\User;
use App\Policies\PeminjamanPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        
    }

    
    public function boot(): void
    {
        // Di Vercel koneksi HTTPS berakhir di proxy; paksa URL production memakai https.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::define('kelola-buku', fn (User $user): bool => $user->hasAnyRole(['admin', 'petugas']));

       
        Gate::policy(Peminjaman::class, PeminjamanPolicy::class);
    }
}
