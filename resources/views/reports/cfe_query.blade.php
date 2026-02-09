<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta CFE</title>
    <style>
        @page { margin: 50px 30px; }
        body { font-family: Arial, sans-serif; font-size: 9pt; }
        h1 { text-align: center; font-size: 14pt; margin-bottom: 20px; }
        .filters { background: #f0f0f0; padding: 10px; margin-bottom: 15px; font-size: 8pt; }
        .filters strong { display: inline-block; width: 100px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #4a5568; color: white; padding: 8px; text-align: left; font-size: 8pt; }
        td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 8pt; }
        .text-right { text-align: right; }
        .totals { background: #e6f3ff; font-weight: bold; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 7pt; color: #666; }
    </style>
</head>
<body>
    <h1>CONSULTA DE HISTORIAL POR SERVICIO CFE</h1>

    <!-- Filters Applied -->
    @if(!empty(array_filter($filters)))
    <div class="filters">
        <strong>Filtros Aplicados:</strong><br>
        @if($filters['year'] ?? false)
            <strong>Año:</strong> {{ $filters['year'] }}<br>
        @endif
        @if($filters['requirement_number'] ?? false)
            <strong>Req. #:</strong> {{ $filters['requirement_number'] }}<br>
        @endif
        @if($filters['rpu'] ?? false)
            <strong>RPU:</strong> {{ $filters['rpu'] }}<br>
        @endif
        @if($filters['search'] ?? false)
            <strong>Ubicación:</strong> {{ $filters['search'] }}<br>
        @endif
        @if($filters['date_from'] ?? false)
            <strong>Desde:</strong> {{ \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') }}<br>
        @endif
        @if($filters['date_to'] ?? false)
            <strong>Hasta:</strong> {{ \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') }}<br>
        @endif
        @if($filters['amount_min'] ?? false)
            <strong>Monto Mín:</strong> ${{ number_format($filters['amount_min'], 2) }}<br>
        @endif
        @if($filters['amount_max'] ?? false)
            <strong>Monto Máx:</strong> ${{ number_format($filters['amount_max'], 2) }}<br>
        @endif
    </div>
    @endif

    <!-- Results Table -->
    <table>
        <thead>
            <tr>
                <th>Año</th>
                <th>Req. #</th>
                <th>RPU</th>
                <th>Poblado</th>
                <th>Dirección</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">IVA</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalSubtotal = 0;
                $totalIva = 0;
                $totalTotal = 0;
            @endphp
            @foreach($receipts as $receipt)
                @php
                    $parts = explode(',', $receipt->description, 2);
                    $poblado = trim($parts[0] ?? '');
                    $direccion = trim($parts[1] ?? '');
                    
                    $totalSubtotal += $receipt->subtotal;
                    $totalIva += $receipt->iva;
                    $totalTotal += $receipt->total;
                @endphp
                <tr>
                    <td>{{ $receipt->requirement->year ?? '' }}</td>
                    <td>{{ $receipt->requirement->requirement_number ?? '' }}</td>
                    <td>{{ $receipt->rpu }}</td>
                    <td>{{ $poblado }}</td>
                    <td>{{ $direccion }}</td>
                    <td class="text-right">${{ number_format($receipt->subtotal, 2) }}</td>
                    <td class="text-right">${{ number_format($receipt->iva, 2) }}</td>
                    <td class="text-right">${{ number_format($receipt->total, 2) }}</td>
                </tr>
            @endforeach
            
            <!-- Totals Row -->
            <tr class="totals">
                <td colspan="5" class="text-right">TOTALES ({{ $receipts->count() }} registros)</td>
                <td class="text-right">${{ number_format($totalSubtotal, 2) }}</td>
                <td class="text-right">${{ number_format($totalIva, 2) }}</td>
                <td class="text-right">${{ number_format($totalTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
