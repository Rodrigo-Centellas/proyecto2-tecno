<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pagos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .filters {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .filters h3 {
            margin-top: 0;
            color: #333;
        }
        .filter-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }
        .filter-label {
            font-weight: bold;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background-color: #d4edda; color: #155724; }
        .badge-warning { background-color: #fff3cd; color: #856404; }
        .badge-danger { background-color: #f8d7da; color: #721c24; }
        .badge-secondary { background-color: #e2e3e5; color: #383d41; }
        .total-row {
            font-weight: bold;
            background-color: #e9ecef !important;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Reporte de Pagos</h1>
        <p>Generado el {{ date('d/m/Y H:i:s') }}</p>
        <p>Total de registros: {{ count($pagos) }}</p>
    </div>

    @if(count($filtros) > 0)
    <div class="filters">
        <h3>🔍 Filtros Aplicados:</h3>
        @foreach($filtros as $key => $value)
            <div class="filter-item">
                <span class="filter-label">{{ $key }}:</span> {{ $value }}
            </div>
        @endforeach
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 8%">ID</th>
                <th style="width: 12%">Fecha</th>
                <th style="width: 20%">Usuario</th>
                <th style="width: 18%">Vehículo</th>
                <th style="width: 12%">Tipo Pago</th>
                <th style="width: 12%">Tipo Contrato</th>
                <th style="width: 10%">Monto</th>
                <th style="width: 8%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pagos as $pago)
            <tr>
                <td class="text-center">{{ $pago['id'] }}</td>
                <td>{{ date('d/m/Y', strtotime($pago['fecha'])) }}</td>
                <td>{{ $pago['usuario'] }}</td>
                <td style="font-size: 10px;">{{ $pago['vehiculo'] }}</td>
                <td class="text-center">
                    <span class="badge badge-secondary">{{ ucfirst($pago['tipo_pago']) }}</span>
                </td>
                <td class="text-center">{{ $pago['tipo_contrato'] }}</td>
                <td class="text-right">${{ number_format($pago['monto'], 2) }}</td>
                <td class="text-center">
                    <span class="badge 
                        @if($pago['estado'] == 'pagado') badge-success
                        @elseif($pago['estado'] == 'pendiente') badge-warning
                        @elseif($pago['estado'] == 'fallido') badge-danger
                        @else badge-secondary
                        @endif">
                        {{ ucfirst($pago['estado']) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 20px; color: #666;">
                    No se encontraron pagos con los filtros aplicados
                </td>
            </tr>
            @endforelse
        </tbody>
        @if(count($pagos) > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>TOTAL GENERAL:</strong></td>
                <td class="text-right"><strong>${{ number_format($pagos->sum('monto'), 2) }}</strong></td>
                <td class="text-center">{{ count($pagos) }} pagos</td>
            </tr>
        </tfoot>
        @endif
    </table>

    @if(count($pagos) > 0)
    <div style="margin-top: 30px;">
        <h3>📈 Resumen por Estado:</h3>
        <table style="width: 50%; margin-top: 10px;">
            <thead>
                <tr>
                    <th>Estado</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Monto Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $resumenEstados = collect($pagos)->groupBy('estado')->map(function($group, $estado) {
                        return [
                            'estado' => $estado,
                            'cantidad' => $group->count(),
                            'monto' => $group->sum('monto')
                        ];
                    });
                @endphp
                @foreach($resumenEstados as $resumen)
                <tr>
                    <td>{{ ucfirst($resumen['estado']) }}</td>
                    <td class="text-center">{{ $resumen['cantidad'] }}</td>
                    <td class="text-right">${{ number_format($resumen['monto'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        <h3>💰 Resumen por Tipo de Pago:</h3>
        <table style="width: 50%; margin-top: 10px;">
            <thead>
                <tr>
                    <th>Tipo de Pago</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Monto Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $resumenTipos = collect($pagos)->groupBy('tipo_pago')->map(function($group, $tipo) {
                        return [
                            'tipo' => $tipo,
                            'cantidad' => $group->count(),
                            'monto' => $group->sum('monto')
                        ];
                    });
                @endphp
                @foreach($resumenTipos as $resumen)
                <tr>
                    <td>{{ ucfirst($resumen['tipo']) }}</td>
                    <td class="text-center">{{ $resumen['cantidad'] }}</td>
                    <td class="text-right">${{ number_format($resumen['monto'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Sistema de Gestión de Vehículos - Reporte generado automáticamente</p>
        <p>Página 1 de 1 | {{ count($pagos) }} registros mostrados</p>
    </div>
</body>
</html>