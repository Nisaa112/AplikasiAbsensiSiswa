<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perizinan_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru');
            $table->date('tgl_izin');
            $table->enum('jenis_izin', ['sakit', 'izin', 'cuti', 'dinas_luar']);
            $table->text('alasan');
            $table->string('bukti_gambar'); 
            
            $table->enum('status_izin', [
                'pending',               
                'disetujui_admin', 
                'disetujui_kepsek',  
                'ditolak' 
            ])->default('pending');

            $table->foreignId('admin_id')->nullable()->constrained('users'); 
            $table->foreignId('kepsek_id')->nullable()->constrained('users');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perizinan_guru');
    }
};