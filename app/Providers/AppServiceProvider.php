<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // SOAL 3: aturan pengelolaan buku dipusatkan di satu tempat.
        // Dipakai oleh middleware can:kelola-buku pada route dan @can('kelola-buku') pada tampilan.
        Gate::define('kelola-buku', fn (User $user): bool => $user->hasAnyRole(['admin', 'petugas']));
    }
}
