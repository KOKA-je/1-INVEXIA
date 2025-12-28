<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use App\Models\Equipement;

class AttributionRemovedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userName;
    public $removedEquipments;
    public $actionType;
    public $userId; // ID de l'utilisateur concerné

    /**
     * Create a new event instance.
     */
    public function __construct($userName, $removedEquipments, $userId, $actionType = 'retrait machines')
    {
        $this->userName = $userName;
        $this->userId = $userId;
        $this->actionType = $actionType;

        // Convertir les IDs en modèles si nécessaire
        if (is_array($removedEquipments) && !empty($removedEquipments)) {
            $this->removedEquipments = is_int($removedEquipments[0])
                ? Equipement::whereIn('id', $removedEquipments)->get()
                : collect($removedEquipments);
        } else {
            $this->removedEquipments = $removedEquipments;
        }
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
        return 'attribution.removed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $equipmentDetails = $this->removedEquipments->map(function ($equipment) {
            return [
                'id' => $equipment->id,
                'nom' => $equipment->nom_eq ?? 'N/A',
                'num_serie' => $equipment->num_serie_eq ?? 'N/A',
                'num_inventaire' => $equipment->num_inventaire_eq ?? 'N/A',
            ];
        })->toArray();

        $message = match ($this->actionType) {
            'retrait attribution' => count($equipmentDetails) > 1
                ? "Une attribution complète  a été retirée"
                : "Une attribution vous a été retirée",
            default => count($equipmentDetails) > 1
                ? "Certaines machines ont été retirées de votre attribution"
                : "Une machine a été retirée de votre attribution",
        };

        return [
            'type' => $this->actionType,
            'message' => $message,
            'equipments' => $equipmentDetails,
            'removed_by' => $this->userName,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
