<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Equipement;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use Illuminate\Notifications\Messages\BroadcastMessage;

class AttributionRemovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $userName;
    protected $removedEquipments; // This will now hold actual Equipement models (or a collection)
    protected $actionType;

    /**
     * Create a new notification instance.
     * @param string $userName The name of the user who performed the action.
     * @param \Illuminate\Support\Collection|array $removedEquipments A collection or array of Equipement models or their IDs.
     * @param string $actionType The type of removal action.
     */
    public function __construct($userName, $removedEquipments, $actionType = 'retrait machines')
    {
        $this->userName = $userName;
        $this->actionType = $actionType;

        // Ensure $removedEquipments is a collection of Equipement models
        if (is_array($removedEquipments) && !empty($removedEquipments) && is_int($removedEquipments[0])) {
            $this->removedEquipments = Equipement::whereIn('id', $removedEquipments)->get();
        } elseif (is_array($removedEquipments)) {
            $this->removedEquipments = collect($removedEquipments);
        } else {
            $this->removedEquipments = $removedEquipments;
        }
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
        foreach ($this->removedEquipments as $equipment) {
            if ($equipment) { // Defensive check: ensure $equipment is not null
                $equipmentDetails[] = [
                    'id' => $equipment->id,
                    'nom' => $equipment->nom_eq ?? 'N/A',
                    'num_serie' => $equipment->num_serie_eq ?? 'N/A',
                    'num_inventaire' => $equipment->num_inventaire_eq ?? 'N/A',
                ];
            }
        }

        $message = '';
        if ($this->actionType === 'retrait attribution') {
            $message = count($equipmentDetails) > 1
                ? "Une attribution complète vous a été retirée, incluant les machines suivantes :"
                : "Une attribution vous a été retirée pour la machine :";
        } elseif ($this->actionType === 'retrait machines') {
            $message = count($equipmentDetails) > 1
                ? "Certaines machines ont été retirées de votre attribution :"
                : "Une machine a été retirée de votre attribution :";
        }

        return [
            'type' => $this->actionType,
            'message' => $message,
            'equipments' => $equipmentDetails,
            'removed_by' => $this->userName, // Changed to removed_by for clarity
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
    public function toMail(object $notifiable): MailMessage
    {
        $equipmentList = $this->removedEquipments->map(function ($equipement) {
            return ($equipement->nom_eq ?? 'Inconnu') . ' (N° Inventaire: ' . ($equipement->num_inventaire_eq ?? 'N/A') . ')';
        })->implode("\n- ");

        return (new MailMessage)
            ->subject('Retrait d\'Équipement')
            ->greeting('Bonjour,')
            ->line('Une opération de retrait d\'équipement a été effectuée par ' . $this->userName . '.')
            ->line(count($this->removedEquipments) > 1
                ? "Les machines suivantes ont été retirées de votre attribution :"
                : "La machine suivante a été retirée de votre attribution :")
            ->line("- " . $equipmentList)
            ->action('Voir mes Attributions', url('/attributions')) // Adjust URL as needed
            ->line('Merci!');
    }
}
