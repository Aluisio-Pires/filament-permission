<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view permissions');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permission $permission): bool
    {
        return $user->can('view permissions');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create permissions');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Permission $permission): bool
    {
        return $user->can('update permissions');
    }
}
