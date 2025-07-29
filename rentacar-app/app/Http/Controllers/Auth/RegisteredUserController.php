<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'                          => 'required|string|max:255',
            'apellido'                      => 'required|string|max:255',
            'ci'                            => 'required|integer|unique:users,ci',
            'domicilio'                     => 'required|string|max:255',
            'telefono'                      => 'required|string|max:20',
            'email'                         => 'required|string|email|max:255|unique:users,email',
            'password'                      => ['required','confirmed', Rules\Password::defaults()],
            'documento_frontal'             => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'documento_trasero'             => 'required|image|mimes:jpeg,png,jpg|max:2048',
         // 5MB para PDFs
        ]);

        // Procesar y guardar las imágenes
        $documentoFrontalPath = null;
        $documentoTraseroPath = null;
        $certificadoAntecedentesPath = null;

        if ($request->hasFile('documento_frontal')) {
            $documentoFrontalPath = $request->file('documento_frontal')
                ->store('usuarios/documentos', 'public');
        }

        if ($request->hasFile('documento_trasero')) {
            $documentoTraseroPath = $request->file('documento_trasero')
                ->store('usuarios/documentos', 'public');
        }



        // 1) Crear usuario
        $user = User::create([
            'name'                          => $request->name,
            'apellido'                      => $request->apellido,
            'ci'                            => $request->ci,
            'domicilio'                     => $request->domicilio,
            'telefono'                      => $request->telefono,
            'email'                         => $request->email,
            'password'                      => Hash::make($request->password),
            'documento_frontal_path'        => $documentoFrontalPath,
            'documento_trasero_path'        => $documentoTraseroPath,
            
        ]);

        // 2) Asignar rol "cliente" (id = 3)
        $user->syncRoles([3]);
        // o alternativamente: $user->assignRole('cliente');

        event(new Registered($user));

        // 3) Autenticar y redirigir
        Auth::login($user);

        return redirect()->route('vehiculos.show');
    }
}