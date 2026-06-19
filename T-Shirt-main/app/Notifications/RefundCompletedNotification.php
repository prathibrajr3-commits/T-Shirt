<?php

namespace App\Notifications;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundCompletedNotification extends Notification implements ShouldQueue
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
        $amount = $this->returnRequest->refund_amount;
        $ref = $this->returnRequest->refund_reference;

        $email = (new MailMessage)
            ->subject("Refund Completed: Order #{$orderNumber}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your refund for order **#{$orderNumber}** has been processed and completed successfully.");

        if ($amount) {
            $email->line("Refunded Amount: **₹" . number_format($amount, 2) . "**");
        }

        if ($ref) {
            $email->line("Transaction Reference: **{$ref}**");
        }

        return $email->action('View Order Details', route('orders.show', $this->returnRequest->order_id))
            ->line('The amount should reflect in your account within 5-7 business days.');
    }
}
