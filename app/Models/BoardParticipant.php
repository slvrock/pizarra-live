<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardParticipant extends Model
{
    protected $table = 'board_participants';

    protected $fillable = [
        'participant_uuid',
        'name',
        'last_seen'
    ];

    protected $casts = [
        'last_seen' => 'datetime'
    ];
}
