<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobación de Viáticos</title>
    <style>
        @page {
            margin: 0.5cm 0.5cm; /* Wide margins resembling Image 2 */
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt; 
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            text-align: center;
            vertical-align: middle;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            font-size: 8pt;
        }
        .info-table td {
            padding: 3px;
            border: 1px solid black;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px; /* Reduced further to fit on one page */
            font-size: 8pt;
        }
        .data-table th, .data-table td {
            border: 1px solid black;
            padding: 3px;
            text-align: center;
        }
        .section-header {
            background-color: #FFFF00; /* Yellow */
            font-weight: bold;
            text-align: left;
            padding-left: 5px;
        }
        .total-row {
            font-weight: bold;
        }
        .signatures {
            width: 100%;
            margin-top: 20px; /* Reduced vertical space */
            border-collapse: collapse;
        }
        .signatures td {
            text-align: center;
            vertical-align: bottom;
            height: 40px;
            font-size: 8pt;
        }
        .sig-line {
            border-top: 1px solid black;
            width: 60%; 
            margin: 0 auto;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">
        COMISIÓN DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO DE QUINTANA ROO<br>
        DIRECCIÓN DE CONTABILIDAD COMPROBACIÓN DE GASTOS
    </div>
    <div style="text-align: right; font-size: 7pt; margin-bottom: 5px;">
        Nº DE VIÁTICO: {{ $oficioNumber }}
    </div>
    <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">
        COMPROBACIÓN DE VIÁTICOS DEVENGADOS
    </div>

    <!-- Info Table -->
    <table class="info-table">
        <tr>
            <td style="width: 15%; font-weight: bold;">NOMBRE:</td>
            <td>{{ $employee->nombre_completo ?? '' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">PUESTO:</td>
            <td>{{ $employee->puesto ?? '' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">ADSCRIPCIÓN:</td>
            <td>ORGANISMO OPERADOR JOSÉ MARÍA MORELOS</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">CONCEPTO:</td>
            <td>REQ.{{ $requirement->formatted_number }} COMPROBACIÓN DE VIÁTICOS DEVENGADOS A {{ $requirement->destination_city }}, {{ $requirement->destination_state }} DEL {{ $requirement->start_date ? $requirement->start_date->format('d') : '' }} DE {{ $requirement->start_date ? $requirement->start_date->locale('es')->monthName : '' }} DEL {{ $requirement->start_date ? $requirement->start_date->format('Y') : '' }}. {{ $requirement->description }}</td>
        </tr>
    </table>

    <!-- Main Data Table Structure -->
    <table class="data-table">
        <thead>
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <th style="width: 8%;">FECHA</th>
                <th style="width: 10%;">FACTURA</th>
                <th style="width: 10%;">RFC</th>
                <th style="width: 22%;">NOMBRE</th>
                <th style="width: 20%;">TIPO DE GASTO</th>
                <th style="width: 6%;">SUBTOTAL</th>
                <th style="width: 6%;">OTROS<br>IMPUESTOS</th>
                <th style="width: 6%;">IVA</th>
                <th style="width: 6%;">RETENCION<br>(RESICO)</th>
                <th style="width: 6%;">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            <!-- TRANSPORTE SECTION -->
            <!-- Header Row -->
            <tr>
                <td></td><td></td><td></td><td></td>
                <td class="section-header" style="text-align: left; background-color: #FFFF00;">CUOTA DE TRANSPORTE RECIBIDO</td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="text-align: right; font-weight: bold; background-color: #FFFF00;">{{ number_format($cuotaPasaje, 2) }}</td>
            </tr>
            <!-- Items -->
            @forelse($transporteItems as $item)
            <tr>
                <td>{{ $item->invoice_date ? $item->invoice_date->format('Y-m-d') : '' }}</td>
                <td>{{ $item->invoice_folio }}</td>
                <td>{{ $item->provider_rfc }}</td>
                <td>{{ $item->provider_name }}</td>
                <td>{{ $item->partida->nombre }}</td>
                <td>{{ number_format($item->invoice_subtotal, 2) }}</td>
                <td>0.00</td>
                <td>{{ number_format($item->invoice_iva, 2) }}</td>
                <td>{{ number_format(($item->invoice_retention_isr + $item->invoice_retention_iva), 2) }}</td>
                <td>{{ number_format($item->invoice_total, 2) }}</td>
            </tr>
            @empty
            <!-- Print 3 empty rows if no items -->
            @for($i=0; $i<3; $i++)
            <tr>
                <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                <td>0.00</td><td>0.00</td><td>0.00</td><td>0.00</td><td>0.00</td>
            </tr>
            @endfor
            @endforelse
            <!-- SUBTOTAL Row -->
            <tr class="total-row">
                <td colspan="4" style="border-right: 1px solid black;"></td>
                <td style="text-align: left;">SUBTOTAL</td>
                <td>{{ number_format($transporteItems->sum('invoice_subtotal'), 2) }}</td>
                <td>0.00</td>
                <td>{{ number_format($transporteItems->sum('invoice_iva'), 2) }}</td>
                <td>{{ number_format($transporteItems->sum('invoice_retention_isr') + $transporteItems->sum('invoice_retention_iva'), 2) }}</td>
                <td>{{ number_format($totalTransporteComprobado, 2) }}</td>
            </tr>
            <!-- DEVOLUCION Row -->
            <tr class="total-row">
                <td colspan="4"></td>
                <td style="text-align: left;">DEVOLUCION</td>
                <td colspan="4"></td>
                <td style="text-align: right;">{{ number_format($cuotaPasaje - $totalTransporteComprobado, 2) }}</td>
            </tr>

            <!-- Spacer Row (with borders to keep grid) -->
            <tr><td colspan="10" style="height: 10px;"></td></tr>

            <!-- VIATICOS SECTION -->
            <tr>
                <td></td><td></td><td></td><td></td>
                <td class="section-header" style="text-align: left; background-color: #FFFF00;">CUOTA DE VIÁTICOS</td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="text-align: right; font-weight: bold; background-color: #FFFF00;">{{ number_format($cuotaViaticos, 2) }}</td>
            </tr>
            @forelse($viaticosItems as $item)
            <tr>
                <td>{{ $item->invoice_date ? $item->invoice_date->format('Y-m-d') : '' }}</td>
                <td>{{ $item->invoice_folio }}</td>
                <td>{{ $item->provider_rfc }}</td>
                <td>{{ $item->provider_name }}</td>
                <td>{{ $item->description ?: $item->partida->nombre }}</td>
                <td>{{ number_format($item->invoice_subtotal, 2) }}</td>
                <td>0.00</td>
                <td>{{ number_format($item->invoice_iva, 2) }}</td>
                <td>{{ number_format(($item->invoice_retention_isr + $item->invoice_retention_iva), 2) }}</td>
                <td>{{ number_format($item->invoice_total, 2) }}</td>
            </tr>
            @empty
             @if(count($viaticosItems) == 0 && count($transporteItems) == 0 && count($hospedajeItems) == 0)
                <!-- Only show empty rows if truly empty to avoid clutter, or just 1 -->
                <tr>
                    <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                    <td>0.00</td><td>0.00</td><td>0.00</td><td>0.00</td><td>0.00</td>
                </tr>
             @endif
            @endforelse
            
            <!-- ANEXO 4 ROW -->
            <tr>
                <td></td>
                <td></td>
                <td>ANEXO 4</td>
                <td>GASTOS MENORES</td>
                <td></td>
                <td>0.00</td>
                <td>0.00</td>
                <td>0.00</td>
                <td>0.00</td>
                <td>0.00</td>
            </tr>

            <tr class="total-row">
                <td colspan="4"></td>
                <td style="text-align: left;">SUBTOTAL</td>
                <td>{{ number_format($viaticosItems->sum('invoice_subtotal'), 2) }}</td>
                <td>0.00</td>
                <td>{{ number_format($viaticosItems->sum('invoice_iva'), 2) }}</td>
                <td>{{ number_format($viaticosItems->sum('invoice_retention_isr') + $viaticosItems->sum('invoice_retention_iva'), 2) }}</td>
                <td>{{ number_format($totalViaticosComprobado, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="4"></td>
                <td style="text-align: left;">DEVOLUCION</td>
                <td colspan="4"></td>
                <td style="text-align: right;">{{ number_format($cuotaViaticos - $totalViaticosComprobado, 2) }}</td>
            </tr>

            <!-- Spacer Row -->
            <tr><td colspan="10" style="height: 10px; border-left: 1px solid black; border-right: 1px solid black;"></td></tr>

            <!-- FINAL SUMMARY SECTION (Integrated) -->
            <!-- Row 1: Total Pagos -->
            <tr>
                <td colspan="4" style="border: none;"></td> <!-- Empty left side -->
                <td style="text-align: left;">TOTAL PAGOS VIÁTICOS, HOSPEDAJE Y PASAJES</td>
                <td style="text-align: right;">{{ number_format($transporteItems->sum('invoice_subtotal') + $viaticosItems->sum('invoice_subtotal') + $hospedajeItems->sum('invoice_subtotal'), 2) }}</td>
                <td style="text-align: right;">0.00</td>
                <td style="text-align: right;">{{ number_format($transporteItems->sum('invoice_iva') + $viaticosItems->sum('invoice_iva') + $hospedajeItems->sum('invoice_iva'), 2) }}</td>
                <td style="text-align: right;">{{ number_format(
                    $transporteItems->sum('invoice_retention_isr') + $transporteItems->sum('invoice_retention_iva') +
                    $viaticosItems->sum('invoice_retention_isr') + $viaticosItems->sum('invoice_retention_iva') +
                    $hospedajeItems->sum('invoice_retention_isr') + $hospedajeItems->sum('invoice_retention_iva'), 2)
                }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($cuotaViaticos + $cuotaPasaje + $cuotaHospedaje, 2) }}</td>
            </tr>
            <!-- Row 2: Total Pasajes -->
            <tr>
                <td colspan="4" style="border: none;"></td>
                <td style="text-align: left;">TOTAL PASAJES</td>
                <td style="text-align: right;">{{ number_format($transporteItems->sum('invoice_subtotal'), 2) }}</td>
                <td style="text-align: right;">0.00</td>
                <td style="text-align: right;">{{ number_format($transporteItems->sum('invoice_iva'), 2) }}</td>
                <td style="text-align: right;">{{ number_format($transporteItems->sum('invoice_retention_isr') + $transporteItems->sum('invoice_retention_iva'), 2) }}</td>
                <td style="text-align: right;">{{ number_format($totalTransporteComprobado, 2) }}</td>
            </tr>
            <!-- Row 3: Total Alimentos -->
            <tr>
                <td colspan="4" style="border: none;"></td>
                <td style="text-align: left;">TOTAL ALIMENTOS (VIÁTICOS)</td>
                <td style="text-align: right;">{{ number_format($viaticosItems->sum('invoice_subtotal'), 2) }}</td>
                <td style="text-align: right;">0.00</td>
                <td style="text-align: right;">{{ number_format($viaticosItems->sum('invoice_iva'), 2) }}</td>
                <td style="text-align: right;">{{ number_format($viaticosItems->sum('invoice_retention_isr') + $viaticosItems->sum('invoice_retention_iva'), 2) }}</td>
                <td style="text-align: right;">{{ number_format($totalViaticosComprobado, 2) }}</td>
            </tr>
            <!-- Row 4: Total Hospedaje -->
            <tr>
                <td colspan="4" style="border: none;"></td>
                <td style="text-align: left;">TOTAL HOSPEDAJE</td>
                <td style="text-align: right;">{{ number_format($hospedajeItems->sum('invoice_subtotal'), 2) }}</td>
                <td style="text-align: right;">0.00</td>
                <td style="text-align: right;">{{ number_format($hospedajeItems->sum('invoice_iva'), 2) }}</td>
                <td style="text-align: right;">{{ number_format($hospedajeItems->sum('invoice_retention_isr') + $hospedajeItems->sum('invoice_retention_iva'), 2) }}</td>
                <td style="text-align: right;">{{ number_format($totalHospedajeComprobado, 2) }}</td>
            </tr>
            <!-- Row 5: Total de Viaticos Comprobados -->
            <tr>
                <td colspan="4" style="border: none;"></td>
                <td style="text-align: left;">TOTAL DE VIATICOS COMPROBADOS</td>
                <td style="text-align: right;">{{ number_format($transporteItems->sum('invoice_subtotal') + $viaticosItems->sum('invoice_subtotal') + $hospedajeItems->sum('invoice_subtotal'), 2) }}</td>
                <td style="text-align: right;">0.00</td>
                <td style="text-align: right;">{{ number_format($transporteItems->sum('invoice_iva') + $viaticosItems->sum('invoice_iva') + $hospedajeItems->sum('invoice_iva'), 2) }}</td>
                <td style="text-align: right;">{{ number_format(
                    $transporteItems->sum('invoice_retention_isr') + $transporteItems->sum('invoice_retention_iva') +
                    $viaticosItems->sum('invoice_retention_isr') + $viaticosItems->sum('invoice_retention_iva') +
                    $hospedajeItems->sum('invoice_retention_isr') + $hospedajeItems->sum('invoice_retention_iva'), 2)
                }}</td>
                <td style="text-align: right;">{{ number_format($totalTransporteComprobado + $totalViaticosComprobado + $totalHospedajeComprobado, 2) }}</td>
            </tr>
            <!-- Row 6: Excedente -->
            <tr>
                <td colspan="4" style="border: none;"></td>
                <td style="text-align: left;">EXCEDENTE</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: right;">{{ number_format(($cuotaViaticos + $cuotaPasaje + $cuotaHospedaje) - ($totalTransporteComprobado + $totalViaticosComprobado + $totalHospedajeComprobado), 2) }}</td>
            </tr>
            <!-- Row 7: Deposito a Pagar -->
            <tr>
                <td colspan="4" style="border: none;"></td> <!-- Empty left side, no border -->
                <td colspan="5" style="text-align: center; font-weight: bold; border-bottom: 2px solid black;">DEPOSITO A PAGAR</td>
                <td style="text-align: right; font-weight: bold; border-bottom: 2px solid black;">{{ number_format($cuotaViaticos + $cuotaPasaje + $cuotaHospedaje, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Signatures -->

    <!-- Signatures -->
    <table class="signatures">
        <tr>
            <td style="width: 50%;">
                SOLICITO
                <br><br><br><br>
                <div class="sig-line"></div>
                {{ $employee->nombre_completo ?? 'NOMBRE DEL COMISIONADO' }}<br>
                {{ $employee->puesto ?? 'PUESTO' }}
            </td>
            <td style="width: 50%;">
                AUTORIZO
                <br><br><br><br>
                <div class="sig-line"></div>
                PLC. MARIANO CERVANTES SANCHEZ<br>
                SUBGERENTE ADMINISTRATIVO
            </td>
        </tr>
    </table>

</body>
</html>
