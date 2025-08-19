<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'address',
        'country',
        'city',
        'province',
        'longitude',
        'latitude',
        'radius',
    ];

    public function moduls()
{
    return $this->belongsToMany(Modul::class, 'questionnaire_mappings', 'tenant_id', 'modul_id');
}

}
