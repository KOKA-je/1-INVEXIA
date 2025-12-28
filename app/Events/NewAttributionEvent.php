<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Equipement;

class NewAttributionEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public string $userName;
    public $attributedEquipments;
    public string $actionType;
    public $userId; // Nouveau: ID de l'utilisateur concerné

    public function __construct(
        string $userName,
        $attributedEquipments,
        $userId, // Ajout du paramètre userId
        string $actionType = 'nouvelle attribution'
    ) {
        $this->userName = $userName;
        $this->userId = $userId;
        $this->actionType = $actionType;
        $this->attributedEquipments = $this->resolveEquipments($attributedEquipments);

        if ($this->attributedEquipments->isEmpty()) {
            throw new \InvalidArgumentException("Aucun équipement valide fourni pour l'attribution");
        }
    }

    protected function resolveEquipments($equipments)
    {
        if (is_array($equipments)) {
            return is_int($equipments[0] ?? null)
                ? Equipement::whereIn('id', $equipments)->get()
                : collect($equipments);
        }
        return $equipments;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'attribution.created';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->actionType,
            'message' => $this->getMessage(),
            'equipments' => $this->getEquipmentDetails(),
            'attributed_by' => $this->userName,
            'timestamp' => now()->toISOString()
        ];
    }

    protected function getMessage(): string
    {
        $count = $this->attributedEquipments->count();

        return match ($this->actionType) {
            'nouvelle attribution' => $count > 1
                ? "Vous avez reçu une nouvelle attribution avec {$count} machines"
                : "Vous avez reçu un nouvel equipement",
            'ajout machines' => $count > 1
                ? "{$count} nouvelles machines ont été ajoutées à votre attribution"
                : "Une nouvelle machine a été ajoutée à votre attribution",
            default => "Votre attribution a été mise à jour",
        };
    }

    protected function getEquipmentDetails()
    {
        return $this->attributedEquipments->map(fn($equipment) => [
            'id' => $equipment->id,
            'nom' => $equipment->nom_eq ?? 'N/A',
            'num_serie' => $equipment->num_serie_eq ?? 'N/A',
            'num_inventaire' => $equipment->num_inventaire_eq ?? 'N/A',
        ]);
    }
}
