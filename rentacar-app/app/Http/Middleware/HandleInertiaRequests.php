<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $pagina = optional($request->route())->getName();

        if ($pagina) {
            $visita = \App\Models\Visita::where('pagina', $pagina)->first();

            if ($visita) {
                $visita->increment('contador');
            } else {
                \App\Models\Visita::create([
                    'pagina' => $pagina,
                    'contador' => 1,
                ]);
            }

            $contador = $visita ? $visita->contador : 1;
        } else {
            $contador = 0;
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'apellido' => $request->user()->apellido,
                    'ci' => $request->user()->ci,
                    'domicilio' => $request->user()->domicilio,
                    'telefono' => $request->user()->telefono,
                    'documento_frontal_path' => $request->user()->documento_frontal_path,
                    'documento_trasero_path' => $request->user()->documento_trasero_path,
                    'certificado_antecedentes_path' => $request->user()->certificado_antecedentes_path,
                    // AGREGAR ROLES Y PERMISOS PARA EL SISTEMA DE PERMISOS
                    'roles' => $request->user()->roles()->get(['id', 'name']),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                    // Para debugging (opcional, puedes eliminar después)
                    'direct_permissions' => $request->user()->getDirectPermissions()->pluck('name'),
                    'role_permissions' => $request->user()->getPermissionsViaRoles()->pluck('name'),
                ] : null,
            ],
            'visitas' => [
                'pagina' => $pagina,
                'contador' => $contador,
            ],
            // Agregar mensajes flash para mejor UX
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
            ],
        ];
    }
}