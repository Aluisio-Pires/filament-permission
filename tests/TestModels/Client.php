<?php

namespace AluisioPires\Permission\Tests\TestModels;

use AluisioPires\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Laravel\Passport\Client as BaseClient;

class Client extends BaseClient implements AuthorizableContract
{
    use Authorizable;
    use HasRoles;

    /**
     * Required to make clear that the client requires the api guard
     */
    protected string $guard_name = 'api';
}
