<?php

namespace App\Notifications;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnRequestRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public ReturnRequest $returnRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(ReturnRequest $returnRequest)
    {
        $this->returnRequest = $returnRequest;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $orderNumber = $this->returnRequest->order->order_number;
        $notes = $this->returnRequest->admin_notes;

        $email = (new MailMessage)
            ->subject("Return Request Rejected: Order #{$orderNumber}")
            ->greeting("Hello {$notifiable->name},")
            ->line("We regret to inform you that your return request for order **#{$orderNumber}** has been rejected.");

        if ($notes) {
            $email->line("Reason/Notes: **{$notes}**");
        }

        return $email->action('View Order Details', route('orders.show', $this->returnRequest->order_id))
            ->line('If you have any questions, please reply directly to this email or contact support.');
    }
}
