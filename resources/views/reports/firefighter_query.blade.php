<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consulta de Historial por Comunidad - Bomberos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .filters {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        .filters p {
            margin: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #e0e0e0;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>CONSULTA DE HISTORIAL POR COMUNIDAD - BOMBEROS</h1>

    @php
        $totalSubtotal = 0;
        $totalCommission = 0;
        $totalTotal = 0;
    @endphp

    <div class="filters">
        <strong>Filtros Aplicados:</strong>
        @if(!empty($filters['year']))
            <p><strong>Año:</strong> {{ $filters['year'] }}</p>
        @endif
        @if(!empty($filters['requirement_number']))
            <p><strong>Req. #:</strong> {{ $filters['requirement_number'] }}</p>
        @endif
        @if(!empty($filters['community_id']))
            <p><strong>Comunidad ID:</strong> {{ $filters['community_id'] }}</p>
        @endif
        @if(!empty($filters['firefighter_id']))
            <p><strong>Bombero ID:</strong> {{ $filters['firefighter_id'] }}</p>
        @endif
        @if(!empty($filters['date_from']))
            <p><strong>Fecha Desde:</strong> {{ \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') }}</p>
        @endif
        @if(!empty($filters['date_to']))
            <p><strong>Fecha Hasta:</strong> {{ \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') }}</p>
        @endif
        @if(!empty($filters['amount_min']))
            <p><strong>Monto Mínimo:</strong> ${{ number_format($filters['amount_min'], 2) }}</p>
        @endif
        @if(!empty($filters['amount_max']))
            <p><strong>Monto Máximo:</strong> ${{ number_format($filters['amount_max'], 2) }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Año</th>
                <th>Req. #</th>
                <th>Fecha</th>
                <th>Comunidad</th>
                <th>Bombero</th>
                <th class="text-right">Total Recaudado</th>
                <th class="text-right">Comisión</th>
                <th class="text-right">Neto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($captures as $capture)
                @php
                    $totalSubtotal += $capture->subtotal;
                    $totalCommission += $capture->commission;
                    $totalTotal += ($capture->subtotal - $capture->commission);
                @endphp
                <tr>
                    <td>{{ $capture->year }}</td>
                    <td>{{ $capture->requirement_number ?? '' }}</td>
                    <td>{{ $capture->date ? \Carbon\Carbon::parse($capture->date)->format('d/m/Y') : '' }}</td>
                    <td>{{ $capture->community->name ?? '' }}</td>
                    <td>{{ $capture->firefighter->name ?? '' }}</td>
                    <td class="text-right">${{ number_format($capture->subtotal, 2) }}</td>
                    <td class="text-right">${{ number_format($capture->commission, 2) }}</td>
                    <td class="text-right">${{ number_format($capture->subtotal - $capture->commission, 2) }}</td>
                </tr>
            @endforeach
            
            <!-- Totals Row -->
            <tr class="totals">
                <td colspan="5" class="text-right">TOTALES ({{ $captures->count() }} registros)</td>
                <td class="text-right">${{ number_format($totalSubtotal, 2) }}</td>
                <td class="text-right">${{ number_format($totalCommission, 2) }}</td>
                <td class="text-right">${{ number_format($totalTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
