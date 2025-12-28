<?php

namespace Database\Seeders;

use App\Models\CategorieEquipement;

use App\Models\Equipement;
use Illuminate\Database\Seeder;

class EquipementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer manuellement une liste de catégories connues (si pas déjà dans CategoryEqSeeder)
        $categories = [
            'UC',
            'Portable',
            'Micro-ordinateur',

            'Tablette FAMOCO',
            'Telephone portable',
            'Telephone ip',

            'Ecran',
            'Clavier',
            'Souris',

            'Scanner',
            'Switch',
            'Routeur',
            'Imprimante',
            'Box wi-fi',
            'Pocket wi-fi',


        ];

        // Créer les catégories si elles n'existent pas encore
        $categoryIds = [];
        foreach ($categories as $libelle) {
            $category = CategorieEquipement::firstOrCreate(['lib_cat' => $libelle]);
            $categoryIds[] = $category->id;
        }

        // Générer 30 équipements avec une catégorie tirée au hasard
        Equipement::factory()
            ->count(1)
            ->create([
                'categorie_equipement_id' => fake()->randomElement($categoryIds),
            ]);
    }
}
