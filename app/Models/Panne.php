<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Panne extends Model
{
    protected $fillable = [

        'lib_pan',
        'lib_cat',
        'sta_pan',
        'diag_pan',
        'action_pan',
        'date_signa',
        'date_dt',
        'date_rsl',
        'date_an',
        'user_id',
        'user2_id',
        'equipement_id',
    ];

    protected $casts = [
        'date_signa' => 'datetime',
        'date_dt' => 'datetime',
        'date_rsl' => 'datetime',
        'date_an' => 'datetime',
    ];

    /**
     * Get the user that owns the panne.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class); // Pour user_id
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user2_id'); // Pour user2_id
    }
    /**
     * Get the categorie_panne that owns the panne.
     */

    /**
     * Get the equipements for the panne.
     */
    public function equipement() // Use singular 'equipement' as per your Blade template
    {
        return $this->belongsTo(Equipement::class);
    }
}
