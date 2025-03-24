<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreResponseRequest;
use App\Http\Requests\Api\UpdateResponseRequest;
use App\Services\Response\ResponseService;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Responses",
 *     description="Endpoints pour la gestion des réponses aux tickets d'assistance client"
 * )
 * @OA\PathItem(path="/api")
 *
 * @OA\Schema(
 *     schema="Response",
 *     title="Response",
 *     description="Schéma du modèle Response",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="content", type="string", example="Voici ma réponse au ticket..."),
 *     @OA\Property(property="ticket_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="user_id", type="integer", format="int64", example=2),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="ResponsePayload",
 *     title="ResponsePayload",
 *     description="Schéma pour la création/mise à jour d'une Response (payload de la requête)",
 *     @OA\Property(property="content", type="string", example="Voici ma réponse au ticket...")
 * )
 */
class ResponseController extends Controller
{
    protected $responseService;

    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

    /**
     * Display a listing of responses for a specific ticket.
     * @OA\Get(
     *     path="/tickets/{ticket}/responses",
     *     tags={"Responses"},
     *     summary="Liste les réponses d'un ticket",
     *     description="Récupère la liste des réponses associées à un ticket spécifique.",
     *     @OA\Parameter(
     *         name="ticket",
     *         in="path",
     *         description="ID du ticket pour lequel récupérer les réponses",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des réponses du ticket",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Response")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket non trouvé"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function index(Ticket $ticket): JsonResponse
    {
        $responses = $this->responseService->getResponsesByTicketId($ticket->id);
        return response()->json($responses);
    }

    /**
     * Store a newly created response for a ticket.
     * @OA\Post(
     *     path="/tickets/{ticket}/responses",
     *     tags={"Responses"},
     *     summary="Créer une nouvelle réponse pour un ticket",
     *     description="Crée une nouvelle réponse pour un ticket spécifique.",
     *     @OA\Parameter(
     *         name="ticket",
     *         in="path",
     *         description="ID du ticket auquel ajouter la réponse",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ResponsePayload")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Réponse créée avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Response")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreurs de validation"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket non trouvé"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function store(StoreResponseRequest $request, Ticket $ticket): JsonResponse
    {
        $user = auth()->user(); // Récupérer l'utilisateur authentifié
        $response = $this->responseService->createResponse($request->validated(), $ticket, $user);
        return response()->json($response, 201);
    }

    /**
     * Display the specified response.
     * @OA\Get(
     *     path="/responses/{response}",
     *     tags={"Responses"},
     *     summary="Afficher une réponse spécifique",
     *     description="Récupère les détails d'une réponse par son ID.",
     *     @OA\Parameter(
     *         name="response",
     *         in="path",
     *         description="ID de la réponse à afficher",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de la réponse",
     *         @OA\JsonContent(ref="#/components/schemas/Response")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Réponse non trouvée"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $response = $this->responseService->getResponseById($id);
        if (!$response) {
            return response()->json(['message' => 'Response not found'], 404);
        }
        return response()->json($response);
    }

    /**
     * Update the specified response.
     * @OA\Put(
     *     path="/responses/{response}",
     *     tags={"Responses"},
     *     summary="Mettre à jour une réponse",
     *     description="Met à jour une réponse existante avec les données fournies.",
     *     @OA\Parameter(
     *         name="response",
     *         in="path",
     *         description="ID de la réponse à mettre à jour",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ResponsePayload")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Réponse mise à jour avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Response")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Réponse non trouvée"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreurs de validation"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function update(UpdateResponseRequest $request, int $id): JsonResponse
    {
        $response = $this->responseService->updateResponse($id, $request->validated());
        if (!$response) {
            return response()->json(['message' => 'Response not found'], 404);
        }
        return response()->json($response);
    }

    /**
     * Remove the specified response from storage.
     * @OA\Delete(
     *     path="/responses/{response}",
     *     tags={"Responses"},
     *     summary="Supprimer une réponse",
     *     description="Supprime une réponse existante par son ID.",
     *     @OA\Parameter(
     *         name="response",
     *         in="path",
     *         description="ID de la réponse à supprimer",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Réponse supprimée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Response deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Réponse non trouvée"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->responseService->deleteResponse($id);
        if (!$deleted) {
            return response()->json(['message' => 'Response not found'], 404);
        }
        return response()->json(['message' => 'Response deleted successfully']);
    }
}