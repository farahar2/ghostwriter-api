<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignBlueprint>
 */
class CampaignBlueprintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'          => User::factory(),
            'name'             => $this->faker->sentence(3),
            'tone_description' => $this->faker->sentence(10),
            'max_characters'   => 280,
            'max_hashtags'     => 1,
            'extra_rules'      => [
                'Toujours commencer par un chiffre',
                'Pas d\'emojis excessifs',
            ],
        ];
    }
}