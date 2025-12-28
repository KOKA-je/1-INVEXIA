<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // If you intend to queue this notification
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use Illuminate\Notifications\Messages\BroadcastMessage;

class NewAttributionNotification extends Notification implements ShouldQueue // Added ShouldQueue for consistency and good practice
{
    use Queueable;

    protected $userName;
    protected $attributedEquipments; // This will now hold actual Equipement models (or a collection)
    protected $actionType;

    /**
     * Create a new notification instance.
     * @param string $userName The name of the user who performed the action.
     * @param \Illuminate\Support\Collection|array $attributedEquipments A collection or array of Equipement models.
     * @param string $actionType The type of attribution action.
     */
    public function __construct($userName, $attributedEquipments, $actionType = 'nouvelle attribution')
    {
        $this->userName = $userName;
        // Ensure it's a collection, or cast array to collection for consistent handling
        $this->attributedEquipments = is_array($attributedEquipments) ? collect($attributedEquipments) : $attributedEquipments;
        $this->actionType = $actionType;
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
        $equipmentDetails = [];
        // Loop directly through the Equipement objects already passed to the constructor
        foreach ($this->attributedEquipments as $equipment) {
            if ($equipment) { // Defensive check: ensure $equipment is not null
                $equipmentDetails[] = [
                    'id' => $equipment->id,
                    'nom' => $equipment->nom_eq ?? 'N/A', // Add null coalescing for robustness
                    'num_serie' => $equipment->num_serie_eq ?? 'N/A',
                    'num_inventaire' => $equipment->num_inventaire_eq ?? 'N/A',
                ];
            }
        }

        $message = '';
        if ($this->actionType === 'nouvelle attribution') {
            $message = count($equipmentDetails) > 1
                ? "Vous avez reçu une nouvelle attribution avec les machines suivantes :"
                : "Vous avez reçu un nouvel equipement";
        } elseif ($this->actionType === 'ajout machines') {
            $message = count($equipmentDetails) > 1
                ? "De nouvelles machines ont été ajoutées à votre attribution :"
                : "Une nouvelle machine a été ajoutée à votre attribution :";
        }

        return [
            'type' => $this->actionType,
            'message' => $message,
            'equipments' => $equipmentDetails, // This now contains rich data
            'attributed_by' => $this->userName,
        ];
    }

    /**
     * Get the mail representation of the notification.
     * This method is optional unless 'mail' is in your via() channels.
     *
     * @param object $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $equipmentList = $this->attributedEquipments->map(function ($equipement) {
            return ($equipement->nom_eq ?? 'Inconnu') . ' (N° Inventaire: ' . ($equipement->num_inventaire_eq ?? 'N/A') . ')';
        })->implode("\n- ");

        return (new MailMessage)
            ->subject('Nouvelle Attribution d\'Équipement')
            ->greeting('Bonjour,')
            ->line('Une nouvelle attribution d\'équipement a été effectuée pour vous par ' . $this->userName . '.')
            ->line(count($this->attributedEquipments) > 1
                ? "Les machines suivantes vous ont été attribuées :"
                : "La machine suivante vous a été attribuée :")
            ->line("- " . $equipmentList)
            ->action('Voir mes Attributions', url('/attributions')) // Adjust URL as needed
            ->line('Merci!');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'notification' => $this->toArray($notifiable),
            'created_at' => now()->toDateTimeString(),
        ]);
    }
}
