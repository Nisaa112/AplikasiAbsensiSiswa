<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPerizinan
 */
class Perizinan extends Model
{
    use HasFactory;

    protected $table = 'perizinan';
    protected $fillable = [
        'siswa_id',
        'tgl_izin',
        'jenis_izin',
        'alasan',
        'bukti_gambar',
        'status_izin',
        'validator_id',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function validator()
    {
        return $this->belongsTo(Guru::class, 'validator_id');
    }
}