<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $client;
    protected $agent;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'client']);
        Role::create(['name' => 'agent']);
        Role::create(['name' => 'admin']);

        $this->client = User::factory()->asClient()->create();
        $this->agent = User::factory()->asAgent()->create();
        $this->admin = User::factory()->asAdmin()->create();
    }

    /** @test */
    public function client_can_create_ticket_with_valid_data()
    {
        Sanctum::actingAs($this->client);
        
        $response = $this->postJson('/api/tickets', [
            'title' => 'Problème technique',
            'description' => 'Je ne peux pas accéder à mon compte'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'title', 'description', 
                'status', 'user_id', 'created_at'
            ]);
    }

    /** @test */
    public function ticket_creation_requires_title_and_description()
    {
        Sanctum::actingAs($this->client);
        
        $response = $this->postJson('/api/tickets', [
            'title' => '',
            'description' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description']);
    }

    /** @test */
    public function agent_can_view_all_tickets()
    {
        Ticket::factory()->count(5)->create();

        Sanctum::actingAs($this->agent);
        $response = $this->getJson('/api/tickets');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function client_can_only_view_their_own_tickets()
    {
        $otherClient = User::factory()->asClient()->create();
        
        Ticket::factory()->create(['user_id' => $this->client->id]);
        Ticket::factory()->create(['user_id' => $otherClient->id]);

        Sanctum::actingAs($this->client);
        $response = $this->getJson('/api/tickets');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_id', $this->client->id);
    }

    /** @test */
    public function admin_can_view_all_tickets()
    {
        Ticket::factory()->count(3)->create();

        Sanctum::actingAs($this->admin);
        $response = $this->getJson('/api/tickets');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function client_can_update_their_own_ticket()
    {
        $ticket = Ticket::factory()->create(['user_id' => $this->client->id]);

        Sanctum::actingAs($this->client);
        $response = $this->putJson("/api/tickets/{$ticket->id}", [
            'title' => 'Titre mis à jour'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('title', 'Titre mis à jour');
    }

    /** @test */
    public function client_cannot_update_other_clients_tickets()
    {
        $otherClient = User::factory()->asClient()->create();
        $ticket = Ticket::factory()->create(['user_id' => $otherClient->id]);

        Sanctum::actingAs($this->client);
        $response = $this->putJson("/api/tickets/{$ticket->id}", [
            'title' => 'Tentative de modification'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function agent_can_update_assigned_tickets()
    {
        $ticket = Ticket::factory()->create(['agent_id' => $this->agent->id]);

        Sanctum::actingAs($this->agent);
        $response = $this->putJson("/api/tickets/{$ticket->id}", [
            'status' => 'en_cours'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'en_cours');
    }

    /** @test */
    public function admin_can_update_any_ticket()
    {
        $ticket = Ticket::factory()->create();

        Sanctum::actingAs($this->admin);
        $response = $this->putJson("/api/tickets/{$ticket->id}", [
            'status' => 'résolu'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'résolu');
    }

    /** @test */
    public function client_can_delete_their_own_open_ticket()
    {
        $ticket = Ticket::factory()->create([
            'user_id' => $this->client->id,
            'status' => 'ouvert'
        ]);

        Sanctum::actingAs($this->client);
        $response = $this->deleteJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Ticket deleted successfully']);
    }

    /** @test */
    public function client_cannot_delete_closed_tickets()
    {
        $ticket = Ticket::factory()->create([
            'user_id' => $this->client->id,
            'status' => 'fermé'
        ]);

        Sanctum::actingAs($this->client);
        $response = $this->deleteJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_delete_any_ticket()
    {
        $ticket = Ticket::factory()->create();

        Sanctum::actingAs($this->admin);
        $response = $this->deleteJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function can_filter_tickets_by_status()
    {
        Ticket::factory()->create(['status' => 'ouvert']);
        Ticket::factory()->create(['status' => 'fermé']);

        Sanctum::actingAs($this->agent);
        $response = $this->getJson('/api/tickets?status=ouvert');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'ouvert');
    }

    /** @test */
    public function can_search_tickets_by_keyword()
    {
        Ticket::factory()->create(['title' => 'Problème urgent']);
        Ticket::factory()->create(['title' => 'Bug mineur']);

        Sanctum::actingAs($this->agent);
        $response = $this->getJson('/api/tickets?search=urgent');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Problème urgent');
    }

    /** @test */
    public function tickets_list_is_paginated()
    {
        Ticket::factory()->count(15)->create();

        Sanctum::actingAs($this->admin);
        $response = $this->getJson('/api/tickets?per_page=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data',
                'links',
                'meta'
            ]);
    }

    /** @test */
    public function prevent_xss_injection_in_tickets()
    {
        $maliciousInput = '<script>alert("hack")</script>';

        Sanctum::actingAs($this->client);
        $response = $this->postJson('/api/tickets', [
            'title' => $maliciousInput,
            'description' => $maliciousInput
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tickets', [
            'title' => $maliciousInput, // Ou vérifier l'échappement
            'description' => $maliciousInput
        ]);
    }
}