<?php

namespace App\Models;

use App\Models\Attribution;
use App\Models\Category_eq;
use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Equipement extends Model
{
    use HasFactory;
    protected $table = 'equipements';
    protected $fillable = [

        'num_serie_eq',
        'num_inventaire_eq',
        'nom_eq',
        'designation_eq',
        'etat_eq',
        'statut_eq',
        'date_acq',
        'user_id',
        'categorie_equipement_id',
    ];


    protected $casts = [
        'date_acq' => 'datetime:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categorieEquipement()
    {
        return $this->belongsTo(CategorieEquipement::class, 'categorie_equipement_id');
    }

    public function attributions()
    {
        return $this->belongsToMany(Attribution::class, 'attribution_equipement');
    }

    public function pannes()
    {
        return $this->hasMany(Panne::class, 'equipement_id');
    }


    public function historique()
    {
        return $this->hasMany(HistoriqueEquipement::class);
    }
}
