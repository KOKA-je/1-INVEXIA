<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use App\Models\Panne;
use App\Models\User;

class PanneTraiteeEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $panne;
    public $auteur;
    public $newStatus;
    public $userId; // ID de l'utilisateur à notifier

    /**
     * Create a new event instance.
     */
    public function __construct(Panne $panne, User $auteur, string $newStatus,  $userId)
    {
        $this->panne = $panne;
        $this->auteur = $auteur;
        $this->newStatus = $newStatus;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'panne.treated'; // Nom cohérent avec la convention
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $equipment = $this->panne->equipement;

        return [
            'notification' => [
                'type' => 'panne',
                'message' => $this->getStatusMessage(),
                'equipement' => $equipment ? [
                    'id' => $equipment->id,
                    'nom' => $equipment->nom_eq ?? 'N/A',
                    'num_serie' => $equipment->num_serie_eq ?? 'N/A',
                    'num_inventaire' => $equipment->num_inventaire_eq ?? 'N/A'
                ] : null,
                'libelle' => $this->panne->lib_pan ?? 'Non spécifié',
                'panne_id' => $this->panne->id,
                'statut' => $this->newStatus,
                'diagnostic' => $this->panne->diag_pan ?? 'Non précisé',
                'updated_by' => $this->auteur->nom_ag . ' ' . $this->auteur->pren_ag,
                'timestamp' => now()->toISOString()
            ]
        ];
    }

    /**
     * Generate the appropriate status message.
     */
    protected function getStatusMessage(): string
    {
        $equipmentName = $this->panne->equipement->nom_eq ?? 'l\'équipement';

        return match ($this->newStatus) {
            'En cours' => "Le traitement de votre panne sur $equipmentName a commencé.",
            'Résolue' => "Votre panne sur $equipmentName a été résolue !",
            'Annulée' => "Votre panne sur $equipmentName a été annulée.",
        };
    }
}
