<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vest;

class VestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessVestModule();
    }

    public function view(User $user, Vest $vest): bool
    {
        if ($user->hasGlobalVestScope()) {
            return true;
        }

        if ($user->isResponsible()) {
            return $user->clients()->whereKey($vest->client_id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->canManageAllVests() || $user->isResponsibleLevelOne();
    }

    public function update(User $user, Vest $vest): bool
    {
        if ($user->canManageAllVests()) {
            return true;
        }

        return $user->isResponsibleLevelOne() && $this->vestInPortfolio($user, $vest);
    }

    public function updatePhotos(User $user, Vest $vest): bool
    {
        return $this->update($user, $vest);
    }

    public function delete(User $user, Vest $vest): bool
    {
        return false;
    }

    public function import(User $user): bool
    {
        return $user->canManageAllVests() || $user->isResponsibleLevelOne();
    }

    private function vestInPortfolio(User $user, Vest $vest): bool
    {
        return $user->clients()->whereKey($vest->client_id)->exists();
    }
}
