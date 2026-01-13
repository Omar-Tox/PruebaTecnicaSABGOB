<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = [
        'name',
        'status',
        'project_id'
    ];

    // Relación: Una tarea pertenece a un proyecto
    public function project() {
        return $this->belongsTo(Project::class);
    }

    // Relación: Una tarea tiene muchas subtareas
    public function subtasks() {
        return $this->hasMany(Subtask::class);
    }

    // Relación Polimórfica: Una tarea tiene muchos comentarios
    public function comments() {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
