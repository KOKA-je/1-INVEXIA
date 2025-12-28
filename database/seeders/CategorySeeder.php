<?php

namespace Database\Seeders;

use App\Models\CategorieEquipement;

use App\Models\Equipement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CategorieEquipement::factory()
        ->has(Equipement::factory()->count(30))
        ->create();
    }
}