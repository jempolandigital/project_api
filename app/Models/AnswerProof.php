<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnswerProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_answer_id',
        'file_path',
        'file_type',
    ];

    public function answer()
    {
        return $this->belongsTo(QuestionAnswer::class, 'question_answer_id');
    }
}
