<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\MenuSeeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    private $permissions = [
        'roles list',
        'roles create',
        'roles edit',
        'roles delete',
        'users list',
        'users create',
        'users edit',
        'users delete',
        'products list',
        'products create',
        'products edit',
        'products delete',
    ];

    public function run(): void
    {
        // User::factory(10)->create();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        foreach ($this->permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        $role = Role::create(['name' => 'Admin']);
        $permissions = Permission::pluck('id', 'id')->all();
        $role->syncPermissions($permissions);
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@example.com',
        ]);
        $user->assignRole([$role->id]);

        $superadminRole = Role::create(['name' => 'Super Admin']);
        $user = User::factory()->create([
            'name' => 'Example superadmin user',
            'email' => 'superadmin@example.com',
        ]);
        $user->assignRole($superadminRole);

        $this->call(MenuSeeder::class);
    }
}
