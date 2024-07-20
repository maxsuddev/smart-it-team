<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        return in_array($user->role, ['cashier', 'admin']);
    }

    public function update(User $user)
    {
        return in_array($user->role, ['cashier', 'admin']);
    }

    public function delete(User $user)
    {
        return $user->role === 'admin';
    }
}
