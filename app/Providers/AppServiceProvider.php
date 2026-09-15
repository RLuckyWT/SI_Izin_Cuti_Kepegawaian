<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Mapping tipe_pengajuan (cuti/izin) ke class model
        Relation::enforceMorphMap([
            'cuti' => \App\Models\PengajuanCuti::class,
            'izin' => \App\Models\PengajuanIzin::class,
        ]);
    }
}