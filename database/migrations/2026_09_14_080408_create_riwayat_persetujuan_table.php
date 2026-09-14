<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_persetujuan', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('tipe_pengajuan', ['cuti', 'izin']);
            $table->unsignedInteger('pengajuan_id')
                  ->comment('ID di tabel pengajuan_cuti atau pengajuan_izin');
            $table->unsignedInteger('pengguna_id')
                  ->comment('HRD/Pimpinan yang melakukan aksi');
            $table->enum('aksi', ['disetujui', 'ditolak']);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('pengguna_id')
                  ->references('id')->on('pengguna')
                  ->cascadeOnDelete();

            $table->index(['tipe_pengajuan', 'pengajuan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_persetujuan');
    }
};