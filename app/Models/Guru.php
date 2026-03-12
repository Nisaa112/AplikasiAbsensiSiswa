<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperGuru
 */
class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';
    protected $fillable = [
        'user_id',
        'nip',
        'nama_guru',
        'senioritas',
        'gender',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function waliKelas()
    {
        return $this->hasOne(Kelas::class, 'wali_kelas_id');
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'guru_id');
    }

    public function piketHarian() {
        return $this->hasMany(JadwalPiket::class, 'guru_id');
    }

    public function perizinan() {
        return $this->hasMany(PerizinanGuru::class, 'guru_id');
    }
}