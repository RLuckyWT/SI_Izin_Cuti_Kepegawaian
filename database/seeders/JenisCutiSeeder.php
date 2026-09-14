<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisCutiSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('jenis_cuti')->insert([
            [
                'nama_jenis' => 'Cuti Tahunan',
                'kuota_default_hari' => 12,
                'keterangan' => 'Reset setiap tahun',
            ],
            [
                'nama_jenis' => 'Cuti Melahirkan',
                'kuota_default_hari' => 90,
                'keterangan' => '1,5 bulan sebelum + 1,5 bulan sesudah melahirkan',
            ],
            [
                'nama_jenis' => 'Cuti Menikah',
                'kuota_default_hari' => 3,
                'keterangan' => 'Untuk pegawai yang menikah',
            ],
            [
                'nama_jenis' => 'Cuti Duka',
                'kuota_default_hari' => 2,
                'keterangan' => 'Keluarga inti meninggal dunia',
            ],
        ]);
    }
}