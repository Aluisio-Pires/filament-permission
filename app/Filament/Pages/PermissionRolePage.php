<?php

namespace App\Filament\Pages;

use AluisioPires\Permission\PermissionRegistrar;
use App\Models\Permission;
use App\Models\Role;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class PermissionRolePage extends Page
{
    /** @var Collection<int, Role> */
    public Collection $roles;

    /** @var Collection<int, Permission> */
    public Collection $permissions;

    public string $search = '';

    /** @var array<int, mixed> */
    public array $selectedPermissions;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static string $view = 'pages.permission-role-page';

    public static function getRoutePath(): string
    {
        return 'acl';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Administration');
    }

    public static function getNavigationLabel(): string
    {
        return __('Access Control Panel');
    }

    public function getTitle(): string|Htmlable
    {
        return static::getNavigationLabel();
    }

    public static function canAccess(): bool
    {
        // @phpstan-ignore-next-line
        return auth()->user()?->hasPermissionTo('update roles');
    }

    public function mount(): void
    {
        $this->roles = Role::all();
        $this->permissions = Permission::all();

        $this->selectedPermissions = $this->roles->mapWithKeys(function (Role $role) {
            return [(int) $role->id => $role->permissions->pluck('id')->toArray()];
        })->toArray();

    }

    public function searchPermissions(string $value): void
    {
        $this->search = $value;
    }

    public function savePermissions(): void
    {
        foreach ($this->roles as $role) {
            $selected = $this->selectedPermissions[$role->id] ?? [];
            $permissions = Permission::whereIn('id', $selected)->get();
            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Notification::make()
            ->title(__('Saved.'))
            ->success()
            ->send();

        session()->flash('success', __('Permissions updated successfully!'));
    }
}
