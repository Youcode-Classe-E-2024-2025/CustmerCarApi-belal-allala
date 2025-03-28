<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'client', 'label' => 'Client']);
        Role::create(['name' => 'agent', 'label' => 'Agent Support']);
        Role::create(['name' => 'admin', 'label' => 'Administrateur']);
    }
}