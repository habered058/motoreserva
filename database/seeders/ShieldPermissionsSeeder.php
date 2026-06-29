<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShieldPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $resources = ['servicio', 'tecnico', 'reserva', 'user'];
        $actions   = ['view_any', 'view', 'create', 'update', 'delete', 'delete_any'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name'       => "{$action}_{$resource}",
                    'guard_name' => 'web',
                ]);
            }
        }

        $admin = Role::where('name', 'admin')->first();
        $admin?->syncPermissions(Permission::all());
    }
}
