<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ParticipantsUpdated implements ShouldBroadcast
{
    public function __construct(
        public array $names
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('pizarra-live')
        ];
    }

    public function broadcastAs(): string
    {
        return 'ParticipantsUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'count' => count($this->names),
            'names' => $this->names
        ];
    }
}
