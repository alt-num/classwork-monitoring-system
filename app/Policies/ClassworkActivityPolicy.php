<?php

namespace App\Policies;

use App\Models\ClassworkActivity;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClassworkActivityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSecretary();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClassworkActivity $classworkActivity): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSecretary()) {
            return $classworkActivity->created_by === $user->id;
        }

        if ($user->isStudent()) {
            return $classworkActivity->section_id === $user->section_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSecretary();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClassworkActivity $classworkActivity): bool
    {
        return $user->isSecretary() && $classworkActivity->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClassworkActivity $classworkActivity): bool
    {
        return $user->isSecretary() && $classworkActivity->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ClassworkActivity $classworkActivity): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ClassworkActivity $classworkActivity): bool
    {
        return false;
    }
}
