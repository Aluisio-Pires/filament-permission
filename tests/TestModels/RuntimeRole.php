<?php

namespace AluisioPires\Permission\Tests\TestModels;

class RuntimeRole extends \AluisioPires\Permission\Models\Role
{
    protected $visible = [
        'id',
        'name',
    ];
}
