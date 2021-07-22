<?php

namespace App\Policies;

use App\Paper;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class PaperPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the paper.
     *
     * @return mixed
     */
    public function view(User $user, Paper $paper)
    {
        return
            $user->isAdmin() or
            $user->isSetterFor($paper->course) or
            $user->isModeratorFor($paper->course) or
            $user->isExternalFor($paper->course);
    }

    /**
     * Determine whether the user can create papers.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the paper.
     *
     * @return mixed
     */
    public function update(User $user, Paper $paper)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the paper.
     *
     * @return mixed
     */
    public function delete(User $user, Paper $paper)
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
     *
     * @return mixed
     */
    public function restore(User $user, Paper $paper)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the paper.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Paper $paper)
    {
        //
    }
}
