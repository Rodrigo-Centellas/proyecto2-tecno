<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Si no se especifican permisos, solo verificar autenticación
        if (empty($permissions)) {
            return $next($request);
        }

        // Verificar si el usuario tiene al menos uno de los permisos requeridos
        $hasPermission = false;
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            // Redirigir según el rol del usuario
            return $this->redirectBasedOnRole($user);
        }

        return $next($request);
    }

    /**
     * Redirigir según el rol del usuario cuando no tiene permisos
     */
    private function redirectBasedOnRole($user)
    {
        if ($user->hasRole('Cliente')) {
            return redirect()->route('vehiculos.show')
                ->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        if ($user->hasRole('Empleado Operativo')) {
            return redirect()->route('reservas.index')
                ->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        // Para administradores o roles no definidos
        return redirect()->route('dashboard')
            ->with('error', 'No tienes permisos para acceder a esta sección.');
    }
}