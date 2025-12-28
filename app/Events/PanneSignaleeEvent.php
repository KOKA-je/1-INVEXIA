<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Panne;

class PanneSignaleeEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $panne;
    public $signaledBy; // Nom du signaleur
    public $userId; // ID de l'utilisateur à notifier

    /**
     * Create a new event instance.
     */
    public function __construct(Panne $panne, string $signaledBy, int $userId)
    {
        $this->panne = $panne;
        $this->signaledBy = $signaledBy;
        $this->userId = $userId;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'panne.signaled';
    }

    public function broadcastWith(): array
    {
        return [
            'notification' => [
                'type' => 'panne',
                'message' => 'Nouvelle panne signalée',
                'equipement' => $this->panne->equipement ? [
                    'id' => $this->panne->equipement->id,
                    'nom' => $this->panne->equipement->nom_eq,
                    'num_inventaire' => $this->panne->equipement->num_inventaire_eq
                ] : null,
                'libelle' => $this->panne->lib_pan,
                'signaled_by' => $this->signaledBy,
                'timestamp' => now()->toISOString()
            ]
        ];
    }
}
