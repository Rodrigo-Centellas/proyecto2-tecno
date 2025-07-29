<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
public function index(Request $request)
{
    $search = $request->input('search');

    $users = User::with('roles')
        ->when($search, function ($query, $search) {
            $search = strtolower($search); // Normaliza la búsqueda a minúsculas

            $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(apellido) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
        })
        ->get();

    return Inertia::render('Users/Index', [
        'users' => $users,
        'filters' => $request->only('search'),
    ]);
}


    public function create()
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'ci' => 'required|integer|unique:users,ci',
            'domicilio' => 'required|string|max:255',
            'telefono' => 'required|integer',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'apellido' => $data['apellido'],
            'ci' => $data['ci'],
            'domicilio' => $data['domicilio'],
            'telefono' => $data['telefono'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('users.index');
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user->load('roles'),
            'roles' => Role::all(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'ci' => 'required|integer|unique:users,ci,' . $user->id,
            'domicilio' => 'required|string|max:255',
            'telefono' => 'required|integer',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->update([
            'name' => $data['name'],
            'apellido' => $data['apellido'],
            'ci' => $data['ci'],
            'domicilio' => $data['domicilio'],
            'telefono' => $data['telefono'],
            'email' => $data['email'],
        ]);

        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('users.index');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index');
    }

public function show(User $user)
{
    // Cargar las relaciones necesarias
    $user->load('roles');
    
    return Inertia::render('Users/show', [
        'user' => $user
    ]);
}
}
