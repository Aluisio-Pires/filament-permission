<?php

namespace AluisioPires\Permission;

use AluisioPires\Permission\Contracts\Permission as PermissionContract;
use AluisioPires\Permission\Contracts\Role as RoleContract;
use Composer\InstalledVersions;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class PermissionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->offerPublishing();

        $this->registerMacroHelpers();

        $this->registerCommands();

        $this->registerModelBindings();

        $this->registerOctaneListener();

        $this->callAfterResolving(Gate::class, function (Gate $gate, Application $app) {
            if ($this->app['config']->get('permission.register_permission_check_method')) {
                /** @var PermissionRegistrar $permissionLoader */
                $permissionLoader = $app->get(PermissionRegistrar::class);
                $permissionLoader->clearPermissionsCollection();
                $permissionLoader->registerPermissions($gate);
            }
        });

        $this->app->singleton(PermissionRegistrar::class);

        $this->registerAbout();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/permission.php',
            'permission'
        );

        $this->callAfterResolving('blade.compiler', fn (BladeCompiler $bladeCompiler) => $this->registerBladeExtensions($bladeCompiler));
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

    protected function registerCommands(): void
    {
        $this->commands([
            Commands\CacheReset::class,
        ]);

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Commands\CreateRole::class,
            Commands\CreatePermission::class,
            Commands\Show::class,
            Commands\UpgradeForTeams::class,
        ]);
    }

    protected function registerOctaneListener(): void
    {
        if ($this->app->runningInConsole() || ! $this->app['config']->get('octane.listeners')) {
            return;
        }

        $dispatcher = $this->app[Dispatcher::class];
        // @phpstan-ignore-next-line
        $dispatcher->listen(function (\Laravel\Octane\Contracts\OperationTerminated $event) {
            // @phpstan-ignore-next-line
            $event->sandbox->make(PermissionRegistrar::class)->setPermissionsTeamId(null);
        });

        if (! $this->app['config']->get('permission.register_octane_reset_listener')) {
            return;
        }
        // @phpstan-ignore-next-line
        $dispatcher->listen(function (\Laravel\Octane\Contracts\OperationTerminated $event) {
            // @phpstan-ignore-next-line
            $event->sandbox->make(PermissionRegistrar::class)->clearPermissionsCollection();
        });
    }

    protected function registerModelBindings(): void
    {
        $this->app->bind(PermissionContract::class, fn ($app) => $app->make($app->config['permission.models.permission']));
        $this->app->bind(RoleContract::class, fn ($app) => $app->make($app->config['permission.models.role']));
    }

    public static function bladeMethodWrapper($method, $role, $guard = null): bool
    {
        return auth($guard)->check() && auth($guard)->user()->{$method}($role);
    }

    protected function registerBladeExtensions(BladeCompiler $bladeCompiler): void
    {
        $bladeMethodWrapper = '\\AluisioPires\\Permission\\PermissionServiceProvider::bladeMethodWrapper';

        // permission checks
        $bladeCompiler->if('haspermission', fn () => $bladeMethodWrapper('checkPermissionTo', ...func_get_args()));

        // role checks
        $bladeCompiler->if('role', fn () => $bladeMethodWrapper('hasRole', ...func_get_args()));
        $bladeCompiler->if('hasrole', fn () => $bladeMethodWrapper('hasRole', ...func_get_args()));
        $bladeCompiler->if('hasanyrole', fn () => $bladeMethodWrapper('hasAnyRole', ...func_get_args()));
        $bladeCompiler->if('hasallroles', fn () => $bladeMethodWrapper('hasAllRoles', ...func_get_args()));
        $bladeCompiler->if('hasexactroles', fn () => $bladeMethodWrapper('hasExactRoles', ...func_get_args()));
        $bladeCompiler->directive('endunlessrole', fn () => '<?php endif; ?>');
    }

    protected function registerMacroHelpers(): void
    {
        if (! method_exists(Route::class, 'macro')) { // @phpstan-ignore-line Lumen
            return;
        }

        Route::macro('role', function ($roles = []) {
            /** @var Route $this */
            return $this->middleware('role:'.implode('|', Arr::wrap($roles)));
        });

        Route::macro('permission', function ($permissions = []) {
            /** @var Route $this */
            return $this->middleware('permission:'.implode('|', Arr::wrap($permissions)));
        });
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

        // array format: 'Display Text' => 'boolean-config-key name'
        $features = [
            'Teams' => 'teams',
            'Wildcard-Permissions' => 'enable_wildcard_permission',
            'Octane-Listener' => 'register_octane_reset_listener',
            'Passport' => 'use_passport_client_credentials',
        ];

        $config = $this->app['config'];

        AboutCommand::add('AluisioPires Permissions', static fn () => [
            'Features Enabled' => collect($features)
                ->filter(fn (string $feature, string $name): bool => $config->get("permission.{$feature}"))
                ->keys()
                ->whenEmpty(fn (Collection $collection) => $collection->push('Default'))
                ->join(', '),
            'Version' => InstalledVersions::getPrettyVersion('aluisio-pires/filament-permission'),
        ]);
    }

    private function publishesFiles()
    {
        $this->publishes([
            __DIR__.'/../config/permission.php' => config_path('permission.php'),
        ], 'permission-config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_permission_tables.php.stub' => $this->getMigrationFileName('create_permission_tables.php'),
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
            __DIR__.'/../app/Filament/Resources/PermissionResource/Pages/ListPermissions.php' => app_path('Filament/Resources/PermissionResource/Pages/ListPermission.php'),
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
            __DIR__.'/../app/Filament/Resources/RoleResource/Pages/ListRoles.php' => app_path('Filament/Resources/RoleResource/Pages/ListRole.php'),
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
