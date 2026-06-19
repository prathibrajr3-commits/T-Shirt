<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->id === $order->user_id;
    }

    /**
     * Determine whether the user can update the order status.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can cancel the order.
     */
    public function cancel(User $user, Order $order): \Illuminate\Auth\Access\Response
    {
        if ($user->id !== $order->user_id) {
            return \Illuminate\Auth\Access\Response::deny('You do not own this order.');
        }

        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return \Illuminate\Auth\Access\Response::deny('This order can no longer be cancelled.');
        }

        return \Illuminate\Auth\Access\Response::allow();
    }
}
