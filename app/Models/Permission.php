<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends \AluisioPires\Permission\Models\Permission
{
    use HasFactory;

    protected $fillable = [
        'name',
        'guard_name',
    ];
    //
}
