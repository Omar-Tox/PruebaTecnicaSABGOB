<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubtaskController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Task $task)
    {
        // Validación en cadena: Subtarea -> Tarea -> Proyecto -> Usuario
        if ($task->project->user_id !== Auth::id()) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $subtask = $task->subtasks()->create([
            'name' => $request->name,
            'status' => false
        ]);

        return response()->json($subtask, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subtask $subtask)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|boolean'
        ]);

        $subtask->update($data);
        return $subtask;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subtask $subtask)
    {
        if ($subtask->task->project->user_id !== Auth::id()) abort(403);
        $subtask->delete();

        return response()->json([
            'message' => 'Subtarea eliminada correctamente'
        ], 200);
    }
}
