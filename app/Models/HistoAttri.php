<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // Ajouté pour la relation avec les équipements

class HistoAttri extends Model
{
    protected $table = 'histo_attri_tables';

    protected $fillable = [
        'action_type',
        'attribution_id',
        'user_id',
        'user2_id',
        'equipements',
        'equipements_ajoutes',
        'equipements_retires',
    ];

    protected $casts = [
        'equipements'         => 'array',
        'equipements_ajoutes' => 'array',
        'equipements_retires' => 'array',
    ];

    /**
     * Auteur de l'action (user_id)
     */
    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Bénéficiaire de l'attribution (user2_id)
     */
    public function beneficiaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    /**
     * Relation avec l'attribution associée (via attribution_id)
     */
    public function attribution(): BelongsTo
    {
        return $this->belongsTo(Attribution::class, 'attribution_id');
    }

    /**
     * Retourne les modèles d'équipements associés à cet historique,
     * en fusionnant les IDs de tous les champs pertinents.
     * Cette méthode peut être utilisée pour simplifier la récupération des équipements
     * dans la vue ou le contrôleur.
     */
    public function equipements(): HasMany
    {
        $allEquipementIds = collect([])
            ->merge($this->equipements ?? [])
            ->merge($this->equipements_ajoutes ?? [])
            ->merge($this->equipements_retires ?? [])
            ->unique()
            ->filter() // Supprime les valeurs nulles ou vides
            ->toArray();

        return $this->hasMany(Equipement::class, 'id', 'id') // Ceci est un peu tordu pour une relation HasMany directe
                    ->whereIn('id', $allEquipementIds);
    }
}