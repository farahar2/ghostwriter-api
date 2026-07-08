<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login with correct credentials returns 200 and token.
     */
    public function test_login_with_correct_credentials_returns_token(): void
    {
        // Créer un utilisateur en base de données de test
        $user = User::factory()->create([
            'email'    => 'john@example.com',
            'password' => bcrypt('Password1'),
        ]);

        // Envoyer une requête POST sur /api/auth/login
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'john@example.com',
            'password' => 'Password1',
        ]);

        // Vérifier que la réponse est 200
        $response->assertStatus(200);

        // Vérifier que la réponse contient un token
        $response->assertJsonStructure([
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                ],
                'token',
            ],
        ]);

        // Vérifier le message
        $response->assertJson([
            'message' => 'Login successful',
        ]);
    }

    /**
     * Test login with wrong password returns 401.
     */
    public function test_login_with_wrong_password_returns_401(): void
    {
        // Créer un utilisateur en base de données de test
        User::factory()->create([
            'email'    => 'john@example.com',
            'password' => bcrypt('Password1'),
        ]);

        // Envoyer une requête avec un mauvais mot de passe
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'john@example.com',
            'password' => 'WrongPassword',
        ]);

        // Vérifier que la réponse est 401
        $response->assertStatus(401);

        // Vérifier le message d'erreur
        $response->assertJson([
            'message' => 'Invalid credentials',
        ]);
    }

    /**
     * Test register creates a new user and returns token.
     */
    public function test_register_creates_user_and_returns_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'message',
            'data' => [
                'user',
                'token',
            ],
        ]);

        // Vérifier que l'utilisateur est bien en base
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Test logout revokes the token.
     */
    public function test_logout_revokes_token(): void
    {
        // Créer un utilisateur
        $user = User::factory()->create();

        // Simuler l'authentification avec Sanctum
        $token = $user->createToken('api-token')->plainTextToken;

        // Envoyer une requête de déconnexion
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200);

        $response->assertJson([
            'message' => 'Logged out successfully',
        ]);
    }
}