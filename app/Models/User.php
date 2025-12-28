<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles;
    use HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [

        'mat_ag',
        'nom_ag',
        'pren_ag',
        'email',
        'password',

        'dir_ag',
        'loc_ag',
        'sta_ag',


    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    public function scopeActive($query)
    {
        return $query->where('sta_ag', 'actif'); // Assuming 'status' column and 'active' value
    }


    public function attributions()
    {
        return $this->hasMany(Attribution::class);
    }



    public function pannes()
    {
        return $this->hasMany(Panne::class, 'user_id');
    }



    public function equipements()
    {
        return $this->hasMany(Equipement::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }




    // If you also want to easily access *currently* assigned equipment, keep this:
    public function currentEquipment()
    {
        return $this->belongsToMany(Equipement::class, 'user_equipment_history')
            ->withPivot('assigned_at', 'returned_at')
            ->wherePivotNull('returned_at')
            ->withTimestamps();
    }
}
