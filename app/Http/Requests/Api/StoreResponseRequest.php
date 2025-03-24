<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreResponseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ajouter ici la logique d'autorisation si nécessaire.
        // Pour l'instant, on suppose que tout utilisateur authentifié peut créer une réponse.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'content' => 'required|string', // Le contenu de la réponse est obligatoire et doit être une chaîne
            // 'ticket_id' => 'required|exists:tickets,id', // Pas besoin de valider ticket_id ici, il est passé dans l'URL (route model binding)
            // 'user_id' => 'required|exists:users,id',   // User_id sera récupéré de l'utilisateur authentifié côté serveur
        ];
    }
}