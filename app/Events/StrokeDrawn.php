<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class StrokeDrawn implements ShouldBroadcast
{
    // Información del trazo realizado.
    public array $stroke;

    public function __construct(array $stroke)
    {
        $this->stroke = $stroke;
    }

    // Canal por el que transmitiremos el dibujo.
    public function broadcastOn(): array
    {
        return [
            new Channel('pizarra-live')
        ];
    }

    // Nombre que escuchará Laravel Echo.
    public function broadcastAs(): string
    {
        return 'StrokeDrawn';
    }
}
