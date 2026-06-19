<?php

namespace App\Notifications;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnRequestApprovedNotification extends Notification implements ShouldQueue
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
            ->subject("Return Request Approved: Order #{$orderNumber}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Good news! Your return request for order **#{$orderNumber}** has been approved.");

        if ($notes) {
            $email->line("Admin Notes: **{$notes}**");
        }

        return $email->action('View Order Details', route('orders.show', $this->returnRequest->order_id))
            ->line('Our representative will contact you shortly to arrange the return pickup.');
    }
}
