<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class BoardCleared implements ShouldBroadcast
{
    // Utilizamos el mismo canal de la pizarra.
    public function broadcastOn(): array
    {
        return [
            new Channel('pizarra-live')
        ];
    }

    // Nombre del evento que escuchará Echo.
    public function broadcastAs(): string
    {
        return 'BoardCleared';
    }
}

