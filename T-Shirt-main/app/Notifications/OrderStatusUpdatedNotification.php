<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification implements ShouldQueue
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
     *
     * @return array<int, string>
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
        $status = $this->order->status;
        $orderNumber = $this->order->order_number;

        $subject = match ($status) {
            'confirmed' => "Order #{$orderNumber} Confirmed",
            'shipped' => "Order #{$orderNumber} Shipped",
            'delivered' => "Order #{$orderNumber} Delivered",
            'cancelled' => "Order #{$orderNumber} Cancelled",
            default => "Order #{$orderNumber} Status Updated",
        };

        $greeting = "Hello " . $notifiable->name . ",";

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting);

        switch ($status) {
            case 'confirmed':
                $mailMessage->line("We are happy to inform you that your order **#{$orderNumber}** has been confirmed!")
                    ->line("Our team is currently preparing your package for shipment.");
                break;

            case 'shipped':
                $carrier = $this->order->shipping_provider ?? 'our courier partner';
                $tracking = $this->order->tracking_number;
                $mailMessage->line("Great news! Your order **#{$orderNumber}** has been shipped via **{$carrier}**.");
                if ($tracking) {
                    $mailMessage->line("Tracking Number: **{$tracking}**");
                }
                break;

            case 'delivered':
                $mailMessage->line("Your order **#{$orderNumber}** has been delivered successfully.")
                    ->line("Thank you for shopping with us! We hope you love your new gear.");
                break;

            case 'cancelled':
                $mailMessage->line("We regret to inform you that your order **#{$orderNumber}** has been cancelled.");
                if ($this->order->notes) {
                    $mailMessage->line("Reason/Notes: {$this->order->notes}");
                }
                break;

            default:
                $mailMessage->line("The status of your order **#{$orderNumber}** has been updated to: **" . ucfirst($status) . "**.");
                break;
        }

        return $mailMessage
            ->action('Track & View Order', route('orders.show', $this->order->id))
            ->line('If you have any questions, please reply to this email to reach our support team.');
    }
}
