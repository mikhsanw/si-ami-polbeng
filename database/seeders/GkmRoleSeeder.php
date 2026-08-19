<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GkmRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create or find role GKM
        $gkmRole = Role::firstOrCreate(['name' => 'GKM']);

        // Module codes that GKM can access (read-only / list)
        // Restricted ONLY to Evaluasi Diri (hasilaudits) and Template Instrumen (instrumentemplates)
        $readModules = [
            'hasilaudits',
            'instrumentemplates',
        ];

        $permissions = [];
        foreach ($readModules as $module) {
            $permissionName = $module . ' list';
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            $permissions[] = $permission;
        }

        // Sync permissions so GKM only has exactly these permissions
        $gkmRole->syncPermissions($permissions);
    }
}
