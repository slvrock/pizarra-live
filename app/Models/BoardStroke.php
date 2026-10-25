<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardStroke extends Model
{
    protected $table = 'board_strokes';

    protected $fillable = [
        'stroke_uuid',
        'tool',
        'color',
        'size',
        'points'
    ];

    protected $casts = [
        'points' => 'array',
        'size' => 'integer'
    ];
}
