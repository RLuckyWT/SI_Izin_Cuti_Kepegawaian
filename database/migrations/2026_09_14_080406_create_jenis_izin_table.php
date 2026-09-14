<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_izin', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama_jenis', 50);
            $table->integer('batas_maksimal_per_bulan');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_izin');
    }
};