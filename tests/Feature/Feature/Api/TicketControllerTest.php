<?php

namespace Tests\Feature\Api;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum; // Importer Sanctum pour l'authentification

class TicketControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
public function it_can_get_all_tickets()
{
    // 1. Arrange : Créer des tickets de test en base de données
    Ticket::factory()->count(3)->create();

    // 2. Act : Envoyer une requête GET vers l'endpoint /api/tickets
    $response = $this->getJson('/api/tickets');

    // 3. Assert : Vérifier la réponse HTTP et le contenu JSON
    $response->assertStatus(200) // Vérifier le code de statut 200 OK
        ->assertJsonStructure([ // Vérifier la structure JSON de la réponse paginée
            'data' => [
                '*' => [ // '*' pour indiquer que c'est un tableau d'objets
                    'id',
                    'title',
                    'description',
                    'status',
                    'user_id',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);
}

    /** @test */
    public function it_can_get_a_ticket_by_id()
    {
        // 1. Arrange : Créer un ticket de test en base de données
        $ticket = Ticket::factory()->create();

        // 2. Act : Envoyer une requête GET vers l'endpoint /api/tickets/{id}
        $response = $this->getJson("/api/tickets/{$ticket->id}");

        // 3. Assert : Vérifier la réponse HTTP et le contenu JSON
        $response->assertStatus(200)
            ->assertJsonStructure([ // Vérifier la structure JSON du ticket
                'id',
                'title',
                'description',
                'status',
                'user_id',
                'created_at',
                'updated_at',
            ]);
    }

    /** @test */
    public function it_can_create_a_ticket()
    {
        // 1. Arrange : Préparer les données pour la création du ticket et authentifier un utilisateur
        $ticketData = [
            'title' => 'Nouveau ticket de test',
            'description' => 'Description du nouveau ticket de test',
        ];
        $user = User::factory()->create();
        Sanctum::actingAs($user); // Authentifier l'utilisateur avec Sanctum pour ce test

        // 2. Act : Envoyer une requête POST vers l'endpoint /api/tickets
        $response = $this->postJson('/api/tickets', $ticketData);

        // 3. Assert : Vérifier la réponse HTTP et le contenu JSON
        $response->assertStatus(201) // Vérifier le code de statut 201 Created
            ->assertJsonStructure([ // Vérifier la structure JSON du ticket créé
                'id',
                'title',
                'description',
                'status', // Assurez-vous que cette clé est présente
                'user_id',
                'created_at',
                'updated_at',
            ])
            ->assertJsonFragment($ticketData); // Vérifier que les données envoyées sont bien dans la réponse
        $this->assertDatabaseHas('tickets', $ticketData); // Vérifier que le ticket est bien créé en base de données
    }

    /** @test */
    public function it_can_update_a_ticket()
    {
        // 1. Arrange : Créer un ticket existant, préparer les données de mise à jour et authentifier un utilisateur
        $ticket = Ticket::factory()->create();
        $updatedData = [
            'title' => 'Titre mis à jour',
            'description' => 'Description mise à jour',
        ];
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // 2. Act : Envoyer une requête PUT vers l'endpoint /api/tickets/{id}
        $response = $this->putJson("/api/tickets/{$ticket->id}", $updatedData);

        // 3. Assert : Vérifier la réponse HTTP et le contenu JSON
        $response->assertStatus(200)
            ->assertJsonStructure([ // Vérifier la structure JSON du ticket mis à jour
                'id',
                'title',
                'description',
                'status',
                'user_id',
                'created_at',
                'updated_at',
            ])
            ->assertJsonFragment($updatedData); // Vérifier que les données mises à jour sont bien dans la réponse
        $this->assertDatabaseHas('tickets', $updatedData); // Vérifier que les données sont bien mises à jour en base
    }

    /** @test */
    public function it_can_delete_a_ticket()
    {
        // 1. Arrange : Créer un ticket existant et authentifier un utilisateur
        $ticket = Ticket::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // 2. Act : Envoyer une requête DELETE vers l'endpoint /api/tickets/{id}
        $response = $this->deleteJson("/api/tickets/{$ticket->id}");

        // 3. Assert : Vérifier la réponse HTTP et que le ticket est bien supprimé
        $response->assertStatus(200)
            ->assertJsonStructure(['message']); // Vérifier la structure JSON de la réponse (message)
        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]); // Vérifier que le ticket n'est plus en base de données
    }
}