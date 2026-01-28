<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperAbsensi
 */
class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';
    protected $fillable = [
        'sesi_id',
        'siswa_id',
        'waktu_scan',
        'status',
        'lat_siswa',
        'long_siswa',
        'is_valid',
    ];

    public function sesi()
    {
        return $this->belongsTo(SesiPresensi::class, 'sesi_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}