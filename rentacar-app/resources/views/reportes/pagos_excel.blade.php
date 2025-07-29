<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Fecha</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Monto (Bs.)</th>
            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Tipo de Pago</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pagos as $pago)
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') }}</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ number_format($pago->monto, 2, ',', '.') }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ ucfirst($pago->tipo_pago) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: center; color: #999;">
                    No hay pagos registrados en el período seleccionado.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
