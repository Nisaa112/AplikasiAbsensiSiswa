<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $table = 'assessments';

    protected $fillable = [
        'evaluator_id', 
        'evaluatee_id', 
        'tahun_ajaran_id', 
        'general_notes'
    ];

    /**
     * Relasi: Guru yang menilai
     */
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    /**
     * Relasi: Siswa yang dinilai
     */
    public function evaluatee()
    {
        return $this->belongsTo(User::class, 'evaluatee_id');
    }
    
    /**
     * Relasi: Tahun Ajaran (Periode)
     */
    public function tahunAjaran() // Disarankan namanya tahunAjaran agar selaras dengan controller
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    /**
     * Relasi ke poin-poin nilai (Detail)
     */
    public function details()
    {
        return $this->hasMany(AssessmentDetail::class, 'assessment_id');
    }
}