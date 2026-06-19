<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PACKED = 'packed';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_PROCESSING,
        self::STATUS_PACKED,
        self::STATUS_SHIPPED,
        self::STATUS_OUT_FOR_DELIVERY,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
        self::STATUS_REFUNDED,
    ];

    protected $fillable = [
        'user_id',
        'coupon_id',
        'coupon_code',
        'order_number',
        'status',
        'total_amount',
        'discount_amount',
        'payment_method',
        'payment_status',
        'shipping_address',
        'phone',
        'tracking_number',
        'shipping_provider',
        'tracking_url',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'status_changed_at',
        'notes',
        'customer_cancel_reason',
        'customer_cancelled_at',
        'cancelled_by',
    ];

    protected function casts(): array
    {
        return [
            'customer_cancelled_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'status_changed_at' => 'datetime',
            'discount_amount' => 'decimal:2',
        ];
    }

    protected static function booted()
    {
        static::created(function ($order) {
            $order->histories()->create([
                'status' => self::STATUS_PENDING,
                'notes' => 'Order placed successfully.',
            ]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function histories()
    {
        return $this->hasMany(OrderHistory::class)->latest();
    }

    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function returnRequest()
    {
        return $this->hasOne(ReturnRequest::class)->latestOfMany();
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function couponUsages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function canRequestReturn(): bool
    {
        if ($this->status !== self::STATUS_DELIVERED) {
            return false;
        }

        if (!$this->delivered_at || $this->delivered_at->addDays(7)->isPast()) {
            return false;
        }

        $hasActive = $this->returnRequests()
            ->whereIn('status', [ReturnRequest::STATUS_PENDING, ReturnRequest::STATUS_APPROVED, ReturnRequest::STATUS_COMPLETED])
            ->exists();

        return !$hasActive;
    }

    public function statusBadgeClass()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_CONFIRMED => 'bg-info text-dark',
            self::STATUS_PROCESSING => 'bg-primary',
            self::STATUS_PACKED => 'bg-secondary',
            self::STATUS_SHIPPED => 'bg-primary',
            self::STATUS_OUT_FOR_DELIVERY => 'bg-warning text-dark',
            self::STATUS_DELIVERED => 'bg-success',
            self::STATUS_CANCELLED => 'bg-danger',
            self::STATUS_REFUNDED => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    public function isValidTransition($newStatus)
    {
        if ($this->status === $newStatus) {
            return true;
        }

        // Any Status -> Cancelled (unless it is already cancelled, refunded, or delivered)
        if ($newStatus === self::STATUS_CANCELLED) {
            return !in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_REFUNDED, self::STATUS_CANCELLED]);
        }

        $transitions = [
            self::STATUS_PENDING => [self::STATUS_CONFIRMED],
            self::STATUS_CONFIRMED => [self::STATUS_PROCESSING],
            self::STATUS_PROCESSING => [self::STATUS_PACKED],
            self::STATUS_PACKED => [self::STATUS_SHIPPED],
            self::STATUS_SHIPPED => [self::STATUS_OUT_FOR_DELIVERY],
            self::STATUS_OUT_FOR_DELIVERY => [self::STATUS_DELIVERED],
            self::STATUS_DELIVERED => [self::STATUS_REFUNDED],
        ];

        $allowed = $transitions[$this->status] ?? [];
        return in_array($newStatus, $allowed);
    }

    public function updateStatus($newStatus, $notes = null, $shippingProvider = null, $trackingNumber = null, $trackingUrl = null, $updatedBy = null)
    {
        $oldStatus = $this->status;
        $this->status = $newStatus;
        $this->status_changed_at = now();

        if ($newStatus === self::STATUS_SHIPPED) {
            $this->shipped_at = now();
        } elseif ($newStatus === self::STATUS_DELIVERED) {
            $this->delivered_at = now();
            $this->payment_status = 'completed';
        } elseif ($newStatus === self::STATUS_CANCELLED) {
            $this->cancelled_at = now();
            if (!$this->cancelled_by) {
                $this->cancelled_by = 'admin';
            }
        }

        if ($shippingProvider !== null) {
            $this->shipping_provider = $shippingProvider;
        }
        if ($trackingNumber !== null) {
            $this->tracking_number = $trackingNumber;
        }
        if ($trackingUrl !== null) {
            $this->tracking_url = $trackingUrl;
        }
        if ($notes !== null) {
            $this->notes = $notes;
        }

        $this->save();

        $historyNotes = $notes ?: "Order status updated from " . ucfirst($oldStatus) . " to " . ucfirst($newStatus) . ".";
        
        $this->histories()->create([
            'status' => $newStatus,
            'notes' => $historyNotes,
            'updated_by' => $updatedBy,
        ]);
    }

    public function getMilestones()
    {
        $milestones = [
            ['name' => 'Placed', 'status' => self::STATUS_PENDING, 'completed' => true],
            ['name' => 'Confirmed', 'status' => self::STATUS_CONFIRMED, 'completed' => false],
            ['name' => 'Processing', 'status' => self::STATUS_PROCESSING, 'completed' => false],
            ['name' => 'Packed', 'status' => self::STATUS_PACKED, 'completed' => false],
            ['name' => 'Shipped', 'status' => self::STATUS_SHIPPED, 'completed' => false],
            ['name' => 'Delivered', 'status' => self::STATUS_DELIVERED, 'completed' => false],
        ];

        $statusOrder = [
            self::STATUS_PENDING => 1,
            self::STATUS_CONFIRMED => 2,
            self::STATUS_PROCESSING => 3,
            self::STATUS_PACKED => 4,
            self::STATUS_SHIPPED => 5,
            self::STATUS_OUT_FOR_DELIVERY => 5,
            self::STATUS_DELIVERED => 6,
            self::STATUS_CANCELLED => 0,
            self::STATUS_REFUNDED => 0,
        ];

        $currentOrderVal = $statusOrder[$this->status] ?? 1;

        foreach ($milestones as &$milestone) {
            $milestoneVal = $statusOrder[$milestone['status']];
            if ($currentOrderVal >= $milestoneVal && $currentOrderVal > 0) {
                $milestone['completed'] = true;
            }
        }

        return $milestones;
    }
}
