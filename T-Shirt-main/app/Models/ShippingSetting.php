<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimated_shipping',
        'free_shipping_minimum',
        'shipping_type',
        'custom_message',
        'free_shipping_message',
        'icon_class',
        'background_color',
        'text_color',
        'border_radius',
        'is_active',
        'show_free_shipping_promo',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'show_free_shipping_promo' => 'boolean',
        'estimated_shipping'     => 'decimal:2',
        'free_shipping_minimum'  => 'decimal:2',
    ];

    /**
     * Get the single active shipping settings record (or default values).
     */
    public static function getSettings(): self
    {
        return static::first() ?? new static([
            'estimated_shipping'      => 150.00,
            'free_shipping_minimum'   => 2500.00,
            'shipping_type'           => 'free_above_threshold',
            'custom_message'          => 'Add :amount more for FREE shipping!',
            'free_shipping_message'   => '🎉 You\'ve unlocked FREE shipping!',
            'icon_class'              => 'fa-solid fa-truck-fast',
            'background_color'        => 'transparent',
            'text_color'              => '#ffffff',
            'border_radius'           => '0.5rem',
            'is_active'               => true,
            'show_free_shipping_promo' => true,
        ]);
    }

    /**
     * Calculate the shipping cost for a given cart subtotal.
     */
    public function calculateShipping(float $subtotal): float
    {
        if ($this->shipping_type === 'fixed') {
            return (float) $this->estimated_shipping;
        }

        // free_above_threshold
        if ($subtotal >= (float) $this->free_shipping_minimum) {
            return 0.00;
        }

        return (float) $this->estimated_shipping;
    }

    /**
     * Build the promotion message with the remaining amount filled in.
     */
    public function buildPromoMessage(float $subtotal): string
    {
        $remaining = max(0, (float) $this->free_shipping_minimum - $subtotal);
        $formatted = '₹' . number_format($remaining, 2);
        return str_replace(':amount', $formatted, $this->custom_message);
    }
}
