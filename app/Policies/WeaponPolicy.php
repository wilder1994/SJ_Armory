<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Weapon;

class WeaponPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isResponsible() || $user->isAuditor();
    }

    public function view(User $user, Weapon $weapon): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Weapon $weapon): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Weapon $weapon): bool
    {
        return $user->isAdmin();
    }
}
