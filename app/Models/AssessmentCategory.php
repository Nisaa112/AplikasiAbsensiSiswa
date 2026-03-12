<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentCategory extends Model
{
    protected $table = 'assessment_categories';

    protected $fillable = [
        'name', 
        'description', 
        'type', 
        'is_active'
    ];

    // Relasi ke detail penilaian
    public function questions()
    {
        return $this->hasMany(AssessmentQuestion::class, 'category_id');
    }
}