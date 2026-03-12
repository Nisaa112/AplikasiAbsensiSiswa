<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('sesi_presensi');
            $table->foreignId('siswa_id')->constrained('siswa');
            $table->timestamp('waktu_scan')->nullable();
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpa']);
            $table->double('lat_siswa')->nullable();
            $table->double('long_siswa')->nullable();
            $table->boolean('is_valid')->default(false);
            $table->timestamps();

            $table->unique(['siswa_id', 'sesi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
