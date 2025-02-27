<?php

namespace AluisioPires\Permission\Exceptions;

use InvalidArgumentException;

class WildcardPermissionNotImplementsContract extends InvalidArgumentException
{
    public static function create()
    {
        return new static('Wildcard permission class must implements AluisioPires\Permission\Contracts\Wildcard contract');
    }
}
