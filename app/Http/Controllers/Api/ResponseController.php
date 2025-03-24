<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreResponseRequest; // Vous devrez créer ces Request classes
use App\Http\Requests\Api\UpdateResponseRequest; // Vous devrez créer ces Request classes
use App\Services\Response\ResponseService;
use App\Models\Ticket; // Importez le modèle Ticket
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    protected $responseService;

    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

    /**
     * Display a listing of responses for a specific ticket.
     * GET /api/tickets/{ticket}/responses
     */
    public function index(Ticket $ticket): JsonResponse
    {
        $responses = $this->responseService->getResponsesByTicketId($ticket->id);
        return response()->json($responses);
    }

    /**
     * Store a newly created response for a ticket.
     * POST /api/tickets/{ticket}/responses
     */
    public function store(StoreResponseRequest $request, Ticket $ticket): JsonResponse
    {
        $user = auth()->user(); // Récupérer l'utilisateur authentifié
        $response = $this->responseService->createResponse($request->validated(), $ticket, $user);
        return response()->json($response, 201); // 201 Created
    }

    /**
     * Display the specified response.
     * GET /api/responses/{response}
     */
    public function show(int $id): JsonResponse
    {
        $response = $this->responseService->getResponseById($id);
        if (!$response) {
            return response()->json(['message' => 'Response not found'], 404); // 404 Not Found
        }
        return response()->json($response);
    }

    /**
     * Update the specified response.
     * PUT/PATCH /api/responses/{response}
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
     * DELETE /api/responses/{response}
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