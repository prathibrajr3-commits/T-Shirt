<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerOrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
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
        $orderNumber = $this->order->order_number;
        $reason = $this->order->customer_cancel_reason;

        return (new MailMessage)
            ->subject("Order #{$orderNumber} Cancellation Confirmation")
            ->greeting("Hello {$notifiable->name},")
            ->line("This email confirms that your order **#{$orderNumber}** has been cancelled.")
            ->line("Cancellation Reason: **{$reason}**")
            ->action('View Order Details', route('orders.show', $this->order->id))
            ->line('If you have any questions or did not authorize this, please contact support immediately.');
    }
}
