<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class AnswerSession extends Model
{
    protected $fillable = ['tenant_id', 'user_id', 'modul_id', 'started_at',
        'submitted_at',
        'ended_at'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }

    public function modul() {
        return $this->belongsTo(Modul::class);
    }

    public function answers() {
        return $this->hasMany(QuestionAnswer::class, 'session_id');
    }

    protected $casts = [
    'started_at' => 'datetime',
    'submitted_at' => 'datetime',
    'ended_at' => 'datetime',
];

}
