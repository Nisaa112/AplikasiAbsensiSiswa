<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas'; 

    protected $fillable = [
        'tingkat',
        'jurusan',
        'nomor_kelas',
        'tahun_ajaran_id',
        'wali_kelas_id',
    ];

    // Agar Flutter tetap menerima JSON 'nama_kelas'
    protected $appends = ['nama_kelas'];

    public function getNamaKelasAttribute()
    {
        $romawi = [
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];

        // Jika tingkat ada di array $romawi, ubah. Jika tidak, pakai angka aslinya.
        $tingkatStr = $romawi[$this->tingkat] ?? $this->tingkat;

        return "{$tingkatStr} {$this->jurusan} {$this->nomor_kelas}";
    }

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }
}