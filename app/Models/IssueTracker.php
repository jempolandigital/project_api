<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueTracker extends Model
{
    protected $table = 'issue_trackers';

    protected $fillable = [
        'question_id',
        'title',
        'description',
        'notes',
        'status',
        'user_id',
        'assigned_by',
        'reminder_at',
        'deadline',
        'answer_id',
        'priority',
        'tenant_id',
        'answer_reason',
        'session_id',
        'created_at',
        'updated_at'
    ];

    // semua history (baru -> lama)
    public function histories()
    {
        return $this->hasMany(IssueHistory::class, 'issue_tracker_id', 'id')->orderBy('created_at', 'desc');
    }

    // relasi untuk ambil history terakhir per issue (efisien untuk list)
    public function lastHistory()
    {
        // latestOfMany membutuhkan Laravel 8.41+/9+, jika versi lama ganti mekanisme
        return $this->hasOne(IssueHistory::class, 'issue_tracker_id', 'id')->latestOfMany();
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
