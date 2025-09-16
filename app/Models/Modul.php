<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Modul extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        // 'tenant_id',   // ✅ tambahin ini
        'duration',
        'open_at',
        'closed_at',
        'is_spv'

        // tambahkan kolom lain jika ada di tabel `moduls`
    ];

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'questionnaire_mappings', 'modul_id', 'tenant_id');
    }

    public function questions()
{
    return $this->belongsToMany(Question::class, 'modul_mappings', 'modul_id', 'question_id');
}

}
