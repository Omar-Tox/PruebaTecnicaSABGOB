<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //Funcion para registrar usuarios
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user' => $user,
            'token' => $user->createToken('API Token')->plainTextToken
        ]);
    }

    //Funcion de inicio de sesion
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        //Obtenemos el usuario que coincida con el email
        $user = User::where('email', $request->email)->first();

        //Validamos que el usuario exista y que la contraseña sea correcta
        if(! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas'],
            ]);
        }

        return response()->json([
            'message' => 'Inicio de sesion exitoso',
            'user' => $user,
            'token' => $user->createToken('API Token')->plainTextToken
        ]);
    }
}
