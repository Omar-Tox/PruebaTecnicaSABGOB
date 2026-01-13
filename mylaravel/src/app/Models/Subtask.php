<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subtask extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'status',
        'task_id'
    ];

    // Relación: Una subtarea pertenece a una tarea
    public function task() {
        return $this->belongsTo(Task::class);
    }

    // Relación Polimórfica: Una subtarea tiene muchos comentarios
    public function comments() {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
