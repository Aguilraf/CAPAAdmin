<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Relación de ingresos por recaudación</title>
    <style>
        @page { margin: 18px 22px; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 9px; }
        h1 { font-size: 15px; margin: 0; text-align: center; text-transform: uppercase; }
        h2 { font-size: 11px; margin: 3px 0 12px; text-align: center; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #374151; padding: 4px 3px; word-wrap: break-word; }
        tr { page-break-inside: avoid; }
        th { background: #e5e7eb; text-align: center; text-transform: uppercase; }
        td.number { text-align: right; white-space: nowrap; }
        td.center { text-align: center; }
        td.nowrap { white-space: nowrap; }
        .total { background: #fef3c7; font-weight: bold; }
        .col-fecha { width: 62px; }
        .col-concepto { width: 70px; }
        .col-cajera { width: 55px; }
        .col-poliza { width: 130px; }
        .summary-wrap { width: 100%; margin-top: 12px; }
        .summary-wrap td { border: none; vertical-align: top; padding: 0 6px; }
        .summary-table { table-layout: auto; }
        .summary-table td { padding: 3px 4px; }
        .highlight { background: #fef3c7; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Comisión de Agua Potable y Alcantarillado del Estado de Quintana Roo</h1>
    <h2>Relación de ingresos por recaudación del {{ \Carbon\Carbon::parse($data['fecha_desde'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($data['fecha_hasta'])->format('d/m/Y') }} {{ \Carbon\Carbon::parse($data['fecha_hasta'])->format('Y') }}</h2>

    <table>
        <thead>
            <tr>
                <th class="col-fecha">Fecha</th>
                <th class="col-concepto">Concepto</th>
                <th class="col-cajera">Dif. cajera</th>
                @foreach ($banks as $bank)
                    <th>{{ $bank['name'] }}<br>{{ $bank['account_number'] }}</th>
                @endforeach
                <th>Total bancos</th>
                <th class="col-poliza">Póliza ing</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="center nowrap">{{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('d/m/Y') : '' }}</td>
                    <td class="nowrap">{{ $row['date'] ? 'REC DEL MAC' : '' }}</td>
                    <td class="number">{{ $row['date'] ? '0.00' : '' }}</td>
                    @foreach ($banks as $bank)
                        <td class="number">{{ is_null($row['amounts'][$bank['id']] ?? null) ? '' : number_format($row['amounts'][$bank['id']], 2) }}</td>
                    @endforeach
                    <td class="number">{{ is_null($row['total']) ? '' : number_format($row['total'], 2) }}</td>
                    <td class="number">
                        @if (($row['policy_line']['label'] ?? false))
                            <strong>{{ $row['policy_line']['text'] }}</strong>
                        @else
                            {{ number_format($row['policy_line']['value'] ?? 0, 2) }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $banks->count() + 5 }}" class="center">No hay cobranzas para el rango seleccionado.</td></tr>
            @endforelse
            @if ($rows->isNotEmpty())
                <tr class="total">
                    <td colspan="3" class="number">TOTALES</td>
                    @foreach ($banks as $bank)
                        <td class="number">{{ number_format($bankTotals[$bank['id']] ?? 0, 2) }}</td>
                    @endforeach
                    <td class="number">{{ number_format($totalBanks, 2) }}</td>
                    <td class="number">{{ number_format($policyTotal, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    @if ($rows->isNotEmpty())
        @php $diferencia = $totalBanks - $policyTotal; @endphp
        <table class="summary-wrap">
            <tr>
                <td style="width: 55%;">
                    <table class="summary-table">
                        @foreach ($banks as $bank)
                            @continue(str_starts_with($bank['id'], 'sin-banco-'))
                            <tr><td>TOTAL ING. {{ $bank['name'] }} {{ $bank['account_number'] }} DEL {{ \Carbon\Carbon::parse($data['fecha_desde'])->format('d/m/Y') }} AL {{ \Carbon\Carbon::parse($data['fecha_hasta'])->format('d/m/Y') }}</td><td class="number">{{ number_format($bankTotals[$bank['id']] ?? 0, 2) }}</td></tr>
                        @endforeach
                        <tr><td>ING. DE OTRAS CUENTAS DEL {{ \Carbon\Carbon::parse($data['fecha_desde'])->format('d/m/Y') }} AL {{ \Carbon\Carbon::parse($data['fecha_hasta'])->format('d/m/Y') }}</td><td class="number">0.00</td></tr>
                        <tr style="font-weight:bold;"><td>SUMA DE LOS INGRESOS DEPOSITADOS</td><td class="number">{{ number_format($totalBanks, 2) }}</td></tr>
                        <tr style="font-weight:bold;"><td>MENOS: INGRESOS SEGÚN LAYOUT FACTURADO</td><td class="number">{{ number_format($policyTotal, 2) }}</td></tr>
                        <tr class="highlight"><td>DIF, ENTRE LO COBRADO Y GENERADO AL {{ \Carbon\Carbon::parse($data['fecha_hasta'])->format('d/m/Y') }}</td><td class="number">{{ number_format($diferencia, 2) }}</td></tr>
                    </table>
                </td>
                <td style="width: 45%;">
                    <table class="summary-table">
                        <tr class="highlight"><td colspan="2">DIFERENCIA PARA AJUSTE DEL MES</td><td class="number">{{ number_format($diferencia, 2) }}</td></tr>
                    </table>
                    <table class="summary-table" style="margin-top: 6px;">
                        <tr>
                            <th>MENOS PAGOS DE LA DRAEF: FECHA</th>
                            <th>FACTURACIÓN</th>
                            <th>IVA</th>
                            <th>TOTAL</th>
                        </tr>
                        @forelse ($draefPayments as $payment)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($payment['date'])->format('d/m/Y') }}</td>
                                <td class="number">{{ number_format($payment['subtotal'], 2) }}</td>
                                <td class="number">{{ number_format($payment['iva'], 2) }}</td>
                                <td class="number">{{ number_format($payment['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">Sin pagos DRAEF en el periodo.</td></tr>
                        @endforelse
                        <tr style="font-weight:bold;">
                            <td>SUMAS</td>
                            <td class="number">{{ number_format($draefSubtotalTotal, 2) }}</td>
                            <td class="number">{{ number_format($draefIvaTotal, 2) }}</td>
                            <td class="number">{{ number_format($draefTotal, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    @endif

    <table class="summary-wrap" style="margin-top: 60px;">
        <tr>
            <td style="width: 50%; text-align: center;">ELABORÓ</td>
            <td style="width: 50%; text-align: center;">VO.BO</td>
        </tr>
        <tr>
            <td style="width: 50%; text-align: center; padding-top: 30px;">
                <div style="border-top: 1px solid #111827; width: 260px; margin: 0 auto; padding-top: 4px;">
                    {{ strtoupper($subgerenteAdministrativo->nombre ?? '') }}<br>
                    {{ strtoupper($subgerenteAdministrativo->puesto ?? 'SUBGERENTE ADMINISTRATIVO') }}
                </div>
            </td>
            <td style="width: 50%; text-align: center; padding-top: 30px;">
                <div style="border-top: 1px solid #111827; width: 260px; margin: 0 auto; padding-top: 4px;">
                    {{ strtoupper($subgerenteComercial->nombre ?? '') }}<br>
                    {{ strtoupper($subgerenteComercial->puesto ?? 'SUBGERENTE COMERCIAL') }}
                </div>
            </td>
        </tr>
    </table>

    <script type="text/php">
        if ( isset($pdf) ) {
            $font = $fontMetrics->get_font("Arial", "normal");
            $pdf->page_text($pdf->get_width() / 2 - 45, $pdf->get_height() - 30, "PÁGINA {PAGE_NUM} DE {PAGE_COUNT}", $font, 8, array(0,0,0));
        }
    </script>
</body>
</html>
