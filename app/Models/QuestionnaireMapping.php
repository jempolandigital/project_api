<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionnaireMapping extends Model
{
    protected $table = 'questionnaire_mappings';

    protected $fillable = ['tenant_id', 'modul_id'];

    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }

    public function modul() {
        return $this->belongsTo(Modul::class);
    }
}
