<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\Comment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //Creacion de usuario de prueba
        $me = User::factory()->create([
            'name' => 'Omar Cito',
            'email' => 'omarcito@gmail.com',
            'password' => bcrypt('password'),
        ]);

        //Creamos usuarios donde se llenaran los datos
        User::factory(5)->create()->each(function ($user) {

            //Se crean sus proyectos
            Project::factory(3)
                ->for($user) //usuario actual
                ->has(
                    //Se crean 4 tareas por proyecto
                    Task::factory(4)
                        ->has(
                            //Se crean 2 subtareas por tarea
                            Subtask::factory(2)
                                ->hasComments(1) //Se crean 1 comentario por subtarea
                        )
                        ->hasComments(2) //Se crean 2 comentarios por tarea
                )
                ->hasComments(3) //Se crean 3 comentarios por proyecto
                ->create();
        });

        echo "BD Llena";
    }
}
