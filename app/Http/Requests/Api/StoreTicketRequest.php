<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ici, vous pouvez ajouter une logique d'autorisation si nécessaire.
        // Pour l'instant, on suppose que tout utilisateur authentifié peut créer un ticket.
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            // 'status' => 'in:open,pending,closed', // On pourrait valider le statut, mais par défaut 'open' à la création.
            // 'user_id' => 'exists:users,id', // Validation de l'user_id si vous le recevez dans la requête, sinon c'est l'utilisateur authentifié.
        ];
    }
}