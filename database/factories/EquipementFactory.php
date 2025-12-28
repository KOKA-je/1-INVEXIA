<?php

namespace Database\Factories;

use App\Models\CategorieEquipement;
use App\Models\Category_eq;
use App\Models\Equipement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipement>
 */
class EquipementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Equipement::class;

    public function definition(): array
    {
        return [
            'num_serie_eq' => 'PDT-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'num_inventaire_eq' =>  str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT) . '-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT) . '-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT) . '-' . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'nom_eq' => $this->faker->randomElement(['Souris Logitech', ' HP Clavier', 'HP Ecran', ' HP Imprimante', ' MTN BOX-WIFI', 'DELL LATITUDE', 'MACBOOK AIR', 'HP PROBOOK', 'HP ELITE G6']) . str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'designation_eq' => 'N/A',
            'etat_eq' =>  'Bon',
            'statut_eq' =>  'disponible',
            'date_acq' => $this->faker->date('Y-m-d'),
            'categorie_equipement_id' => CategorieEquipement::inRandomOrder()->value('id'),
        ];
    }
}
