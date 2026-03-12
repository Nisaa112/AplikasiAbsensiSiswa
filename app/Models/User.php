<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Assessments;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'serial_number',
        'password',
        'role',
        'device_id',
        'email'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function guru()
    {
        return $this->hasOne(Guru::class, 'user_id');
    }

    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'user_id');
    }

    // public function getAuthIdentifierName()
    // {
    //     return 'serial_number';
    // }

    /**
     * Relasi untuk mendapatkan semua penilaian yang diterima oleh user (sebagai siswa)
     */
    public function assessmentsReceived()
    {
        return $this->hasMany(Assessment::class, 'evaluatee_id');
    }

    /**
     * Relasi untuk mendapatkan semua penilaian yang diberikan oleh user (sebagai guru/penilai)
     */
    public function assessmentsGiven()
    {
        return $this->hasMany(Assessment::class, 'evaluator_id');
    }

    public function anggotaKelas()
    {
        return $this->hasManyThrough(
            \App\Models\AnggotaKelas::class, // Target akhir
            \App\Models\Siswa::class,        // Lewat model ini
            'user_id',                       // Foreign key di tabel siswa
            'siswa_id',                      // Foreign key di tabel anggota_kelas
            'id',                            // Local key di tabel users
            'id'                             // Local key di tabel siswa
        );
    }
}
