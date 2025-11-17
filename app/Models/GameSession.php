<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSession extends Model
{
    // ✅ Evita que Eloquent intente usar created_at / updated_at
    public $timestamps = false;

    protected $fillable = ['player_name','kills','rooms','ended_at'];

    // (opcional, pero ayuda)
    protected $casts = [
        'kills'    => 'integer',
        'rooms'    => 'integer',
        'ended_at' => 'datetime',
    ];
}

