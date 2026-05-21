<?php

namespace App\Policies;

use App\Models\TemporaryPhotoUser;
use App\Models\User;

class TemporaryPhotoUserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isResponsibleLevelOne();
    }

    public function view(User $user, TemporaryPhotoUser $temporaryPhotoUser): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isResponsibleLevelOne()
            && (int) $temporaryPhotoUser->owner_responsible_user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isResponsibleLevelOne();
    }

    public function update(User $user, TemporaryPhotoUser $temporaryPhotoUser): bool
    {
        return $this->view($user, $temporaryPhotoUser);
    }

    public function delete(User $user, TemporaryPhotoUser $temporaryPhotoUser): bool
    {
        return $this->view($user, $temporaryPhotoUser);
    }
}
