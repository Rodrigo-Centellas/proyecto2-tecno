<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Contrato;
use App\Models\Reserva;
use App\Models\Pago;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $category = $request->input('category', 'todo');
        $results = [];

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        try {
            switch ($category) {
                case 'clientes':
                    $results = $this->searchClientes($query);
                    break;
                case 'vehiculos':
                    $results = $this->searchVehiculos($query);
                    break;
                case 'contratos':
                    $results = $this->searchContratos($query);
                    break;
                case 'reservas':
                    $results = $this->searchReservas($query);
                    break;
                case 'pagos':
                    $results = $this->searchPagos($query);
                    break;
                default: // 'todo'
                    $results = array_merge(
                        $this->searchClientes($query),
                        $this->searchVehiculos($query),
                        $this->searchContratos($query),
                        $this->searchReservas($query),
                        $this->searchPagos($query)
                    );
                    break;
            }

            // Limitar resultados totales
            $results = array_slice($results, 0, 20);

            return response()->json(['results' => $results]);

        } catch (\Exception $e) {
            return response()->json(['results' => [], 'error' => $e->getMessage()], 500);
        }
    }

    private function searchClientes($query)
    {
        $clientes = User::where('name', 'like', "%{$query}%")
            ->orWhere('apellido', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('ci', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        return $clientes->map(function ($cliente) {
            return [
                'id' => $cliente->id,
                'type' => 'cliente',
                'title' => $cliente->name . ' ' . $cliente->apellido,
                'subtitle' => $cliente->email . ' | CI: ' . $cliente->ci,
                'badge' => null,
                'badgeClass' => null
            ];
        })->toArray();
    }

    private function searchVehiculos($query)
    {
        $vehiculos = Vehiculo::where('placa', 'like', "%{$query}%")
            ->orWhere('marca', 'like', "%{$query}%")
            ->orWhere('modelo', 'like', "%{$query}%")
            ->orWhere('tipo', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        return $vehiculos->map(function ($vehiculo) {
            return [
                'id' => $vehiculo->id,
                'type' => 'vehiculo',
                'title' => $vehiculo->marca . ' ' . $vehiculo->modelo,
                'subtitle' => 'Placa: ' . $vehiculo->placa . ' | ' . $vehiculo->tipo,
                'badge' => $vehiculo->estado,
                'badgeClass' => $this->getEstadoBadgeClass($vehiculo->estado)
            ];
        })->toArray();
    }

    private function searchContratos($query)
    {
        $contratos = Contrato::with(['users', 'vehiculo'])
            ->whereHas('users', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('apellido', 'like', "%{$query}%");
            })
            ->orWhereHas('vehiculo', function ($q) use ($query) {
                $q->where('placa', 'like', "%{$query}%");
            })
            ->orWhere('id', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        return $contratos->map(function ($contrato) {
            $cliente = $contrato->users->first();
            return [
                'id' => $contrato->id,
                'type' => 'contrato',
                'title' => 'Contrato #' . $contrato->id,
                'subtitle' => ($cliente ? $cliente->name . ' ' . $cliente->apellido : 'Sin cliente') . 
                            ($contrato->vehiculo ? ' | ' . $contrato->vehiculo->placa : ''),
                'badge' => $contrato->estado,
                'badgeClass' => $this->getEstadoBadgeClass($contrato->estado)
            ];
        })->toArray();
    }

    private function searchReservas($query)
    {
        $reservas = Reserva::with(['user', 'vehiculo'])
            ->whereHas('user', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('apellido', 'like', "%{$query}%");
            })
            ->orWhereHas('vehiculo', function ($q) use ($query) {
                $q->where('placa', 'like', "%{$query}%");
            })
            ->orWhere('id', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        return $reservas->map(function ($reserva) {
            return [
                'id' => $reserva->id,
                'type' => 'reserva',
                'title' => 'Reserva #' . $reserva->id,
                'subtitle' => ($reserva->user ? $reserva->user->name . ' ' . $reserva->user->apellido : 'Sin usuario') . 
                            ($reserva->vehiculo ? ' | ' . $reserva->vehiculo->placa : ''),
                'badge' => $reserva->estado,
                'badgeClass' => $this->getEstadoBadgeClass($reserva->estado)
            ];
        })->toArray();
    }

    private function searchPagos($query)
    {
        $pagos = Pago::with(['contrato.users', 'contrato.vehiculo'])
            ->whereHas('contrato.users', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('apellido', 'like', "%{$query}%");
            })
            ->orWhereHas('contrato.vehiculo', function ($q) use ($query) {
                $q->where('placa', 'like', "%{$query}%");
            })
            ->orWhere('id', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        return $pagos->map(function ($pago) {
            $cliente = $pago->contrato->users->first() ?? null;
            return [
                'id' => $pago->id,
                'type' => 'pago',
                'title' => 'Pago #' . $pago->id,
                'subtitle' => ($cliente ? $cliente->name . ' ' . $cliente->apellido : 'Sin cliente') . 
                            ($pago->contrato->vehiculo ? ' | ' . $pago->contrato->vehiculo->placa : ''),
                'badge' => $pago->estado ?? 'Pendiente',
                'badgeClass' => $this->getEstadoBadgeClass($pago->estado ?? 'pendiente')
            ];
        })->toArray();
    }

    private function getEstadoBadgeClass($estado)
    {
        $estado = strtolower($estado);
        
        switch ($estado) {
            case 'disponible':
            case 'vigente':
            case 'completado':
            case 'confirmada':
                return 'bg-green-100 text-green-800';
            case 'ocupado':
            case 'pendiente':
                return 'bg-yellow-100 text-yellow-800';
            case 'mantenimiento':
            case 'vencido':
            case 'cancelada':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
}