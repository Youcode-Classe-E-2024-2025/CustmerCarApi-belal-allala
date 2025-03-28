<?php

namespace App\Policies;

use App\Models\Response;
use App\Models\Ticket; // Importez le modèle Ticket pour vérifier le statut du ticket
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResponsePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Tous les utilisateurs authentifiés peuvent lister les réponses (à adapter)
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Response $response): bool
    {
        // Par défaut, tout utilisateur authentifié peut voir une réponse (à adapter)
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Ticket $ticket): bool
    {
        // Clients et agents peuvent créer des réponses, SEULEMENT si le ticket est OUVERT ou PENDING
        return $user->hasRole(['client', 'agent']) && in_array($ticket->status, ['open', 'pending']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Response $response): bool
    {
        // Seuls les agents et admins peuvent mettre à jour les réponses
        return $user->hasRole(['agent', 'admin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Response $response): bool
    {
        // Seuls les admins peuvent supprimer les réponses
        return $user->hasRole('admin');
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