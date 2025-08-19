<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueHistory extends Model
{
    protected $table = 'issue_histories';

    protected $fillable = [
        'id',
        'issue_tracker_id',
        'status',
        'changed_by',
        'assigned_to',
        'attachment_paths',
        'note',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // cast attachment_paths JSON <-> array otomatis
    protected $casts = [
        'attachment_paths' => 'array',
    ];

    public function issue()
    {
        return $this->belongsTo(IssueTracker::class, 'issue_tracker_id');
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
