<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;   // <<< IMPORTANTE

class GameSessionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/sessions",
     *     summary="Registrar nueva sesión de juego",
     *     description="Crea un nuevo registro de sesión de juego enviado desde el juego (Unity).",
     *     tags={"GameSessions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"player_name","kills","rooms"},
     *             @OA\Property(
     *                 property="player_name",
     *                 type="string",
     *                 example="Omar"
     *             ),
     *             @OA\Property(
     *                 property="kills",
     *                 type="integer",
     *                 example=15
     *             ),
     *             @OA\Property(
     *                 property="rooms",
     *                 type="integer",
     *                 example=4
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sesión registrada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="player_name", type="string", example="Omar"),
     *             @OA\Property(property="kills", type="integer", example=15),
     *             @OA\Property(property="rooms", type="integer", example=4),
     *             @OA\Property(property="ended_at", type="string", format="date-time", example="2025-11-16T21:35:00"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos inválidos"
     *     )
     * )
     */
    // POST /api/sessions
    public function store(Request $request)
    {
        $session = GameSession::create([
            'player_name' => $request->player_name,
            'kills'       => $request->kills,
            'rooms'       => $request->rooms,
            'ended_at'    => now(), // <-- Esto era lo que faltaba
        ]);

        return response()->json($session, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/leaderboard",
     *     summary="Leaderboard de sesiones de juego",
     *     description="Devuelve, por jugador, sus mejores estadísticas (máximos de kills y rooms) y la última vez que jugó.",
     *     tags={"GameSessions"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Cantidad máxima de jugadores a devolver (1–100).",
     *         @OA\Schema(type="integer", minimum=1, maximum=100, default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de jugadores con sus mejores estadísticas.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(
     *                     property="player_name",
     *                     type="string",
     *                     example="Omar"
     *                 ),
     *                 @OA\Property(
     *                     property="best_kills",
     *                     type="integer",
     *                     example=42
     *                 ),
     *                 @OA\Property(
     *                     property="best_rooms",
     *                     type="integer",
     *                     example=7
     *                 ),
     *                 @OA\Property(
     *                     property="last_played",
     *                     type="string",
     *                     format="date-time",
     *                     example="2025-11-16T21:35:00"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    // GET /api/leaderboard
    public function leaderboard(Request $req): JsonResponse
    {
        $limit = min(max((int)$req->query('limit', 20), 1), 100);

        $rows = DB::table('game_sessions')
            ->select(
                'player_name',
                DB::raw('MAX(kills)    AS best_kills'),
                DB::raw('MAX(rooms)    AS best_rooms'),
                DB::raw('MAX(ended_at) AS last_played')
            )
            ->groupBy('player_name')
            ->orderByDesc('best_kills')
            ->orderByDesc('best_rooms')
            ->limit($limit)
            ->get();

        return response()->json($rows);
    }
}

