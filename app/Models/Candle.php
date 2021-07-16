<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candle extends Model
{
    protected $fillable = [
        'id',
        'timestamp',
        'time',
        'pair',
        'open',
        'high',
        'low',
        'close',
        'created_at',
        'updated_at'
    ];
}
