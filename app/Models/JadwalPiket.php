<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPiket extends Model {
    protected $table = 'jadwal_piket';
    protected $fillable = ['guru_id', 'tanggal'];

    public function guru() {
        return $this->belongsTo(Guru::class, 'guru_id');
    }
}