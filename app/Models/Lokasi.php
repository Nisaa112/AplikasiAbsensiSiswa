<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperLokasi
 */
class Lokasi extends Model
{
    use HasFactory;

    protected $table = 'lokasi';
    protected $fillable = [
        'nama_lokasi',
        'latitude',
        'longitude',
        'radius',
    ];

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'lokasi_id');
    }
}