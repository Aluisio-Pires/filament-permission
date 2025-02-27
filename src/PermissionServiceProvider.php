<?php

namespace AluisioPires\Permission;

use Composer\InstalledVersions;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->offerPublishing();

        $this->registerAbout();
    }

    public function register(): void
    {
        //
    }

    protected function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        if (! function_exists('config_path')) {
            // function not available and 'publish' not relevant in Lumen
            return;
        }

        $this->publishesFiles();
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     */
    protected function getMigrationFileName(string $migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make([$this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR])
            ->flatMap(fn ($path) => $filesystem->glob($path.'*_'.$migrationFileName))
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }

    protected function registerAbout(): void
    {
        if (! class_exists(InstalledVersions::class) || ! class_exists(AboutCommand::class)) {
            return;
        }

        AboutCommand::add('AluisioPires Permissions', static fn () => [
            'Version' => InstalledVersions::getPrettyVersion('aluisio-pires/filament-permission'),
        ]);
    }

    private function publishesFiles(): void
    {
        Artisan::call(
            'vendor:publish',
            [
                '--provider' => "Spatie\Permission\PermissionServiceProvider",
            ]
        );

        $this->publishes([
            __DIR__.'/../database/migrations/seed_permissions.php.stub' => $this->getMigrationFileName('seed_permissions.php'),
        ], 'permission-migrations');

        $this->publishes([
            __DIR__.'/../app/Filament/Pages/PermissionRolePage.php' => app_path('Filament/Pages/PermissionRolePage.php'),
        ], 'permission-filament');

        $this->publishes([
            __DIR__.'/../app/Filament/Resources/PermissionResource/Pages/CreatePermission.php' => app_path('Filament/Resources/PermissionResource/Pages/CreatePermission.php'),
        ], 'permission-filament');

        $this->publishes([
            __DIR__.'/../app/Filament/Resources/PermissionResource/Pages/EditPermission.php' => app_path('Filament/Resources/PermissionResource/Pages/EditPermission.php'),
        ], 'permission-filament');

        $this->publishes([
            __DIR__.'/../app/Filament/Resources/PermissionResource/Pages/ListPermissions.php' => app_path('Filament/Resources/PermissionResource/Pages/ListPermissions.php'),
        ], 'permission-filament');

        $this->publishes([
            __DIR__.'/../app/Filament/Resources/PermissionResource/Pages/ViewPermission.php' => app_path('Filament/Resources/PermissionResource/Pages/ViewPermission.php'),
        ], 'permission-filament');

        $this->publishes([
            __DIR__.'/../app/Filament/Resources/PermissionResource/RelationManagers/RolesRelationManager.php' => app_path('Filament/Resources/PermissionResource/RelationManagers/RolesRelationManager.php'),
        ], 'permission-filament');

        $this->publishes([
            __DIR__.'/../app/Filament/Resources/RoleResource/Pages/CreateRole.php' => app_path('Filament/Resources/RoleResource/Pages/CreateRole.php'),
        ], 'permission-filament');

        $this->publishes([
            __DIR__.'/../app/Filament/Resources/RoleResource/Pages/EditRole.php' => app_path('Filament/Resources/RoleResource/Pages/EditRole.php'),
        ], 'permission-filament');

        $this->publishes([
            __DIR__.'/../app/Filament/Resources/RoleResource/Pages/ListRoles.php' => app_path('Filament/Resources/RoleResource/Pages/ListRoles.php'),
        ], 'permission-filament');

        $this->publishes([
            __DIR__.'/../app/Filament/Resources/RoleResource/Pages/ViewRole.php' => app_path('Filament/Resources/RoleResource/Pages/ViewRole.php'),
        ], 'permission-filament');

        $this->publishes([
            __DIR__.'/../app/Filament/Resources/RoleResource/RelationManagers/PermissionsRelationManager.php' => app_path('Filament/Resources/RoleResource/RelationManagers/PermissionsRelationManager.php'),
        ], 'permission-filament');

        $this->publishes([
            __DIR__.'/../app/Filament/Resources/RoleResource/RelationManagers/UsersRelationManager.php' => app_path('Filament/Resources/RoleResource/RelationManagers/UsersRelationManager.php'),
        ], 'permission-filament');

        $this->publishes([
            __DIR__.'/../app/Filament/Resources/RoleResource.php' => app_path('Filament/Resources/RoleResource.php'),
        ], 'permission-filament');

        $this->publishes([
            __DIR__.'/../app/Models/Permission.php' => app_path('Models/Permission.php'),
        ], 'permission-models');

        $this->publishes([
            __DIR__.'/../app/Models/Role.php' => app_path('Models/Role.php'),
        ], 'permission-models');

        $this->publishes([
            __DIR__.'/../app/Policies/PermissionPolicy.php' => app_path('Policies/PermissionPolicy.php'),
        ], 'permission-policies');

        $this->publishes([
            __DIR__.'/../app/Policies/RolePolicy.php' => app_path('Policies/RolePolicy.php'),
        ], 'permission-policies');

        $this->publishes([
            __DIR__.'/../database/factories/PermissionFactory.php' => database_path('factories/PermissionFactory.php'),
        ], 'permission-factories');

        $this->publishes([
            __DIR__.'/../database/factories/RoleFactory.php' => database_path('factories/RoleFactory.php'),
        ], 'permission-factories');

        $this->publishes([
            __DIR__.'/../database/seeders/PermissionSeeder.php' => database_path('seeders/PermissionSeeder.php'),
        ], 'permission-factories');

        $this->publishes([
            __DIR__.'/../resources/views/filament/pages/permission-role-page.blade.php' => resource_path('views/filament/pages/permission-role-page.blade.php'),
        ], 'permission-views');
    }
}
