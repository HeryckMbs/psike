<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Orders
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.delete',
            
            // Products
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            
            // Blog
            'blog.view',
            'blog.create',
            'blog.edit',
            'blog.delete',
            
            // Kanban
            'kanban.view',
            'kanban.edit',
            
            // Categories
            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'api']);
        }

        // Create roles
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $manager = Role::create(['name' => 'manager', 'guard_name' => 'api']);
        $seller = Role::create(['name' => 'seller', 'guard_name' => 'api']);
        $viewer = Role::create(['name' => 'viewer', 'guard_name' => 'api']);

        // Assign permissions to roles
        $admin->givePermissionTo(Permission::all());
        
        $manager->givePermissionTo([
            'orders.view',
            'orders.edit',
            'products.view',
            'products.edit',
            'blog.view',
            'blog.edit',
            'kanban.view',
            'kanban.edit',
            'categories.view',
            'categories.edit',
        ]);
        
        $seller->givePermissionTo([
            'orders.view',
            'orders.create',
            'orders.edit',
            'products.view',
            'blog.view',
            'kanban.view',
            'kanban.edit',
            'categories.view',
        ]);
        
        $viewer->givePermissionTo([
            'orders.view',
            'products.view',
            'blog.view',
            'kanban.view',
            'categories.view',
        ]);
    }
}
