<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_cuti', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pengguna_id');
            $table->unsignedInteger('jenis_cuti_id');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('jumlah_hari');
            $table->text('alasan')->nullable();
            $table->string('file_lampiran', 255)->nullable();
            $table->enum('status', [
                'menunggu',
                'disetujui_hrd',
                'disetujui_pimpinan',
                'ditolak'
            ])->default('menunggu');
            $table->unsignedInteger('disetujui_oleh')->nullable();
            $table->text('catatan_persetujuan')->nullable();
            $table->timestamps();

            $table->foreign('pengguna_id')
                  ->references('id')->on('pengguna')
                  ->cascadeOnDelete();

            $table->foreign('jenis_cuti_id')
                  ->references('id')->on('jenis_cuti');

            $table->foreign('disetujui_oleh')
                  ->references('id')->on('pengguna')
                  ->nullOnDelete();

            $table->index(['pengguna_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_cuti');
    }
};