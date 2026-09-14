<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pengguna_id');
            $table->string('jabatan', 100)->nullable();
            $table->string('departemen', 100)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('no_telepon', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->date('tanggal_masuk_kerja')->nullable();
            $table->enum('status_kepegawaian', ['tetap', 'kontrak', 'magang'])
                  ->default('kontrak');
            $table->timestamps();

            $table->foreign('pengguna_id')
                  ->references('id')->on('pengguna')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};