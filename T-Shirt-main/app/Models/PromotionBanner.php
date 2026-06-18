<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PromotionBanner extends Model
{
    use HasFactory;

    protected $table = 'promotion_banners';

    protected $fillable = [
        'title',
        'heading',
        'subtitle',
        'coupon_code',
        'button_text',
        'button_link',
        'background_image',
        'gradient_color_1',
        'gradient_color_2',
        'is_active',
        'start_date',
        'expiry_date',
        'display_order',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'start_date'   => 'date',
        'expiry_date'  => 'date',
        'display_order' => 'integer',
    ];

    /**
     * Scope: Only active + within date range offers.
     */
    public function scopeVisible($query)
    {
        $today = Carbon::today();
        return $query->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $today);
            });
    }

    /**
     * Check if the offer has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        if (is_null($this->expiry_date)) {
            return false;
        }
        return Carbon::today()->isAfter($this->expiry_date);
    }

    /**
     * Check if the offer has started.
     */
    public function getIsStartedAttribute(): bool
    {
        if (is_null($this->start_date)) {
            return true;
        }
        return Carbon::today()->greaterThanOrEqualTo($this->start_date);
    }

    /**
     * Returns a CSS gradient string from the two gradient colors.
     */
    public function getGradientStyleAttribute(): string
    {
        $c1 = $this->gradient_color_1 ?? '#8b5cf6';
        $c2 = $this->gradient_color_2 ?? '#3b82f6';
        return "linear-gradient(135deg, {$c1}22 0%, {$c2}22 100%)";
    }
}
