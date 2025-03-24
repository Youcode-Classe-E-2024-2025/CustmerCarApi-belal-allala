<?php

namespace App\Services\Ticket;

use App\Models\Ticket;
use App\Models\User; 

class TicketService
{
    /**
     * Récupère tous les tickets avec pagination (si nécessaire).
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllTickets()
    {
        return Ticket::paginate(10); 
    }

    /**
     * Récupère un ticket par son ID.
     *
     * @param int $ticketId
     * @return \App\Models\Ticket|null
     */
    public function getTicketById(int $ticketId): ?Ticket
    {
        return Ticket::find($ticketId);
    }

    /**
     * Crée un nouveau ticket.
     *
     * @param array $ticketData
     * @param User $user // Exemple: on passe l'utilisateur connecté
     * @return \App\Models\Ticket
     */
    public function createTicket(array $data, User $user): Ticket
    {
        $ticket = new Ticket([
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => 'open', // Statut par défaut
            'user_id' => $user->id,
        ]);
        $ticket->save();
        return $ticket;
    }

    /**
     * Met à jour un ticket existant.
     *
     * @param int $ticketId
     * @param array $ticketData
     * @return \App\Models\Ticket|null
     */
    public function updateTicket(int $ticketId, array $ticketData): ?Ticket
    {
        $ticket = $this->getTicketById($ticketId); // Réutilise le service pour récupérer le ticket
        if (!$ticket) {
            return null; // Ou lancer une exception, selon votre gestion des erreurs
        }

        $ticket->fill($ticketData); // Fill met à jour les champs du modèle avec les données
        $ticket->save();

        return $ticket;
    }

    /**
     * Supprime un ticket.
     *
     * @param int $ticketId
     * @return bool
     */
    public function deleteTicket(int $ticketId): bool
    {
        $ticket = $this->getTicketById($ticketId);
        if (!$ticket) {
            return false; // Ou lancer une exception
        }

        return $ticket->delete();
    }
}