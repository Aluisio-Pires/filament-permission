<?php

namespace AluisioPires\Permission\Tests\TestModels;

use AluisioPires\Permission\Traits\HasRoles;

class User extends UserWithoutHasRoles
{
    use HasRoles;
}
