<?php

namespace App\Models;

use App\Models\Equipement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriqueEquipement extends Model
{


    use HasFactory;

    protected $table = 'historique_equipement';

    protected $fillable = [
        'equipement_id',
        'action',
        'details',
        'old_status',
        'new_status',
        'old_state',
        'new_state',
        'user_id',
    ];

    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
