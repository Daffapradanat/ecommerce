<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function view(User $user, Product $product)
    {
        return true;
    }

    public function update(User $user, Product $product)
    {
        return $user->role->permissions && in_array('products.edit', $user->role->permissions);
    }

    public function delete(User $user, Product $product)
    {
        return $user->role->permissions && in_array('products.delete', $user->role->permissions);
    }
}
