<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Crear una nueva tarea dentro de un proyecto
     */
    public function store(Request $request, Project $project)
    {
        //Validar al usuario autenticado
        if ($project->user_id !== Auth::id()) abort(403);

        //Validar la data 
        $validateData = $request->validate(['name' => 'required|string|max:255',]);

        //Creamos la tarea
        $task = $project->tasks()->create([
            'name' => $validateData['name'],
            'status' => false
        ]);

        return response()->json($task, 201);
    }

    /**
     * Actualizar la informacion de una tarea
     */
    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|boolean'
        ]);

        $task->update($data);
        return $task;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        if ($task->project->user_id !== Auth::id()) abort(403);

         $task->delete();

         return response()->json([
            'message' => 'Tarea eliminada correctamente'
         ], 200);
    }
}
