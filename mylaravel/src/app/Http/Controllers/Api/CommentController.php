<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\Relation;

class CommentController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'body' => 'required|string',
            'commentable_id' => 'required|integer',
            //validar que el tipo sea uno de las 3 opciones (project, task, subtask)
            'commentable_type' => 'required|string|in:project,task,subtask'
        ]);

        //Mapeamos manualmente para seguridad
        $modelClass = match($request->commentable_type) {
            'project' => Project::class,
            'task' => Task::class,
            'subtask' => Subtask::class
        };

        //Buscamos el modelo correspondiente a la jerarquia
        $model = $modelClass::findOrFail($request->commentable_id);

        //Buscamos el ID del dueño "escalando" hacia arriba según el tipo
        $ownerId = match($request->commentable_type) {
            'project' => $model->user_id,
            'task'    => $model->project?->user_id,
            'subtask' => $model->task?->project?->user_id,
        };

        //Verificamos que el modelo pertenece al usuario autenticado
        if($ownerId !== Auth::id()) abort(403, 'Unauthorized action.');

        $comment = $model->comments()->create([
            'body' => $request->body,
            'user_id' => Auth::id()
        ]);

        return response()->json($comment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        //
    }
}
