<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
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
 * @OA\Tag(
 *     name="Tickets",
 *     description="Endpoints pour la gestion des tickets d'assistance client"
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
     * @OA\Get(
     *     path="/tickets",
     *     tags={"Tickets"}, 
     *     summary="Liste tous les tickets",
     *     description="Récupère la liste paginée des tickets.",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des tickets",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Ticket") 
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $tickets = $this->ticketService->getAllTickets();
        return response()->json($tickets);
    }

    /**
     * Store a newly created resource in storage.
     * @OA\Post(
     *     path="/tickets",
     *     tags={"Tickets"}, 
     *     summary="Créer un nouveau ticket",
     *     description="Crée un nouveau ticket avec les données fournies.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TicketPayload")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ticket créé avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Ticket")
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
    public function store(StoreTicketRequest $request): JsonResponse
    {
        // Récupérer l'utilisateur authentifié (exemple, à adapter selon votre auth)
        $user = auth()->user(); // Assurez-vous que l'authentification est configurée et que l'utilisateur est connecté

        $ticket = $this->ticketService->createTicket($request->validated(), $user); // Utilise $request->validated() pour les données validées
        return response()->json($ticket, 201); // 201 Created status code
    }

    /**
     * Display the specified resource.
     * @OA\Get(
     *     path="/tickets/{id}",
     *     tags={"Tickets"},  
     *     summary="Afficher un ticket spécifique",
     *     description="Récupère les détails d'un ticket par son ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du ticket à afficher",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du ticket",
     *         @OA\JsonContent(ref="#/components/schemas/Ticket")
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
     * @OA\Put(
     *     path="/tickets/{id}",
     *     tags={"Tickets"},
     *     summary="Mettre à jour un ticket",
     *     description="Met à jour un ticket existant avec les données fournies.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du ticket à mettre à jour",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TicketPayload")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ticket mis à jour avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/Ticket")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket non trouvé"
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
     * @OA\Delete(
     *     path="/tickets/{id}",
     *     tags={"Tickets"}, 
     *     summary="Supprimer un ticket",
     *     description="Supprime un ticket existant par son ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du ticket à supprimer",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ticket supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ticket deleted successfully")
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
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->ticketService->deleteTicket($id);
        if (!$deleted) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        return response()->json(['message' => 'Ticket deleted successfully']);
    }
}