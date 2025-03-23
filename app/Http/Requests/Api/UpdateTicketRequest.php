<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Importer la classe Rule

class UpdateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Autorisation (à adapter si besoin)
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
            'title' => 'sometimes|string|max:255', // 'sometimes' : champ optionnel à la mise à jour
            'description' => 'sometimes|string',
            'status' => ['sometimes', Rule::in(['open', 'pending', 'closed'])], // Validation du statut avec Rule::in
            // 'user_id' => 'sometimes|exists:users,id', // Valider user_id si on permet de changer l'utilisateur associé
        ];
    }
}