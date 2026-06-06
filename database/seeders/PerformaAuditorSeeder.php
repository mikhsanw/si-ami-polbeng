<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PerformaAuditorSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Permissions ──────────────────────────────────────────────────
        $actions = ['list', 'create', 'edit', 'delete'];
        foreach ($actions as $action) {
            Permission::firstOrCreate(
                ['name' => "performaauditors $action", 'guard_name' => 'web']
            );
        }

        // ── 2. Assign ke roles pimpinan ──────────────────────────────────────
        foreach (['Admin', 'Super Admin', 'Direktur'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo(
                    array_map(fn ($a) => "performaauditors $a", $actions)
                );
            }
        }

        // ── 3. Menu ──────────────────────────────────────────────────────────
        // Cari parent "Laporan" (title atau code mengandung 'laporan')
        $parent = Menu::where('code', 'laporan')
            ->orWhere('title', 'like', '%Laporan%')
            ->first();

        $menuData = [
            'title'    => 'Performa Auditor',
            'subtitle' => 'Performa Auditor',
            'code'     => 'performaauditors',
            'url'      => 'performaauditors',
            'model'    => null,
            'icon'     => 'ki-duotone ki-chart-line-up-2',
            'type'     => 'backend',
            'show'     => '1',
            'active'   => '1',
            'sort'     => '99',
        ];

        if ($parent) {
            $menuData['parent_id'] = $parent->id;
        }

        Menu::updateOrCreate(['code' => 'performaauditors'], $menuData);
    }
}
