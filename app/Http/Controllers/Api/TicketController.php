<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest; // Vous devrez créer ces Request classes plus tard
use App\Http\Requests\UpdateTicketRequest; // Vous devrez créer ces Request classes plus tard
use App\Services\Ticket\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      title="CustomerCareAPI - Ticket Management API",
 *      version="1.0.0",
 *      description="API pour la gestion des tickets d'assistance client"
 * )
 *
 * @OA\Server(
 *      url="http://127.0.0.1:8000",
 *      description="Serveur de développement"
 * )
 *
 * @OA\PathItem(
 *     path="/api"
 * )
 *
 * @OA\Schema(
 *     schema="Ticket",
 *     title="Ticket",
 *     description="Schéma du modèle Ticket",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="title", type="string", example="Problème d'accès à mon compte"),
 *     @OA\Property(property="description", type="string", example="Je n'arrive plus à me connecter à mon compte depuis ce matin..."),
 *     @OA\Property(property="status", type="string", enum={"open", "pending", "closed"}, example="open"),
 *     @OA\Property(property="user_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="TicketPayload",
 *     title="TicketPayload",
 *     description="Schéma pour la création/mise à jour d'un Ticket (payload de la requête)",
 *     @OA\Property(property="title", type="string", example="Problème d'accès à mon compte"),
 *     @OA\Property(property="description", type="string", example="Je n'arrive plus à me connecter à mon compte depuis ce matin...")
 * )
 */
class TicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $tickets = $this->ticketService->getAllTickets();
        return response()->json($tickets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request): JsonResponse
    {
        // Récupérer l'utilisateur authentifié (exemple, à adapter selon votre auth)
        $user = auth()->user(); // Assurez-vous que l'authentification est configurée et que l'utilisateur est connecté

        $ticket = $this->ticketService->createTicket($request->validated(), $user); // Utilise $request->validated() pour les données validées
        return response()->json($ticket, 201); // 201 Created status code
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $ticket = $this->ticketService->getTicketById($id);
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404); // 404 Not Found
        }
        return response()->json($ticket);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, int $id): JsonResponse
    {
        $ticket = $this->ticketService->updateTicket($id, $request->validated());
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        return response()->json($ticket);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->ticketService->deleteTicket($id);
        if (!$deleted) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        return response()->json(['message' => 'Ticket deleted successfully']);
    }
}