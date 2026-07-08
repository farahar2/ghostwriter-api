<?php

namespace Tests\Feature;

use App\Jobs\ProcessRawContentJob;
use App\Models\CampaignBlueprint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RawContentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test POST /api/content/repurpose returns 202 and dispatches job.
     */
    public function test_repurpose_returns_202_and_dispatches_job(): void
    {
        // Fake la queue — aucun vrai appel à Grok
        Queue::fake();

        // Créer un utilisateur et un blueprint
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $blueprint = CampaignBlueprint::factory()->create([
            'user_id' => $user->id,
        ]);

        // Envoyer le contenu brut
        $response = $this->postJson('/api/content/repurpose', [
            'blueprint_id' => $blueprint->id,
            'content'      => 'Aujourd\'hui j\'ai appris que Laravel 13 a introduit une nouvelle façon de gérer les jobs asynchrones avec les queues. La méthode dispatch() permet d\'envoyer un job en arrière-plan sans bloquer la réponse HTTP.',
        ]);

        // Vérifier que la réponse est 202 immédiat
        $response->assertStatus(202);

        // Vérifier le message
        $response->assertJson([
            'message' => 'Content submitted successfully. Processing in background.',
        ]);

        // Vérifier que le Job a bien été dispatché sans appeler Grok
        Queue::assertPushed(ProcessRawContentJob::class);
    }

    /**
     * Test POST /api/content/repurpose with missing content returns 422.
     */
    public function test_repurpose_with_missing_content_returns_422(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $blueprint = CampaignBlueprint::factory()->create([
            'user_id' => $user->id,
        ]);

        // Envoyer sans le champ content
        $response = $this->postJson('/api/content/repurpose', [
            'blueprint_id' => $blueprint->id,
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['content']);
    }

    /**
     * Test POST /api/content/repurpose without token returns 401.
     */
    public function test_repurpose_without_token_returns_401(): void
    {
        $response = $this->postJson('/api/content/repurpose', [
            'blueprint_id' => 1,
            'content'      => 'Some content here for testing purposes.',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test POST /api/content/repurpose with blueprint of another user returns 422.
     */
    public function test_repurpose_with_other_user_blueprint_returns_422(): void
    {
        Queue::fake();

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Blueprint appartient à userA
        $blueprint = CampaignBlueprint::factory()->create([
            'user_id' => $userA->id,
        ]);

        // userB essaie d'utiliser le blueprint de userA
        Sanctum::actingAs($userB);

        $response = $this->postJson('/api/content/repurpose', [
            'blueprint_id' => $blueprint->id,
            'content'      => 'Some content here for testing purposes.',
        ]);

        // Doit retourner 422 car le blueprint n'appartient pas à userB
        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['blueprint_id']);
    }
}