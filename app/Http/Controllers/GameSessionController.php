<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;   // <<< IMPORTANTE

class GameSessionController extends Controller
{
    // POST /api/sessions
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'player_name' => 'required|string|max:64',
            'kills'       => 'required|integer|min:0',
            'rooms'       => 'required|integer|min:0',
            'ended_at'    => 'nullable|date',
        ]);

        $data['ended_at'] = $data['ended_at'] ?? now();
        $row = GameSession::create($data);

        return response()->json(['ok' => true, 'id' => $row->id], 201);
    }

    // GET /api/leaderboard
    public function leaderboard(Request $req): JsonResponse
    {
        $limit = min(max((int)$req->query('limit', 20), 1), 100);

        $rows = DB::table('game_sessions')
            ->select(
                'player_name',
                DB::raw('MAX(kills)   AS best_kills'),
                DB::raw('MAX(rooms)   AS best_rooms'),
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
