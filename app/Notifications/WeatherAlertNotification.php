<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Alert;

class WeatherAlertNotification extends Notification
{
    // Removed ShouldQueue for synchronous processing on Railway

    protected $alert;

    /**
     * Create a new notification instance.
     */
    public function __construct(Alert $alert)
    {
        $this->alert = $alert;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'alert_id' => $this->alert->id,
            'type' => $this->alert->type,
            'severity' => $this->alert->severity,
            'title' => $this->alert->title,
            'message' => $this->alert->description,
            'internal_message' => $this->alert->internal_message,
            'pump_house_id' => $this->alert->pump_house_id,
            'pump_house_name' => $this->alert->pumpHouse?->name ?? 'Unknown',
            'pump_house_address' => $this->alert->pumpHouse?->address ?? '',
            'created_at' => $this->alert->created_at,
            'rainfall' => $this->alert->rainfall ?? 0,
            'water_level' => $this->alert->water_level ?? null,
        ];
    }
}
