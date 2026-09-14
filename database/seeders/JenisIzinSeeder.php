<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisIzinSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('jenis_izin')->insert([
            [
                'nama_jenis' => 'Izin Sakit',
                'batas_maksimal_per_bulan' => 3,
                'keterangan' => 'Wajib surat dokter jika lebih dari 1 hari',
            ],
            [
                'nama_jenis' => 'Izin Keperluan Pribadi/Keluarga',
                'batas_maksimal_per_bulan' => 2,
                'keterangan' => 'Keperluan mendesak di luar cuti',
            ],
            [
                'nama_jenis' => 'Izin Terlambat/Pulang Cepat',
                'batas_maksimal_per_bulan' => 3,
                'keterangan' => 'Dihitung per jam',
            ],
        ]);
    }
}