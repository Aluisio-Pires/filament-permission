<?php

declare(strict_types=1);

namespace AluisioPires\Permission\Events;

use AluisioPires\Permission\Contracts\Role;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class RoleDetached
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Internally the HasRoles trait passes $rolesOrIds as a single  Eloquent record
     * Theoretically one could register the event to other places with an array etc
     * So a Listener should inspect the type of $rolesOrIds received before using.
     *
     * @param  array|int[]|string[]|Role|Role[]|Collection  $rolesOrIds
     */
    public function __construct(public Model $model, public mixed $rolesOrIds) {}
}
