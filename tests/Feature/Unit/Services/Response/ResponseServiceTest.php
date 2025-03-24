<?php

namespace Tests\Unit\Services\Response;

use App\Models\Response;
use App\Models\Ticket; // Importez le modèle Ticket
use App\Models\User;   // Importez le modèle User
use App\Services\Response\ResponseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponseServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ResponseService $responseService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créez un utilisateur et un ticket de test si nécessaire
        $this->user = User::factory()->create();
        $this->ticket = Ticket::factory()->create();
        
        $this->responseService = new ResponseService();
    }

    /** @test */
    public function it_can_get_responses_by_ticket_id()
    {
        // 1. Arrange : Créer un ticket et des réponses associées
        $ticket = Ticket::factory()->create();
        Response::factory()->count(3)->create(['ticket_id' => $ticket->id]); // 3 réponses pour ce ticket
        Response::factory()->count(2)->create(); // 2 réponses pour un autre ticket (pour vérifier qu'elles ne sont pas récupérées)

        // 2. Act : Appeler la méthode getResponsesByTicketId
        $responses = $this->responseService->getResponsesByTicketId($ticket->id);

        // 3. Assert : Vérifier qu'on récupère bien 3 réponses pour le ticket spécifié
        $this->assertCount(3, $responses);
        foreach ($responses as $response) {
            $this->assertEquals($ticket->id, $response->ticket_id); // Vérifier que chaque réponse est bien liée au bon ticket
        }
    }

    /** @test */
    public function it_can_get_response_by_id()
    {
        // 1. Arrange : Créer une réponse de test
        $response = Response::factory()->create();

        // 2. Act : Appeler la méthode getResponseById
        $foundResponse = $this->responseService->getResponseById($response->id);

        // 3. Assert : Vérifier que la réponse retrouvée est correcte
        $this->assertInstanceOf(Response::class, $foundResponse);
        $this->assertEquals($response->id, $foundResponse->id);
        $this->assertEquals($response->content, $foundResponse->content); // Vérifier d'autres propriétés (facultatif)
    }

    /** @test */
    public function it_can_create_a_response()
    {
        // 1. Arrange : Préparer les données pour la création de la réponse, un ticket et un utilisateur
        $ticket = Ticket::factory()->create();
        $user = User::factory()->create();
        $responseData = [
            'content' => 'Ceci est le contenu de la réponse de test.',
        ];

        // 2. Act : Appeler la méthode createResponse
        $createdResponse = $this->responseService->createResponse($responseData, $ticket, $user);

        // 3. Assert : Vérifier que la réponse a été créée correctement
        $this->assertInstanceOf(Response::class, $createdResponse);
        $this->assertEquals($responseData['content'], $createdResponse->content);
        $this->assertEquals($ticket->id, $createdResponse->ticket_id); // Vérifier l'association avec le ticket
        $this->assertEquals($user->id, $createdResponse->user_id);     // Vérifier l'association avec l'utilisateur
        $this->assertDatabaseHas('responses', $responseData + ['ticket_id' => $ticket->id, 'user_id' => $user->id]); // Vérifier en base
    }

    /** @test */
    public function it_can_update_a_response()
    {
        // 1. Arrange : Créer une réponse existante et préparer les données de mise à jour
        $response = Response::factory()->create(['content' => 'Contenu initial']);
        $updatedData = ['content' => 'Contenu mis à jour'];

        // 2. Act : Appeler la méthode updateResponse
        $updatedResponse = $this->responseService->updateResponse($response->id, $updatedData);

        // 3. Assert : Vérifier que la réponse a été mise à jour correctement
        $this->assertInstanceOf(Response::class, $updatedResponse);
        $this->assertEquals($updatedData['content'], $updatedResponse->content);
        $this->assertDatabaseHas('responses', $updatedData); // Vérifier les données mises à jour en base
    }

    /** @test */
    public function it_can_delete_a_response()
    {
        // 1. Arrange : Créer une réponse existante
        $response = Response::factory()->create();

        // 2. Act : Appeler la méthode deleteResponse
        $deleted = $this->responseService->deleteResponse($response->id);

        // 3. Assert : Vérifier que la suppression a réussi
        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('responses', ['id' => $response->id]); // Vérifier que la réponse n'est plus en base
    }
}