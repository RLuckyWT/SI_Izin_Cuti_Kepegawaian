<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_izin', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pengguna_id');
            $table->unsignedInteger('jenis_izin_id');
            $table->date('tanggal');
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->text('alasan')->nullable();
            $table->string('file_lampiran', 255)->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])
                  ->default('menunggu');
            $table->unsignedInteger('disetujui_oleh')->nullable();
            $table->text('catatan_persetujuan')->nullable();
            $table->timestamps();

            $table->foreign('pengguna_id')
                  ->references('id')->on('pengguna')
                  ->cascadeOnDelete();

            $table->foreign('jenis_izin_id')
                  ->references('id')->on('jenis_izin');

            $table->foreign('disetujui_oleh')
                  ->references('id')->on('pengguna')
                  ->nullOnDelete();

            $table->index(['pengguna_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_izin');
    }
};