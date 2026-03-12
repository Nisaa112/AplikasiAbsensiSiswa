<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerizinanGuru extends Model
{
    use HasFactory;

    protected $table = 'perizinan_guru';

    protected $fillable = [
        'guru_id',
        'tgl_izin',
        'jenis_izin',
        'alasan',
        'bukti_gambar',
        'status_izin',
        'admin_id',
        'kepsek_id'
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function admin() { return $this->belongsTo(User::class, 'admin_id'); }
    public function kepsek() { return $this->belongsTo(User::class, 'kepsek_id'); }
}