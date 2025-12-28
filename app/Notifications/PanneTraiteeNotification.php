<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Panne;
use App\Models\User;
use App\Models\Equipement;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use Illuminate\Notifications\Messages\BroadcastMessage;

class PanneTraiteeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $panne;
    protected $auteur; // The user (admin/technician) who updated the panne
    protected $newStatus;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Panne $panne The Panne model instance.
     * @param \App\Models\User $auteur The user who performed the status update.
     * @param string $newStatus The new status of the panne.
     */
    public function __construct(Panne $panne, User $auteur, string $newStatus)
    {
        $this->panne = $panne;
        $this->auteur = $auteur;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $equipmentDetails = null;
        if ($this->panne->equipement) {
            $equipmentDetails = [
                'id' => $this->panne->equipement->id,
                'nom' => $this->panne->equipement->nom_eq ?? 'N/A',
                'num_serie' => $this->panne->equipement->num_serie_eq ?? 'N/A',
                'num_inventaire' => $this->panne->equipement->num_inventaire_eq ?? 'N/A',
            ];
        }

        $message = '';
        switch ($this->newStatus) {
            case 'En cours':
                $message = "Le traitement de votre panne sur l'équipement " .
                    ($equipmentDetails['nom'] ?? 'Inconnu') . " a commencé.";
                break;
            case 'Résolue':
                $message = "Votre panne sur l'équipement " .
                    ($equipmentDetails['nom'] ?? 'Inconnu') . " a été résolue !";
                break;
            case 'Annulée':
                $message = "Votre panne sur l'équipement " .
                    ($equipmentDetails['nom'] ?? 'Inconnu') . " a été annulée.";
                break;
        }
        return [
            'type' => 'panne', // Consistent type for the Blade view
            'message' => $message,
            'equipments' => $equipmentDetails ? [$equipmentDetails] : [], // Use 'equipments' key
            'libelle' => $this->panne->lib_pan ?? 'Non spécifié',
            'panne_id' => $this->panne->id,
            'statut' => $this->newStatus,
            'diagnostic' => $this->panne->diag_pan ?? '-',
            'updated_by' => $this->auteur->nom_ag . ' ' . $this->auteur->pren_ag,
        ];
    }
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'notification' => $this->toArray($notifiable),
            'created_at' => now()->toDateTimeString(),
        ]);
    }
}
