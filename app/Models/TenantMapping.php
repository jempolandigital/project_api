<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantMapping extends Model
{
    protected $table = 'tenant_mapping';

    protected $fillable = [
        'user_id',
        'tenant_id',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
