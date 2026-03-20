<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Film extends Model
{
    use HasFactory;

    protected $table = 'my_favorite_subject';

    protected $fillable = [
        'title', 'image', 'description', 'director',
        'year', 'genre', 'rating', 'user_id',
    ];

    protected $casts = [
        'year'   => 'integer',
        'rating' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
