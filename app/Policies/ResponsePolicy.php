<?php

namespace App\Policies;

use App\Models\Response;
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
        return true; // Par défaut, tout utilisateur authentifié peut lister les réponses (à adapter)
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Response $response): bool
    {
        return true; // Par défaut, tout utilisateur authentifié peut voir une réponse (à adapter)
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Par défaut, tout utilisateur authentifié peut créer une réponse (à adapter)
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Response $response): bool
    {
        // Seuls les agents ou admins peuvent mettre à jour une réponse (exemple)
        return $user->hasRole(['agent', 'admin']); // Réutilise la méthode hasRole du User model
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Response $response): bool
    {
        // Seuls les admins peuvent supprimer une réponse (exemple)
        return $user->hasRole('admin'); // Réutilise la méthode hasRole du User model
    }

     // Méthode utilitaire pour vérifier si un utilisateur a un ou plusieurs rôles (plus lisible) - COPIÉE DE TicketPolicy (pour uniformité)
    protected function hasRole(User $user, array|string $roles): bool
    {
        return $this->checkRole($user, $roles);
    }

    // Méthode utilitaire (COPIÉE DE TicketPolicy)
    protected function checkRole(User $user, array|string $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles]; // Si $roles est une chaîne, la convertir en tableau
        }
        return $user->roles()->whereIn('name', $roles)->exists();
    }
}