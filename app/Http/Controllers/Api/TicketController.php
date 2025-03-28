<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTicketRequest;
use App\Http\Requests\Api\UpdateTicketRequest;
use App\Services\Ticket\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Ticket;
use OpenApi\Annotations as OA;

class TicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
        $this->middleware('auth:api'); // Assure que l'utilisateur est authentifié
    }

    /**
     * Display a listing of the resource.
     * @OA\Get( ... )
     */
    public function index(Request $request): JsonResponse
    {
        // Autorisation: seul un utilisateur authentifié peut voir la liste des tickets
        $this->authorize('viewAny', Ticket::class);

        // Si l'utilisateur n'est pas admin, on filtre pour ne voir que ses tickets
        $user = auth()->user();
        $filters = $request->only(['status', 'search']);
        $perPage = $request->integer('per_page', 10);

        if (!$user->hasRole('admin')) {
            $filters['user_id'] = $user->id;
        }

        $tickets = $this->ticketService->getAllTickets($filters, $perPage);

        return response()->json([
            'data' => $tickets->items(),
            'links' => [
                'first' => $tickets->url(1),
                'last' => $tickets->url($tickets->lastPage()),
                'prev' => $tickets->previousPageUrl(),
                'next' => $tickets->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'from' => $tickets->firstItem(),
                'last_page' => $tickets->lastPage(),
                'path' => $tickets->path(),
                'per_page' => $tickets->perPage(),
                'to' => $tickets->lastItem(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @OA\Post( ... )
     */
    public function store(StoreTicketRequest $request): JsonResponse
    {
        $user = auth()->user();
        $this->authorize('create', Ticket::class);

        $ticket = $this->ticketService->createTicket($request->validated(), $user);
        return response()->json($ticket, 201);
    }

    /**
     * Display the specified resource.
     * @OA\Get( ... )
     */
    public function show(int $id): JsonResponse
    {
        $ticket = $this->ticketService->getTicketById($id);
        
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $this->authorize('view', $ticket);

        return response()->json($ticket);
    }

    /**
     * Update the specified resource in storage.
     * @OA\Put( ... )
     */
    public function update(UpdateTicketRequest $request, int $id): JsonResponse
    {
        $ticket = $this->ticketService->getTicketById($id);
        
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $this->authorize('update', $ticket);

        $ticket = $this->ticketService->updateTicket($id, $request->validated());
        return response()->json($ticket);
    }

    /**
     * Remove the specified resource from storage.
     * @OA\Delete( ... )
     */
    public function destroy(int $id): JsonResponse
    {
        $ticket = $this->ticketService->getTicketById($id);
        
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $this->authorize('delete', $ticket);

        $deleted = $this->ticketService->deleteTicket($id);
        return response()->json(['message' => 'Ticket deleted successfully']);
    }

    /**
     * Change ticket status
     * @OA\Patch(
     *     path="/tickets/{id}/status",
     *     tags={"Tickets"},
     *     summary="Change ticket status",
     *     description="Change the status of a ticket (admin/support only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"open", "pending", "closed"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated",
     *         @OA\JsonContent(ref="#/components/schemas/Ticket")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket not found"
     *     )
     * )
     */
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $ticket = $this->ticketService->getTicketById($id);
        
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        $this->authorize('changeStatus', $ticket);

        $validated = $request->validate([
            'status' => 'required|in:open,pending,closed'
        ]);

        $ticket = $this->ticketService->updateTicketStatus($id, $validated['status']);
        return response()->json($ticket);
    }
}