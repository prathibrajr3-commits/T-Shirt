<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminOrderCancelledNotification extends Notification implements ShouldQueue
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
        $customerName = $this->order->user->name;
        $reason = $this->order->customer_cancel_reason;

        return (new MailMessage)
            ->subject("Customer Order Cancelled: #{$orderNumber}")
            ->greeting("Hello Admin,")
            ->line("Customer **{$customerName}** has cancelled their order **#{$orderNumber}**.")
            ->line("Cancellation Reason: **{$reason}**")
            ->action('Manage Order', route('admin.orders.show', $this->order->id));
    }
}
