<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $admin = Role::create(['name' => 'Admin']);
        $user = Role::create(['name' => 'User']);

        // Define permissions
        $permissions = [

            'view-dashboard-admin',
            'list-role',
            'create-role',
            'view-role',
            'edit-role',
            'delete-role',

            'list-permission',
            'create-permission',
            'view-permission',
            'edit-permission',
            'delete-permission',

            'list-user',
            'create-user',
            'view-user',
            'edit-user',
            'delete-user',

            'list-equipement',
            'create-equipement',
            'view-equipement',
            'edit-equipement',
            'delete-equipement',

            'view-my-equipement',
            'list-categorie',
            'create-categorie',
            'view-categorie',
            'edit-categorie',
            'delete-categorie',

            'list-attribution',
            'list-attribution-history',
            'create-attribution',
            'view-attribution',
            'edit-attribution',
            'delete-attribution',

            'list-panne',
            'create-panne',
            'view-panne',
            'change-status-panne',
            'edit-panne',
            'cancel-panne',
            'view-equipement-history'
        ];
        // Create and assign permissions to roles
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);

            // Assign all permissions to Super Admin
            $superAdmin->givePermissionTo($permission);

            // Assign specific permissions to Admin
            if (in_array($permission->name, [


                'view-dashboard-admin',
                'list-role',


                'list-role',
                'create-role',
                'view-role',
                'edit-role',


                'list-permission',
                'create-permission',
                'view-permission',
                'edit-permission',


                'list-user',
                'create-user',
                'view-user',
                'edit-user',

                'list-equipement',
                'create-equipement',
                'view-equipement',
                'edit-equipement',



                'list-categorie',
                'create-categorie',
                'view-categorie',
                'edit-categorie',


                'list-attribution',
                'list-attribution-history',
                'create-attribution',
                'view-attribution',
                'edit-attribution',


                'list-panne',
                'create-panne',
                'view-panne',
                'change-status-panne',
                'edit-panne',
                'view-equipement-history'




            ])) {
                $admin->givePermissionTo($permission);
            }
            // Assign specific permissions to User
            if (in_array($permission->name, [

                'view-equipement',

            ])) {
                $user->givePermissionTo($permission);
            }
        }
    }
}
