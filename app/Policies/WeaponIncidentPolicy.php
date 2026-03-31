<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WeaponIncident;

class WeaponIncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isResponsible() || $user->isAuditor();
    }

    public function view(User $user, WeaponIncident $weaponIncident): bool
    {
        if ($user->isAdmin() || $user->isAuditor()) {
            return true;
        }

        return $user->isResponsible()
            && $weaponIncident->weapon?->activeClientAssignment?->responsible_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, WeaponIncident $weaponIncident): bool
    {
        return $user->isAdmin();
    }

    public function close(User $user, WeaponIncident $weaponIncident): bool
    {
        return $user->isAdmin();
    }

    public function downloadAttachment(User $user, WeaponIncident $weaponIncident): bool
    {
        return $this->view($user, $weaponIncident);
    }

    public function downloadUpdateAttachment(User $user, WeaponIncident $weaponIncident): bool
    {
        return $this->view($user, $weaponIncident);
    }
}
