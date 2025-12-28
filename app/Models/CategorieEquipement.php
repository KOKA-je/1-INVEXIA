<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategorieEquipement extends Model
{
    use HasFactory;

    protected $table = 'categorie_equipements';

    protected $fillable = [

        'lib_cat',
    ];

    public function equipements()
    {
        return $this->hasMany(Equipement::class, 'categorie_equipement_id');
    }

    public function pannes()
    {
        return $this->hasManyThrough(
            Panne::class,
            Equipement::class,
            'categorie_equipement_id', // Clé étrangère dans equipements
            'equipement_id', // Clé étrangère dans pannes
            'id', // Clé primaire dans categorie_equipements
            'id' // Clé primaire dans equipements
        );
    }
}
