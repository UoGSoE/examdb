<?php

namespace App\Policies;

use App\User;
use App\Paper;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaperPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the paper.
     *
     * @param  \App\User  $user
     * @param  \App\Paper  $paper
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
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the paper.
     *
     * @param  \App\User  $user
     * @param  \App\Paper  $paper
     * @return mixed
     */
    public function update(User $user, Paper $paper)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the paper.
     *
     * @param  \App\User  $user
     * @param  \App\Paper  $paper
     * @return mixed
     */
    public function delete(User $user, Paper $paper)
    {
        if ($paper->created_at->diffInMinutes(now()) > config('exampapers.delete_paper_limit_minutes')) {
            return false;
        }
        return $paper->user_id == $user->id;
    }

    /**
     * Determine whether the user can restore the paper.
     *
     * @param  \App\User  $user
     * @param  \App\Paper  $paper
     * @return mixed
     */
    public function restore(User $user, Paper $paper)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the paper.
     *
     * @param  \App\User  $user
     * @param  \App\Paper  $paper
     * @return mixed
     */
    public function forceDelete(User $user, Paper $paper)
    {
        //
    }
}
