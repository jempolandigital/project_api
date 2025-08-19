<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'question_type',
        'question',
        'desc',
        'min_proofs_required',
    ];

    protected $appends = ['question', 'type','desc'];

    public function getTextAttribute()
    {
        return $this->question; // ini akan muncul sebagai 'text'
    }

    public function getTypeAttribute()
    {
        return $this->question_type;
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }
}
