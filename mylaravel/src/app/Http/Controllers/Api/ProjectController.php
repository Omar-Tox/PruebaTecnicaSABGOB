<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Listar los proyectos del usuario autenticado
     */
    public function index()
    {
        // Usamos 'with' para traer las tareas y subtareas en una sola request
        return Auth::user()->projects()
            ->with(['tasks.subtasks',
            'comments',
            'tasks.comments',
            'tasks.subtasks.comments'])
            ->get();
    }

    /**
     * Creamos un nuevo proyecto
     */
    public function store(Request $request)
{
    // 1. Validamos la estructura anidada (Arrays dentro de arrays)
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'tasks' => 'nullable|array',              // Puede venir un array de tareas
        'tasks.*.name' => 'required|string',      // Cada tarea debe tener nombre
        'tasks.*.subtasks' => 'nullable|array',   // Cada tarea puede tener subtareas
        'tasks.*.subtasks.*.name' => 'required|string', // Cada subtarea debe tener nombre
    ]);

    return DB::transaction(function () use ($data) {
        
        // A. Crear Proyecto
        $project = Auth::user()->projects()->create([
            'name' => $data['name'],
            'status' => false
        ]);

        if (!empty($data['tasks'])) {
            foreach ($data['tasks'] as $taskData) {
                // Crear Tarea
                $task = $project->tasks()->create([
                    'name' => $taskData['name'],
                    'status' => false
                ]);

                // C. ¿Hay subtareas dentro de esta tarea?
                if (!empty($taskData['subtasks'])) {
                    // Preparamos el array para createMany (optimización)
                    $subtasksPayload = array_map(function($sub) {
                        return ['name' => $sub['name'], 'status' => false];
                    }, $taskData['subtasks']);

                    $task->subtasks()->createMany($subtasksPayload);
                }
            }
        }

        return $project->load('tasks.subtasks');
    });
}

    /**
     * Ver un proyecto en especifico.
     */
    public function show(Project $project)
    {
        //Verificamos que el proyecto sea el usuario especificado
        if($project->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return $project->load('tasks.subtasks', 'comments.user');
    }

    /**
     * Actualizar la informacion de un proyecto
     */
    public function update(Request $request, Project $project)
    {
        // Validamos solo lo que venga (a veces solo es status, a veces es name)
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|boolean'
        ]);

        $project->update($data);

        return $project;
    }

    /**
     * Eliminar el proyecto
     */
    public function destroy(Project $project)
    {
        if($project->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $project->delete();

        return response()->json([
            'message' => 'Projecto eliminado correctamente'
        ], 200);
    }
}
