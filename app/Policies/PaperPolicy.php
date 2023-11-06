<?php

namespace App\Policies;

use App\Models\Paper;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class PaperPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the paper.
     */
    public function view(User $user, Paper $paper): bool
    {
        return
            $user->isAdmin() or
            $user->isSetterFor($paper->course) or
            $user->isModeratorFor($paper->course) or
            $user->isExternalFor($paper->course);
    }

    /**
     * Determine whether the user can create papers.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the paper.
     */
    public function update(User $user, Paper $paper): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the paper.
     */
    public function delete(User $user, Paper $paper): bool
    {
        if (Auth::user()->isAdmin()) {
            return true;
        }

        if ($paper->created_at->diffInMinutes(now()) > config('exampapers.delete_paper_limit_minutes')) {
            return false;
        }

        return $paper->user_id == $user->id;
    }

    /**
     * Determine whether the user can restore the paper.
     */
    public function restore(User $user, Paper $paper): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the paper.
     */
    public function forceDelete(User $user, Paper $paper): bool
    {
        //
    }
}
