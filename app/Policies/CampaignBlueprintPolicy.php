<?php

namespace App\Policies;

use App\Models\CampaignBlueprint;
use App\Models\User;

class CampaignBlueprintPolicy
{
    /**
     * Determine if the user can view any blueprints.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view a specific blueprint.
     */
  public function view(User $user, CampaignBlueprint $campaignBlueprint): bool
{
    dd([
        'user_id_connecte'    => $user->id,
        'blueprint_user_id'   => $campaignBlueprint->user_id,
        'sont_egaux'          => $user->id === $campaignBlueprint->user_id,
    ]);

    return $user->id === $campaignBlueprint->user_id;
}

    /**
     * Determine if the user can create a blueprint.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update a specific blueprint.
     */
    public function update(User $user, CampaignBlueprint $campaignBlueprint): bool
    {
        return $user->id === $campaignBlueprint->user_id;
    }

    /**
     * Determine if the user can delete a specific blueprint.
     */
    public function delete(User $user, CampaignBlueprint $campaignBlueprint): bool
    {
        return $user->id === $campaignBlueprint->user_id;
    }
}