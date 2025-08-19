<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'question_id',
        'question_type',
        'answer_option_id',
        'answer_text',
        'answer_reason',
        'started_at',
        'submitted_at',
        'is_correct',
        'session_id'
    ];

    public function proofs()
    {
        return $this->hasMany(AnswerProof::class);
    }

    public function session()
{
    return $this->belongsTo(AnswerSession::class, 'session_id');
}

public function question()
{
    return $this->belongsTo(Question::class);
}

public function option()
{
    return $this->belongsTo(QuestionOption::class, 'answer_option_id');
}

public function modul()
{
    return $this->belongsTo(Modul::class);
}



}
