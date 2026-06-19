<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED = 'fixed';

    const TYPES = [
        self::TYPE_PERCENTAGE,
        self::TYPE_FIXED,
    ];

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'minimum_order_amount',
        'maximum_discount_amount',
        'usage_limit',
        'usage_count',
        'usage_per_customer',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
            'discount_value' => 'decimal:2',
            'minimum_order_amount' => 'decimal:2',
            'maximum_discount_amount' => 'decimal:2',
        ];
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Validate if the coupon is valid for the user and subtotal.
     */
    public function isValidForUser($user, float $subtotal, ?string &$error = null): bool
    {
        if (!$this->is_active) {
            $error = 'Coupon inactive';
            return false;
        }

        $now = now();
        if ($this->start_date && $this->start_date->isFuture()) {
            $error = 'Coupon inactive'; // or not started yet
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            $error = 'Coupon expired';
            return false;
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            $error = 'Coupon usage limit reached';
            return false;
        }

        if ($this->usage_per_customer !== null && $user) {
            $userUsages = $this->usages()->where('user_id', $user->id)->count();
            if ($userUsages >= $this->usage_per_customer) {
                $error = 'Coupon usage limit reached for this customer';
                return false;
            }
        }

        if ($subtotal < $this->minimum_order_amount) {
            $error = 'Minimum order amount not met';
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount based on subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        $discount = 0.00;

        if ($this->discount_type === self::TYPE_FIXED) {
            $discount = (float) $this->discount_value;
        } elseif ($this->discount_type === self::TYPE_PERCENTAGE) {
            $discount = ($this->discount_value / 100) * $subtotal;
            if ($this->maximum_discount_amount !== null) {
                $discount = min($discount, (float) $this->maximum_discount_amount);
            }
        }

        // Must not exceed subtotal
        $discount = min($discount, $subtotal);

        // Prevent negative values
        $discount = max(0.00, $discount);

        return round($discount, 2);
    }
}
