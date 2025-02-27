<?php

namespace App\Models;

use AluisioPires\Permission\PermissionRegistrar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends \AluisioPires\Permission\Models\Role
{
    use HasFactory;

    protected $fillable = [
        'name',
        'guard_name',
    ];

    public function users(): BelongsToMany
    {
        return $this->morphedByMany(
            getModelForGuard($this->attributes['guard_name'] ?? config('auth.defaults.guard')),
            'model',
            config('permission.table_names.model_has_roles'),
            app(PermissionRegistrar::class)->pivotRole,
            config('permission.column_names.model_morph_key')
        );
    }
}
