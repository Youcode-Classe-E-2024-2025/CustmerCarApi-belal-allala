<?php

namespace App\Services\Response;

use App\Models\Response;
use App\Models\Ticket; // Importez le modèle Ticket
use App\Models\User;   // Importez le modèle User

class ResponseService
{
    /**
     * Récupère toutes les réponses pour un ticket spécifique.
     *
     * @param int $ticketId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getResponsesByTicketId(int $ticketId)
    {
        return Response::where('ticket_id', $ticketId)->get();
    }

    /**
     * Récupère une réponse par son ID.
     *
     * @param int $responseId
     * @return \App\Models\Response|null
     */
    public function getResponseById(int $responseId): ?Response
    {
        return Response::find($responseId);
    }

    /**
     * Crée une nouvelle réponse pour un ticket.
     *
     * @param array $responseData
     * @param Ticket $ticket
     * @param User $user
     * @return \App\Models\Response
     */
    public function createResponse(array $responseData, Ticket $ticket, User $user): Response
    {
        $response = new Response($responseData);
        $response->ticket()->associate($ticket); // Associe la réponse au ticket
        $response->user()->associate($user);     // Associe la réponse à l'utilisateur
        $response->save();

        return $response;
    }

    /**
     * Met à jour une réponse existante.
     *
     * @param int $responseId
     * @param array $responseData
     * @return \App\Models\Response|null
     */
    public function updateResponse(int $responseId, array $responseData): ?Response
    {
        $response = $this->getResponseById($responseId);
        if (!$response) {
            return null;
        }

        $response->fill($responseData);
        $response->save();

        return $response;
    }

    /**
     * Supprime une réponse.
     *
     * @param int $responseId
     * @return bool
     */
    public function deleteResponse(int $responseId): bool
    {
        $response = $this->getResponseById($responseId);
        if (!$response) {
            return false;
        }

        return $response->delete();
    }
}