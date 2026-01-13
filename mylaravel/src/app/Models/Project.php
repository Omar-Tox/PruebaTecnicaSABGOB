<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{

    use HasFactory;
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'status',
        'user_id'
    ];

    // Relación: Un proyecto pertenece a un usuario
    public function user() {
        return $this->belongsTo(User::class);
    }

    // Relación: Un proyecto tiene muchas tareas
    public function tasks() {
        return $this->hasMany(Task::class);
    }

    // Relación Polimórfica: Un proyecto tiene muchos comentarios
    public function comments() {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
