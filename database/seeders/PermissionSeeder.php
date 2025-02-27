<?php

namespace Database\seeders;

use AluisioPires\Permission\Models\Permission;
use AluisioPires\Permission\Models\Role;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\AluisioPires\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::firstOrCreate(['name' => 'admin']);
        $modelPermissions = $this->crudPermissionNames();
        $customPermissions = collect([
            'attach roles to users',
            'detach roles to users',
        ]);

        $permissions = $modelPermissions->merge($customPermissions)->unique();

        $permissions->each(function ($permission): void {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        });

        app()[\AluisioPires\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $role->givePermissionTo($permissions);
    }

    public function getModels(): Collection
    {
        $models = collect(File::allFiles(app_path()))
            ->map(function ($item) {
                $path = $item->getRelativePathName();

                return sprintf('\%s%s',
                    Container::getInstance()->getNamespace(),
                    strtr(substr($path, 0, strrpos($path, '.')), '/', '\\'));
            })
            ->filter(function ($class) {
                $valid = false;

                if (class_exists($class)) {
                    $reflection = new \ReflectionClass($class);
                    $valid = $reflection->isSubclassOf(Model::class) &&
                        ! $reflection->isAbstract();
                }

                return $valid;
            });

        return $models->values();
    }

    public function getModelNames(): Collection
    {
        $models = $this->getModels();

        return $models->values()->map(function ($class) {
            return class_basename($class);
        });
    }

    public function crudPermissionNames(): Collection
    {
        $modelNames = $this->getModelNames();

        return $modelNames->map(function ($name) {
            return [
                'view '.str($name)->lower()->plural(),
                'create '.str($name)->lower()->plural(),
                'update '.str($name)->lower()->plural(),
                'delete '.str($name)->lower()->plural(),
            ];
        })->flatten();
    }
}
