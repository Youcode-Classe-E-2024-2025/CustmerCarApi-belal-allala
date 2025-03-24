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

    /** @test */
public function it_can_get_tickets_with_pagination()
{
    // 1. Arrange : Créer 30 tickets de test (pour avoir plusieurs pages)
    Ticket::factory()->count(30)->create();

    // 2. Act : GET /api/tickets?per_page=10 (demander 10 tickets par page)
    $response = $this->getJson('/api/tickets?per_page=10');

    // 3. Assert : Vérifier la réponse HTTP et la pagination
    $response->assertStatus(200)
        ->assertJsonCount(10, 'data') // Vérifier qu'on a 10 tickets dans le tableau 'data' (car per_page=10)
        ->assertJsonPath('meta.per_page', 10) // Vérifier que 'per_page' dans les métadonnées est bien 10
        ->assertJsonPath('meta.total', 30)    // Vérifier que le nombre total de tickets (meta.total) est bien 30
        ->assertJsonPath('meta.last_page', 3); // Vérifier qu'il y a bien 3 pages (30 tickets / 10 par page = 3 pages)
}

/** @test */
public function it_defaults_to_10_tickets_per_page_if_per_page_is_not_provided()
{
    // 1. Arrange : Créer 15 tickets de test
    Ticket::factory()->count(15)->create();

    // 2. Act : GET /api/tickets (sans paramètre per_page)
    $response = $this->getJson('/api/tickets');

    // 3. Assert : Vérifier que par défaut, on a 10 tickets par page
    $response->assertStatus(200)
        ->assertJsonCount(10, 'data') // Vérifier qu'on a 10 tickets (par défaut per_page=10)
        ->assertJsonPath('meta.per_page', 10) // Vérifier que 'per_page' dans les métadonnées est bien 10
        ->assertJsonPath('meta.total', 15)    // Vérifier que le nombre total de tickets (meta.total) est bien 15
        ->assertJsonPath('meta.last_page', 2); // Vérifier qu'il y a 2 pages (15 tickets / 10 par page = 1.5 -> arrondi à 2 pages)
}

/** @test */
public function it_can_get_tickets_on_page_2()
{
    // 1. Arrange : Créer 25 tickets de test
    Ticket::factory()->count(25)->create();

    // 2. Act : GET /api/tickets?page=2&per_page=10 (demander la page 2)
    $response = $this->getJson('/api/tickets?page=2&per_page=10');

    // 3. Assert : Vérifier qu'on récupère bien les tickets de la page 2
    $response->assertStatus(200)
        ->assertJsonCount(10, 'data') // Vérifier qu'on a 10 tickets sur la page 2 (per_page=10)
        ->assertJsonPath('meta.current_page', 2) // Vérifier que la page courante (meta.current_page) est bien 2
        ->assertJsonPath('meta.per_page', 10)    // Vérifier que 'per_page' est bien 10
        ->assertJsonPath('meta.total', 25)       // Vérifier que le total est bien 25
        ->assertJsonPath('meta.last_page', 3);    // Vérifier qu'il y a 3 pages (25 tickets / 10 par page = 2.5 -> arrondi à 3 pages)
}

/** @test */
/** @test */
public function it_can_filter_tickets_by_status()
{
    // 1. Arrange
    Ticket::factory()->count(5)->create(['status' => 'open']);
    Ticket::factory()->count(3)->create(['status' => 'pending']);
    Ticket::factory()->count(2)->create(['status' => 'closed']);

    // 2. Act
    $response = $this->getJson('/api/tickets?status=pending');

    // 3. Assert
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'user_id',
                    'created_at',
                    'updated_at'
                ]
            ]
        ])
        ->assertJsonFragment(['status' => 'pending']);
}

/** @test */
public function it_can_search_tickets_by_keyword()
{
    // 1. Arrange
    Ticket::factory()->create(['title' => 'Ticket avec le mot clé "important"', 'description' => '...']);
    Ticket::factory()->create(['title' => 'Autre ticket', 'description' => 'Description contenant "important" ...']);
    Ticket::factory()->count(5)->create(['title' => 'Ticket sans le mot clé', 'description' => '...']);

    // 2. Act
    $response = $this->getJson('/api/tickets?search=important');

    // 3. Assert
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'user_id',
                    'created_at',
                    'updated_at'
                ]
            ]
        ]);
}

/** @test */
public function it_can_combine_pagination_and_filters()
{
    // 1. Arrange
    Ticket::factory()->count(8)->create(['status' => 'open', 'title' => 'Ticket important']);
    Ticket::factory()->count(5)->create(['status' => 'pending', 'title' => 'Ticket important']);
    Ticket::factory()->count(7)->create(['status' => 'closed', 'title' => 'Autre ticket']);

    // 2. Act
    $response = $this->getJson('/api/tickets?status=open&search=important&per_page=5');

    // 3. Assert
    $response->assertStatus(200)
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.total', 8)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'user_id',
                    'created_at',
                    'updated_at'
                ]
            ]
        ])
        ->assertJsonFragment(['status' => 'open']);
}
}