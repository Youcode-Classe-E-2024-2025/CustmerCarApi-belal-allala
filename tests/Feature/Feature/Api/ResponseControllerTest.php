<?php

namespace Tests\Feature\Api;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\Response;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ResponseControllerTest extends TestCase
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
    public function client_can_respond_to_their_ticket()
    {
        $ticket = Ticket::factory()->create(['user_id' => $this->client->id]);

        Sanctum::actingAs($this->client);
        $response = $this->postJson("/api/tickets/{$ticket->id}/responses", [
            'content' => 'Ma réponse'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'content', 'user_id', 'ticket_id']);
    }

    /** @test */
    public function response_requires_content()
    {
        $ticket = Ticket::factory()->create(['user_id' => $this->client->id]);

        Sanctum::actingAs($this->client);
        $response = $this->postJson("/api/tickets/{$ticket->id}/responses", [
            'content' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    /** @test */
    public function agent_can_respond_to_assigned_ticket()
    {
        $ticket = Ticket::factory()->create(['agent_id' => $this->agent->id]);

        Sanctum::actingAs($this->agent);
        $response = $this->postJson("/api/tickets/{$ticket->id}/responses", [
            'content' => 'Réponse officielle'
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function cannot_respond_to_closed_ticket()
    {
        $ticket = Ticket::factory()->create([
            'user_id' => $this->client->id,
            'status' => 'fermé'
        ]);

        Sanctum::actingAs($this->client);
        $response = $this->postJson("/api/tickets/{$ticket->id}/responses", [
            'content' => 'Tentative de réponse'
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_all_responses()
    {
        $ticket = Ticket::factory()->create();
        Response::factory()->count(3)->create(['ticket_id' => $ticket->id]);

        Sanctum::actingAs($this->admin);
        $response = $this->getJson("/api/tickets/{$ticket->id}/responses");

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function client_can_only_view_responses_to_their_tickets()
    {
        $clientTicket = Ticket::factory()->create(['user_id' => $this->client->id]);
        $otherTicket = Ticket::factory()->create();

        Response::factory()->create(['ticket_id' => $clientTicket->id]);
        Response::factory()->create(['ticket_id' => $otherTicket->id]);

        Sanctum::actingAs($this->client);
        $response = $this->getJson("/api/tickets/{$clientTicket->id}/responses");

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    /** @test */
    public function author_can_update_their_response()
    {
        $ticket = Ticket::factory()->create(['user_id' => $this->client->id]);
        $response = Response::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $this->client->id,
            'content' => 'Original'
        ]);

        Sanctum::actingAs($this->client);
        $updateResponse = $this->putJson("/api/responses/{$response->id}", [
            'content' => 'Modifié'
        ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('content', 'Modifié');
    }

    /** @test */
    public function cannot_update_others_responses()
    {
        $otherUser = User::factory()->asClient()->create();
        $response = Response::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($this->client);
        $updateResponse = $this->putJson("/api/responses/{$response->id}", [
            'content' => 'Tentative de modification'
        ]);

        $updateResponse->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_any_response()
    {
        $response = Response::factory()->create();

        Sanctum::actingAs($this->admin);
        $updateResponse = $this->putJson("/api/responses/{$response->id}", [
            'content' => 'Modifié par admin'
        ]);

        $updateResponse->assertStatus(200);
    }

    /** @test */
    public function author_can_delete_their_response()
    {
        $response = Response::factory()->create(['user_id' => $this->client->id]);

        Sanctum::actingAs($this->client);
        $deleteResponse = $this->deleteJson("/api/responses/{$response->id}");

        $deleteResponse->assertStatus(200)
            ->assertJson(['message' => 'Response deleted successfully']);
    }

    /** @test */
    public function cannot_delete_others_responses()
    {
        $otherUser = User::factory()->asClient()->create();
        $response = Response::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($this->client);
        $deleteResponse = $this->deleteJson("/api/responses/{$response->id}");

        $deleteResponse->assertStatus(403);
    }

    /** @test */
    public function admin_can_delete_any_response()
    {
        $response = Response::factory()->create();

        Sanctum::actingAs($this->admin);
        $deleteResponse = $this->deleteJson("/api/responses/{$response->id}");

        $deleteResponse->assertStatus(200);
    }

    /** @test */
    public function prevent_sql_injection_in_responses()
    {
        $ticket = Ticket::factory()->create();
        $maliciousInput = "1'; DROP TABLE users;--";

        Sanctum::actingAs($this->client);
        $response = $this->postJson("/api/tickets/{$ticket->id}/responses", [
            'content' => $maliciousInput
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('responses', [
            'content' => $maliciousInput
        ]);
    }

    /** @test */
    public function response_list_is_paginated()
    {
        $ticket = Ticket::factory()->create();
        Response::factory()->count(15)->create(['ticket_id' => $ticket->id]);

        Sanctum::actingAs($this->admin);
        $response = $this->getJson("/api/tickets/{$ticket->id}/responses?per_page=5");

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data',
                'links',
                'meta'
            ]);
    }
}