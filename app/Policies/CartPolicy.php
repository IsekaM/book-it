<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Cart;

class CartPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function show(User $user): bool
    {
        return $user->isMember();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function add(User $user): bool
    {
        return $user->isMember();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function remove(User $user): bool
    {
        return $user->isMember();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function checkout(User $user, Cart $cart): bool
    {
        return $user->isMember() && $user->id == $cart->user_id;
    }
}
