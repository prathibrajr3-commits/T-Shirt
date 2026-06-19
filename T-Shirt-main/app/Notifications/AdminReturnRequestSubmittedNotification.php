<?php

namespace App\Notifications;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminReturnRequestSubmittedNotification extends Notification implements ShouldQueue
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
        $customerName = $this->returnRequest->user->name;
        $reason = $this->returnRequest->reason;

        return (new MailMessage)
            ->subject("New Return Request: Order #{$orderNumber}")
            ->greeting("Hello Admin,")
            ->line("Customer **{$customerName}** has submitted a return request for order **#{$orderNumber}**.")
            ->line("Reason for return: **{$reason}**")
            ->action('Manage Return Request', route('admin.returns.show', $this->returnRequest->id));
    }
}
