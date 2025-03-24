<?php

namespace Tests\Unit\Services\Ticket;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Ticket\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase; // Pour réinitialiser la base de données après chaque test
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase; // Utiliser le trait RefreshDatabase pour les tests

    protected TicketService $ticketService;

    protected function setUp(): void
    {
        parent::setUp();

        // Instancier le TicketService avant chaque test
        $this->ticketService = app(TicketService::class);
    }

    /** @test */
    public function it_can_get_all_tickets()
    {
        // 1. Arrange (Préparation) : Créer des données de test (tickets en base de données)
        Ticket::factory()->count(3)->create(); // Crée 3 tickets en utilisant les factories Laravel

        // 2. Act (Action) : Appeler la méthode du service à tester
        $tickets = $this->ticketService->getAllTickets();

        // 3. Assert (Vérification) : Vérifier que le résultat est conforme à ce qui est attendu
        $this->assertCount(3, $tickets); // Vérifier qu'on récupère bien 3 tickets
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $tickets); // Vérifier que c'est bien une instance de Paginator
    }

    /** @test */
    public function it_can_get_ticket_by_id()
    {
        // 1. Arrange : Créer un ticket de test en base de données
        $ticket = Ticket::factory()->create();

        // 2. Act : Appeler la méthode getTicketById avec l'ID du ticket
        $foundTicket = $this->ticketService->getTicketById($ticket->id);

        // 3. Assert : Vérifier que le ticket retrouvé est correct
        $this->assertInstanceOf(Ticket::class, $foundTicket); // Vérifier que c'est bien une instance de Ticket
        $this->assertEquals($ticket->id, $foundTicket->id); // Vérifier que c'est le même ID
        $this->assertEquals($ticket->title, $foundTicket->title); // Vérifier d'autres propriétés (facultatif)
    }

    /** @test */
    public function it_can_create_a_ticket()
    {
        // 1. Arrange : Préparer les données pour la création du ticket et un utilisateur
        $ticketData = [
            'title' => 'Test Ticket',
            'description' => 'Description du ticket de test',
        ];
        $user = User::factory()->create(); // Créer un utilisateur pour associer au ticket

        // 2. Act : Appeler la méthode createTicket du service
        $createdTicket = $this->ticketService->createTicket($ticketData, $user);

        // 3. Assert : Vérifier que le ticket a été créé correctement
        $this->assertInstanceOf(Ticket::class, $createdTicket); // Vérifier que c'est bien une instance de Ticket
        $this->assertEquals($ticketData['title'], $createdTicket->title); // Vérifier le titre
        $this->assertEquals($ticketData['description'], $createdTicket->description); // Vérifier la description
        $this->assertDatabaseHas('tickets', $ticketData); // Vérifier que le ticket est bien en base de données
        $this->assertEquals($user->id, $createdTicket->user_id); // Vérifier l'association avec l'utilisateur
    }

    /** @test */
    public function it_can_update_a_ticket()
    {
        // 1. Arrange : Créer un ticket existant en base de données et les nouvelles données
        $ticket = Ticket::factory()->create(['title' => 'Titre initial']);
        $updatedData = ['title' => 'Titre mis à jour'];

        // 2. Act : Appeler la méthode updateTicket du service
        $updatedTicket = $this->ticketService->updateTicket($ticket->id, $updatedData);

        // 3. Assert : Vérifier que le ticket a été mis à jour correctement
        $this->assertInstanceOf(Ticket::class, $updatedTicket);
        $this->assertEquals($updatedData['title'], $updatedTicket->title); // Vérifier le nouveau titre
        $this->assertDatabaseHas('tickets', $updatedData); // Vérifier que les données mises à jour sont en base
    }

    /** @test */
    public function it_can_delete_a_ticket()
    {
        // 1. Arrange : Créer un ticket existant en base de données
        $ticket = Ticket::factory()->create();

        // 2. Act : Appeler la méthode deleteTicket du service
        $deleted = $this->ticketService->deleteTicket($ticket->id);

        // 3. Assert : Vérifier que la suppression a réussi
        $this->assertTrue($deleted); // Vérifier que la méthode deleteTicket retourne true (succès)
        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]); // Vérifier que le ticket n'est plus en base
    }
}