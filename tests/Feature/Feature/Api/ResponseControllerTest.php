<?php

namespace Tests\Feature\Api;

use App\Models\Response;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ResponseControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_responses_for_a_ticket()
    {
        // 1. Arrange : Créer un ticket et des réponses associées
        $ticket = Ticket::factory()->create();
        Response::factory()->count(3)->create(['ticket_id' => $ticket->id]);

        // 2. Act : GET /api/tickets/{ticket}/responses
        $response = $this->getJson("/api/tickets/{$ticket->id}/responses");

        // 3. Assert : Vérifier la réponse HTTP et le contenu JSON
        $response->assertStatus(200)
            ->assertJsonCount(3, '*') // Vérifier qu'on a un tableau JSON avec 3 réponses
            ->assertJsonStructure([ // Vérifier la structure JSON de chaque réponse
                '*' => [
                    'id',
                    'content',
                    'ticket_id',
                    'user_id',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    /** @test */
    public function it_can_get_a_response_by_id()
    {
        // 1. Arrange : Créer une réponse de test
        $response = Response::factory()->create();

        // 2. Act : GET /api/responses/{response}
        $response = $this->getJson("/api/responses/{$response->id}");

        // 3. Assert : Vérifier la réponse HTTP et le contenu JSON
        $response->assertStatus(200)
            ->assertJsonStructure([ // Vérifier la structure JSON de la réponse
                'id',
                'content',
                'ticket_id',
                'user_id',
                'created_at',
                'updated_at',
            ]);
    }

    /** @test */
    public function it_can_create_a_response_for_a_ticket()
    {
        // 1. Arrange : Créer un ticket, un utilisateur authentifié et préparer les données de la réponse
        $ticket = Ticket::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $responseData = [
            'content' => 'Contenu de la réponse de test',
        ];

        // 2. Act : POST /api/tickets/{ticket}/responses
        $response = $this->postJson("/api/tickets/{$ticket->id}/responses", $responseData);

        // 3. Assert : Vérifier la réponse HTTP et le contenu JSON
        $response->assertStatus(201) // 201 Created
            ->assertJsonStructure([ // Vérifier la structure JSON de la réponse créée
                'id',
                'content',
                'ticket_id',
                'user_id',
                'created_at',
                'updated_at',
            ])
            ->assertJsonFragment($responseData); // Vérifier que les données envoyées sont bien dans la réponse
        $this->assertDatabaseHas('responses', $responseData + ['ticket_id' => $ticket->id, 'user_id' => $user->id]); // Vérifier en base
    }

    /** @test */
    public function it_can_update_a_response()
    {
        // 1. Arrange : Créer une réponse existante, un utilisateur authentifié et préparer les données de mise à jour
        $response = Response::factory()->create(['content' => 'Contenu initial']);
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $updatedData = [
            'content' => 'Contenu mis à jour',
        ];

        // 2. Act : PUT /api/responses/{response}
        $response = $this->putJson("/api/responses/{$response->id}", $updatedData);

        // 3. Assert : Vérifier la réponse HTTP et le contenu JSON
        $response->assertStatus(200)
            ->assertJsonStructure([ // Vérifier la structure JSON de la réponse mise à jour
                'id',
                'content',
                'ticket_id',
                'user_id',
                'created_at',
                'updated_at',
            ])
            ->assertJsonFragment($updatedData); // Vérifier que les données mises à jour sont bien dans la réponse
        $this->assertDatabaseHas('responses', $updatedData); // Vérifier en base
    }

    /** @test */
    public function it_can_delete_a_response()
    {
        // 1. Arrange : Créer une réponse existante et authentifier un utilisateur
        $responseToDelete = Response::factory()->create(); // Renommez la variable pour plus de clarté
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // 2. Act : DELETE /api/responses/{response}
        $httpResponse = $this->deleteJson("/api/responses/{$responseToDelete->id}");

        // 3. Assert : Vérifier la réponse HTTP et la suppression
        $httpResponse->assertStatus(200)
            ->assertJsonStructure(['message']); // Vérifier la structure JSON (message)
        $this->assertDatabaseMissing('responses', ['id' => $responseToDelete->id]); // Utilisez l'ID de l'objet original
    }
}