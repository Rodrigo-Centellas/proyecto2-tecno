<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Si no se especifican roles, solo verificar autenticación
        if (empty($roles)) {
            return $next($request);
        }

        // Verificar si el usuario tiene al menos uno de los roles requeridos
        $hasRole = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            return $this->redirectBasedOnRole($user);
        }

        return $next($request);
    }

    /**
     * Redirigir según el rol del usuario cuando no tiene el rol requerido
     */
    private function redirectBasedOnRole($user)
    {
        if ($user->hasRole('Cliente')) {
            return redirect()->route('vehiculos.show')
                ->with('error', 'No tienes acceso a esta área del sistema.');
        }

        if ($user->hasRole('Empleado Operativo')) {
            return redirect()->route('reservas.index')
                ->with('error', 'No tienes acceso a esta área del sistema.');
        }

        // Para administradores o roles no definidos
        return redirect()->route('dashboard')
            ->with('error', 'No tienes acceso a esta área del sistema.');
    }
}