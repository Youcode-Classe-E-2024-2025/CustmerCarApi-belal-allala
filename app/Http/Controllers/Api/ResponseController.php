<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreResponseRequest;
use App\Http\Requests\Api\UpdateResponseRequest;
use App\Services\Response\ResponseService;
use App\Models\Ticket;
use App\Models\Response;
use Illuminate\Http\JsonResponse;
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
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of responses for a specific ticket.
     * @OA\Get( ... )
     */
    public function index(Ticket $ticket): JsonResponse
    {
        // Autorisation: vérifier si l'utilisateur peut voir les réponses de ce ticket
        $this->authorize('viewTicketResponses', $ticket);

        $responses = $this->responseService->getResponsesByTicketId($ticket->id);
        return response()->json($responses);
    }

    /**
     * Store a newly created response for a ticket.
     * @OA\Post( ... )
     */
    public function store(StoreResponseRequest $request, Ticket $ticket): JsonResponse
    {
        $user = auth()->user();
        
        // Vérifier que le ticket est ouvert et que l'utilisateur peut répondre
        $this->authorize('create', [Response::class, $ticket]);

        $response = $this->responseService->createResponse($request->validated(), $ticket, $user);
        return response()->json($response, 201);
    }

    /**
     * Display the specified response.
     * @OA\Get( ... )
     */
    public function show(int $id): JsonResponse
    {
        $response = $this->responseService->getResponseById($id);
        
        if (!$response) {
            return response()->json(['message' => 'Response not found'], 404);
        }

        $this->authorize('view', $response);

        return response()->json($response);
    }

    /**
     * Update the specified response.
     * @OA\Put( ... )
     */
    public function update(UpdateResponseRequest $request, int $id): JsonResponse
    {
        $response = $this->responseService->getResponseById($id);
        
        if (!$response) {
            return response()->json(['message' => 'Response not found'], 404);
        }

        $this->authorize('update', $response);

        $response = $this->responseService->updateResponse($id, $request->validated());
        return response()->json($response);
    }

    /**
     * Remove the specified response from storage.
     * @OA\Delete( ... )
     */
    public function destroy(int $id): JsonResponse
    {
        $response = $this->responseService->getResponseById($id);
        
        if (!$response) {
            return response()->json(['message' => 'Response not found'], 404);
        }

        $this->authorize('delete', $response);

        $deleted = $this->responseService->deleteResponse($id);
        return response()->json(['message' => 'Response deleted successfully']);
    }
}