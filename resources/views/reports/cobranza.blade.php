<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Relación de ingresos por recaudación</title>
    <style>
        @page { margin: 18px 22px; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 8px; }
        h1 { font-size: 14px; margin: 0; text-align: center; text-transform: uppercase; }
        h2 { font-size: 10px; margin: 3px 0 12px; text-align: center; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #374151; padding: 4px 3px; }
        th { background: #e5e7eb; text-align: center; text-transform: uppercase; }
        td.number { text-align: right; white-space: nowrap; }
        td.center { text-align: center; }
        .total { background: #fef3c7; font-weight: bold; }
        .summary { margin-top: 12px; width: 100%; }
        .summary td { border: 0; padding: 3px; }
        .line { border-top: 2px solid #111827; margin-top: 10px; padding-top: 7px; }
    </style>
</head>
<body>
    <h1>Comisión de Agua Potable y Alcantarillado del Estado de Quintana Roo</h1>
    <h2>Relación de ingresos por recaudación del {{ \Carbon\Carbon::parse($data['fecha_desde'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($data['fecha_hasta'])->format('d/m/Y') }} {{ \Carbon\Carbon::parse($data['fecha_hasta'])->format('Y') }}</h2>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Diferencia capturado de más por cajera</th>
                @foreach ($banks as $bank)
                    <th>{{ $bank->name }}<br>{{ $bank->account_number }}</th>
                @endforeach
                <th>Total bancos</th>
                <th>Póliza ing</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="center">{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                    <td>RECAUDACION DEL MAC</td>
                    <td class="number">0.00</td>
                    @foreach ($banks as $bank)
                        <td class="number">{{ number_format($row['amounts'][(string) $bank->id] ?? 0, 2) }}</td>
                    @endforeach
                    <td class="number">{{ number_format($row['total'], 2) }}</td>
                    <td class="number">{{ number_format($row['policy'], 2) }}<br><small>{{ $row['policy_number'] }}</small></td>
                </tr>
            @empty
                <tr><td colspan="{{ $banks->count() + 5 }}" class="center">No hay cobranzas para el rango seleccionado.</td></tr>
            @endforelse
            @if ($rows->isNotEmpty())
                <tr class="total">
                    <td colspan="3" class="number">TOTALES</td>
                    @foreach ($banks as $bank)
                        <td class="number">{{ number_format($bankTotals[(string) $bank->id] ?? 0, 2) }}</td>
                    @endforeach
                    <td class="number">{{ number_format($totalBanks, 2) }}</td>
                    <td class="number">{{ number_format($policyTotal, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="summary">
        <tr><td><strong>Suma de ingresos depositados:</strong> {{ number_format($totalBanks, 2) }}</td><td><strong>Total pólizas de ingreso:</strong> {{ number_format($policyTotal, 2) }}</td></tr>
        <tr><td><strong>ING. DE OTRAS CUENTAS AL {{ \Carbon\Carbon::parse($data['fecha_hasta'])->format('d/m/Y') }}:</strong> 0.00</td><td><strong>Diferencia entre bancos y póliza:</strong> {{ number_format($totalBanks - $policyTotal, 2) }}</td></tr>
        <tr><td><strong>MENOS: INGRESOS SEGÚN LAYOUT FACTURADO:</strong> 0.00</td><td><strong>DIFERENCIA PARA AJUSTE DEL MES:</strong> 0.00</td></tr>
        <tr><td><strong>DIF. ENTRE LO COBRADO Y GENERADO AL {{ \Carbon\Carbon::parse($data['fecha_hasta'])->format('d/m/Y') }}:</strong> {{ number_format($totalBanks - $policyTotal, 2) }}</td><td><strong>PAGOS DE LA DRAEF:</strong> {{ number_format($draefTotal, 2) }}</td></tr>
    </table>

    <div class="line">
        @foreach ($banks as $bank)
            <div><strong>TOTAL INGRESO {{ $bank->name }} {{ $bank->account_number }} DEL {{ \Carbon\Carbon::parse($data['fecha_desde'])->format('d/m/Y') }} AL {{ \Carbon\Carbon::parse($data['fecha_hasta'])->format('d/m/Y') }}:</strong> {{ number_format($bankTotals[(string) $bank->id] ?? 0, 2) }}</div>
        @endforeach
        <div><strong>SUMA DE TODOS LOS BANCOS:</strong> {{ number_format($totalBanks, 2) }}</div>
    </div>
</body>
</html>
