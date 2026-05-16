<?php

namespace App\Notifications;

use App\Models\PendingRegistration;
use Illuminate\Notifications\Notification;

class NewRegistrationNotification extends Notification
{
    public function __construct(private PendingRegistration $pending) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'new_registration',
            'pending_id' => $this->pending->id,
            'name'       => $this->pending->name,
            'email'      => $this->pending->email,
            'phone'      => $this->pending->phone,
            'ip_address' => $this->pending->ip_address,
            'registered_at' => $this->pending->created_at->toIso8601String(),
            'url'        => '/settings/pending-registrations',
        ];
    }
}
