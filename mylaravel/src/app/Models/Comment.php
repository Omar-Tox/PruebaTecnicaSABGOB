<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'body',
        'user_id',
        'commentable_id',
        'commentable_type'
    ];

    // Relación inversa polimórfica
    public function commentable() {
        return $this->morphTo();
    }

    // Relación: Un comentario fue escrito por un usuario
    public function user() {
        return $this->belongsTo(User::class);
    }
}
