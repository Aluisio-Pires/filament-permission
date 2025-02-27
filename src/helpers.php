<?php

if (! function_exists('getModelForGuard')) {
    function getModelForGuard(string $guard): ?string
    {
        return AluisioPires\Permission\Guard::getModelForGuard($guard);
    }

}

if (! function_exists('setPermissionsTeamId')) {
    /**
     * @param  int|string|null|\Illuminate\Database\Eloquent\Model  $id
     */
    function setPermissionsTeamId($id)
    {
        app(\AluisioPires\Permission\PermissionRegistrar::class)->setPermissionsTeamId($id);
    }
}

if (! function_exists('getPermissionsTeamId')) {
    /**
     * @return int|string|null
     */
    function getPermissionsTeamId()
    {
        return app(\AluisioPires\Permission\PermissionRegistrar::class)->getPermissionsTeamId();
    }
}
