<?php

namespace App\Policies;

use App\Models\GeneratedPost;
use App\Models\User;

class GeneratedPostPolicy
{
    /**
     * Determine if the user can view any generated posts.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view a specific generated post.
     */
    public function view(User $user, GeneratedPost $generatedPost): bool
    {
        return $user->id === $generatedPost->rawContent->user_id;
    }

    /**
     * Determine if the user can update a specific generated post.
     */
    public function update(User $user, GeneratedPost $generatedPost): bool
    {
        return $user->id === $generatedPost->rawContent->user_id;
    }
}