<?php

namespace Tests\Feature;

use App\Models\CampaignBlueprint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BlueprintTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test GET /api/blueprints without token returns 401.
     */
    public function test_get_blueprints_without_token_returns_401(): void
    {
        $response = $this->getJson('/api/blueprints');

        $response->assertStatus(401);

        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    /**
     * Test GET /api/blueprints with valid token returns 200.
     */
    public function test_get_blueprints_with_valid_token_returns_200(): void
    {
        // Créer un utilisateur et l'authentifier avec Sanctum
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Créer quelques blueprints pour cet utilisateur
        CampaignBlueprint::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        // Envoyer la requête
        $response = $this->getJson('/api/blueprints');

        // Vérifier le status 200
        $response->assertStatus(200);

        // Vérifier la structure JSON
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'tone_description',
                    'max_characters',
                    'max_hashtags',
                    'extra_rules',
                    'posts_count',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    /**
     * Test POST /api/blueprints with missing field returns 422.
     */
    public function test_create_blueprint_with_missing_name_returns_422(): void
    {
        // Authentifier un utilisateur
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Envoyer une requête sans le champ obligatoire "name"
        $response = $this->postJson('/api/blueprints', [
            'tone_description' => 'Professionnel',
            'max_characters'   => 280,
        ]);

        // Vérifier le status 422
        $response->assertStatus(422);

        // Vérifier que l'erreur pointe le bon champ
        $response->assertJsonValidationErrors(['name']);
    }

    /**
     * Test POST /api/blueprints creates a blueprint successfully.
     */
    public function test_create_blueprint_returns_201(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/blueprints', [
            'name'             => 'Tech Twitter Blueprint',
            'tone_description' => 'Professionnel mais décontracté',
            'max_characters'   => 280,
            'max_hashtags'     => 1,
            'extra_rules'      => ['Pas d\'emojis', 'Terminer par une question'],
        ]);

        $response->assertStatus(201);

        $response->assertJson([
            'message' => 'Blueprint created successfully',
        ]);

        // Vérifier que le blueprint est bien en base
        $this->assertDatabaseHas('campaign_blueprints', [
            'name'    => 'Tech Twitter Blueprint',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test user cannot access another user's blueprint.
     */
    public function test_user_cannot_access_other_user_blueprint(): void
    {
        // Créer deux utilisateurs
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Créer un blueprint pour userA
        $blueprint = CampaignBlueprint::factory()->create([
            'user_id' => $userA->id,
        ]);

        // Authentifier userB
        Sanctum::actingAs($userB);

        // userB essaie d'accéder au blueprint de userA
        $response = $this->getJson("/api/blueprints/{$blueprint->id}");

        $response->assertStatus(403);
    }
}