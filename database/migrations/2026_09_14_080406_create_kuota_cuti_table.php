<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuota_cuti', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pengguna_id');
            $table->unsignedInteger('jenis_cuti_id');
            $table->year('tahun');
            $table->integer('kuota_total');
            $table->integer('kuota_terpakai')->default(0);
            $table->integer('kuota_sisa');
            $table->timestamps();

            // Foreign key
            $table->foreign('pengguna_id')
                  ->references('id')->on('pengguna')
                  ->cascadeOnDelete();

            $table->foreign('jenis_cuti_id')
                  ->references('id')->on('jenis_cuti')
                  ->cascadeOnDelete();

            // Unique per pengguna + jenis cuti + tahun
            $table->unique(['pengguna_id', 'jenis_cuti_id', 'tahun'], 'kuota_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuota_cuti');
    }
};