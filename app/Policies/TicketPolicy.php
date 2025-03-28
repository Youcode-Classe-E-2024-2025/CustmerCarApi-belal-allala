<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Tous les utilisateurs authentifiés peuvent lister les tickets
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Clients peuvent voir leurs propres tickets, agents et admins peuvent voir tous les tickets
        return $user->hasRole('client') && $user->id === $ticket->user_id // Client voit SON ticket
               || $user->hasRole(['agent', 'admin']); // Agents et admins voient TOUS les tickets
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('client'); // Seuls les clients peuvent créer des tickets
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Agents et admins peuvent mettre à jour les tickets
        // Clients NE PEUVENT PAS mettre à jour les tickets (une fois créés)
        return $user->hasRole(['agent', 'admin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Seuls les admins peuvent supprimer les tickets
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can change the status of the model.
     */
    public function changeStatus(User $user, Ticket $ticket): bool // Nouvelle Policy pour changer le statut
    {
        // Seuls les agents et admins peuvent changer le statut des tickets
        return $user->hasRole(['agent', 'admin']);
    }

    // Méthodes utilitaires (inchangées)
    protected function checkRole(User $user, array|string $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }
        return $user->roles()->whereIn('name', $roles)->exists();
    }

    protected function hasRole(User $user, array|string $roles): bool
    {
        return $this->checkRole($user, $roles);
    }
}