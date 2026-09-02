<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'points_required',
    ];

    protected function casts(): array
    {
        return [
            'points_required' => 'integer',
        ];
    }
}
