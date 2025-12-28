<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Panne;
use App\Models\Equipement; // Make sure to import the Equipement model if you need it
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use Illuminate\Notifications\Messages\BroadcastMessage;

class PanneSignaleeNotification extends Notification implements ShouldQueue // Implemented ShouldQueue for better performance
{
    use Queueable;

    protected $panne;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Panne $panne The Panne model instance.
     */
    public function __construct(Panne $panne)
    {
        $this->panne = $panne;
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
     * This method is used for storing notification data in the database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Get equipment details if available, with defensive checks
        $equipmentDetails = null;
        if ($this->panne->equipement) {
            $equipmentDetails = [
                'id' => $this->panne->equipement->id,
                'nom' => $this->panne->equipement->nom_eq ?? 'N/A',
                'num_serie' => $this->panne->equipement->num_serie_eq ?? 'N/A',
                'num_inventaire' => $this->panne->equipement->num_inventaire_eq ?? 'N/A',
            ];
        }

        return [
            'type' => 'panne', // Consistent type for the Blade view to identify
            'message' => 'Nouvelle panne signalée par ' .
                ($this->panne->user->nom_ag ?? 'Utilisateur Inconnu') . ' ' .
                ($this->panne->user->pren_ag ?? ''),
            'equipments' => $equipmentDetails ? [$equipmentDetails] : [], // Use 'equipments' key for consistency
            'libelle' => $this->panne->lib_pan ?? 'Non spécifié',
            'panne_id' => $this->panne->id,
            'statut' => $this->panne->sta_pan ?? 'Non défini', // Assuming 'statut' exists on Panne model
            'diagnostic' => $this->panne->diagnostic ?? '-', // Assuming 'diagnostic' exists on Panne model
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'notification' => $this->toArray($notifiable),
            'created_at' => now()->toDateTimeString(),
        ]);
    }
    /**
     * Get the mail representation of the notification.
     * This method is optional unless 'mail' is in your via() channels.
     *
     * @param object $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
}
