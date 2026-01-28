<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperSiswa
 */
class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';
    protected $fillable = [
        'user_id',
        'nisn',
        'nama_siswa',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function anggotaKelas()
    {
        return $this->hasMany(AnggotaKelas::class, 'siswa_id');
    }
}