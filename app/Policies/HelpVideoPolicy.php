<?php

namespace App\Policies;

use App\Models\HelpVideo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HelpVideoPolicy
{
    /**
     * Determine whether the user can view any models.
     * Todos los usuarios autenticados pueden ver la lista de videos
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Todos los usuarios autenticados pueden ver videos individuales
     */
    public function view(User $user, HelpVideo $helpVideo): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     * Solo administradores pueden crear videos
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     * Solo administradores pueden editar videos
     */
    public function update(User $user, HelpVideo $helpVideo): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     * Solo administradores pueden eliminar videos
     */
    public function delete(User $user, HelpVideo $helpVideo): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     * Solo administradores pueden restaurar videos
     */
    public function restore(User $user, HelpVideo $helpVideo): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Solo administradores pueden eliminar permanentemente videos
     */
    public function forceDelete(User $user, HelpVideo $helpVideo): bool
    {
        return $user->hasRole('admin');
    }
}
