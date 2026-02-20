<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobación de Viáticos</title>
    <style>
        @page {
            margin: 0.5cm 0.5cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 7pt;
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
            margin-bottom: 3px;
            font-size: 7pt;
        }
        .info-table td {
            padding: 2px;
            border: 1px solid black;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
            font-size: 7pt;
        }
        .data-table th, .data-table td {
            border: 1px solid black;
            padding: 2px;
            text-align: center;
        }
        .section-header {
            background-color: #FFFF00;
            font-weight: bold;
            text-align: left;
            padding-left: 5px;
        }
        .total-row {
            font-weight: bold;
        }
        .signatures {
            width: 100%;
            margin-top: 8px;
            border-collapse: collapse;
        }
        .signatures td {
            text-align: center;
            vertical-align: bottom;
            height: 30px;
            font-size: 7pt;
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
    <div style="text-align: center; font-weight: bold; margin-bottom: 3px;">
        COMISIÓN DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO DE QUINTANA ROO<br>
        DIRECCIÓN DE CONTABILIDAD COMPROBACIÓN DE GASTOS
    </div>
    <div style="text-align: right; font-size: 6pt; margin-bottom: 3px;">
        Nº DE VIÁTICO: {{ $oficioNumber }}
    </div>
    <div style="text-align: center; font-weight: bold; margin-bottom: 3px;">
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
            <td>REQ. ECO {{ $requirement->requirement_number }}/{{ $requirement->year }} ({{ $requirement->assignment_date ? $requirement->assignment_date->format('d/m/Y') : '' }}) COMPROBACIÓN DE VIÁTICOS DEVENGADOS A LA CD. {{ strtoupper($travelAllowance->destination_city ?? '') }} {{ $travelAllowance->justification ?? '' }}</td>
        </tr>
    </table>

    <!-- Main Data Table -->
    <table class="data-table">
        <thead>
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <th style="width: 8%;">FECHA</th>
                <th style="width: 10%;">FACTURA</th>
                <th style="width: 10%;">RFC</th>
                <th style="width: 20%;">NOMBRE</th>
                <th style="width: 20%;">TIPO DE GASTO</th>
                <th style="width: 6%;">SUBTOTAL</th>
                <th style="width: 6%;">OTROS<br>IMPUESTOS</th>
                <th style="width: 6%;">IVA</th>
                <th style="width: 6%;">RETENCION<br>(RESICO)</th>
                <th style="width: 8%;">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @php $nb = 'border:none;'; $sep = 'border-top:none;border-left:none;border-bottom:none;'; @endphp
            <!-- TRANSPORTE SECTION -->
            <tr>
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td class="section-header" style="text-align: left; background-color: #FFFF00;">TRANSPORTE: TERRESTRE/MARITIMO/TREN</td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="text-align: right; font-weight: bold; background-color: #FFFF00;">{{ number_format($cuotaPasaje, 2) }}</td>
            </tr>
            @forelse($transporteItems as $item)
            <tr>
                <td style="{{ $nb }}">{{ $item->invoice_date ? $item->invoice_date->format('Y-m-d') : '' }}</td>
                <td style="{{ $nb }}">{{ $item->invoice_folio }}</td>
                <td style="{{ $nb }}">{{ $item->provider_rfc }}</td>
                <td style="{{ $sep }}">{{ $item->provider_name }}</td>
                <td>{{ $item->partida->nombre }}</td>
                <td>{{ number_format($item->invoice_subtotal, 2) }}</td>
                <td>0.00</td>
                <td>{{ number_format($item->invoice_iva, 2) }}</td>
                <td>{{ number_format(($item->invoice_retention_isr + $item->invoice_retention_iva), 2) }}</td>
                <td>{{ number_format($item->invoice_total, 2) }}</td>
            </tr>
            @empty
            @for($i=0; $i<3; $i++)
            <tr>
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td><td style="{{ $nb }}"></td>
                <td>0.00</td><td>0.00</td><td>0.00</td><td>0.00</td><td>0.00</td>
            </tr>
            @endfor
            @endforelse
            <!-- TOTAL TRANSPORTE Row -->
            <tr class="total-row">
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td style="text-align: left;">TOTAL TRANSPORTE</td>
                <td>{{ number_format($transporteItems->sum('invoice_subtotal'), 2) }}</td>
                <td>0.00</td>
                <td>{{ number_format($transporteItems->sum('invoice_iva'), 2) }}</td>
                <td>{{ number_format($transporteItems->sum('invoice_retention_isr') + $transporteItems->sum('invoice_retention_iva'), 2) }}</td>
                <td>{{ number_format($totalTransporteComprobado, 2) }}</td>
            </tr>

            <!-- Spacer -->
            <tr><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td><td colspan="6" style="height:8px; border-top:none; border-bottom:none;"></td></tr>

            <!-- VIATICOS SECTION -->
            <tr>
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td class="section-header" style="text-align: left; background-color: #FFFF00;">CUOTA DE VIÁTICOS</td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="text-align: right; font-weight: bold; background-color: #FFFF00;">{{ number_format(min($totalViaticosComprobado, $cuotaViaticos), 2) }}</td>
            </tr>
            @forelse($viaticosItems as $item)
            <tr>
                <td style="{{ $nb }}">{{ $item->invoice_date ? $item->invoice_date->format('Y-m-d') : '' }}</td>
                <td style="{{ $nb }}">{{ $item->invoice_folio }}</td>
                <td style="{{ $nb }}">{{ $item->provider_rfc }}</td>
                <td style="{{ $sep }}">{{ $item->provider_name }}</td>
                <td>{{ $item->description ?: $item->partida->nombre }}</td>
                <td>{{ number_format($item->invoice_subtotal, 2) }}</td>
                <td>0.00</td>
                <td>{{ number_format($item->invoice_iva, 2) }}</td>
                <td>{{ number_format(($item->invoice_retention_isr + $item->invoice_retention_iva), 2) }}</td>
                <td>{{ number_format($item->invoice_total, 2) }}</td>
            </tr>
            @empty
            @endforelse
            <!-- ANEXO 4 ROW -->
            <tr>
                <td style="{{ $nb }}"></td>
                <td style="{{ $nb }}"></td>
                <td style="{{ $nb }}"></td>
                <td style="{{ $nb }}">ANEXO 4</td>
                <td>GASTOS MENORES</td>
                <td>0.00</td><td>0.00</td><td>0.00</td><td>0.00</td><td>0.00</td>
            </tr>
            <!-- TOTAL VIÁTICOS COMPROBADOS -->
            <tr class="total-row">
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td style="text-align: left;">TOTAL VIÁTICOS COMPROBADOS</td>
                <td>{{ number_format($viaticosItems->sum('invoice_subtotal'), 2) }}</td>
                <td>0.00</td>
                <td>{{ number_format($viaticosItems->sum('invoice_iva'), 2) }}</td>
                <td>{{ number_format($viaticosItems->sum('invoice_retention_isr') + $viaticosItems->sum('invoice_retention_iva'), 2) }}</td>
                <td>{{ number_format($totalViaticosComprobado, 2) }}</td>
            </tr>
            <!-- APROVECHAMIENTO Viáticos -->
            <tr class="total-row">
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td style="text-align: left;">APROVECHAMIENTO</td>
                <td></td><td></td><td></td><td></td>
                <td style="text-align: right;">{{ number_format(min($cuotaViaticos, $totalViaticosComprobado) - $totalViaticosComprobado, 2) }}</td>
            </tr>

            <!-- Spacer -->
            <tr><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td><td colspan="6" style="height:8px; border-top:none; border-bottom:none;"></td></tr>

            <!-- HOSPEDAJE SECTION -->
            <tr>
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td class="section-header" style="text-align: left; background-color: #FFFF00;">CUOTA DE HOSPEDAJE</td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="background-color: #FFFF00;"></td>
                <td style="text-align: right; font-weight: bold; background-color: #FFFF00;">{{ number_format($cuotaHospedaje, 2) }}</td>
            </tr>
            @forelse($hospedajeItems as $item)
            <tr>
                <td style="{{ $nb }}">{{ $item->invoice_date ? $item->invoice_date->format('Y-m-d') : '' }}</td>
                <td style="{{ $nb }}">{{ $item->invoice_folio }}</td>
                <td style="{{ $nb }}">{{ $item->provider_rfc }}</td>
                <td style="{{ $sep }}">{{ $item->provider_name }}</td>
                <td>{{ $item->description ?: $item->partida->nombre }}</td>
                <td>{{ number_format($item->invoice_subtotal, 2) }}</td>
                <td>0.00</td>
                <td>{{ number_format($item->invoice_iva, 2) }}</td>
                <td>{{ number_format(($item->invoice_retention_isr + $item->invoice_retention_iva), 2) }}</td>
                <td>{{ number_format($item->invoice_total, 2) }}</td>
            </tr>
            @empty
            @for($i=0; $i<2; $i++)
            <tr>
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td><td style="{{ $nb }}"></td>
                <td>0.00</td><td>0.00</td><td>0.00</td><td>0.00</td><td>0.00</td>
            </tr>
            @endfor
            @endforelse
            <!-- TOTAL HOSPEDAJE COMPROBADO -->
            <tr class="total-row">
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td style="text-align: left;">TOTAL HOSPEDAJE COMPROBADO</td>
                <td>{{ number_format($hospedajeItems->sum('invoice_subtotal'), 2) }}</td>
                <td>0.00</td>
                <td>{{ number_format($hospedajeItems->sum('invoice_iva'), 2) }}</td>
                <td>{{ number_format($hospedajeItems->sum('invoice_retention_isr') + $hospedajeItems->sum('invoice_retention_iva'), 2) }}</td>
                <td>{{ number_format($totalHospedajeComprobado, 2) }}</td>
            </tr>
            <!-- APROVECHAMIENTO Hospedaje -->
            <tr class="total-row">
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td style="text-align: left;">APROVECHAMIENTO</td>
                <td></td><td></td><td></td><td></td>
                <td style="text-align: right;">{{ number_format(min($cuotaHospedaje, $totalHospedajeComprobado) - $totalHospedajeComprobado, 2) }}</td>
            </tr>

            <!-- Spacer -->
            <tr><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td><td colspan="6" style="height:8px; border-top:none; border-bottom:none;"></td></tr>

            <!-- FINAL SUMMARY SECTION -->
            <!-- Row 1: Total Pagos -->
            <tr>
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td style="text-align: left;">TOTAL PAGOS VIÁTICOS, HOSPEDAJE Y PASAJES</td>
                <td style="text-align: left; font-size: 6pt;">REG.CONTABLE</td>
                <td></td><td></td><td></td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($cuotaViaticos + $cuotaPasaje + $cuotaHospedaje, 2) }}</td>
            </tr>
            <!-- Row 2: Total Pasajes -->
            <tr>
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td style="text-align: left;">TOTAL PASAJES</td>
                <td style="text-align: right;">{{ number_format($totalTransporteComprobado, 2) }}</td>
                <td></td><td></td><td></td>
                <td style="text-align: right;">{{ number_format($totalTransporteComprobado, 2) }}</td>
            </tr>
            <!-- Row 3: Total Alimentos -->
            <tr>
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td style="text-align: left;">TOTAL ALIMENTOS</td>
                <td style="text-align: right;">{{ number_format($totalViaticosComprobado, 2) }}</td>
                <td></td><td></td><td></td>
                <td style="text-align: right;">{{ number_format($totalViaticosComprobado, 2) }}</td>
            </tr>
            <!-- Row 4: Total Hospedaje -->
            <tr>
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td style="text-align: left;">TOTAL HOSPEDAJE</td>
                <td style="text-align: right;">{{ number_format($totalHospedajeComprobado, 2) }}</td>
                <td></td><td></td><td></td>
                <td style="text-align: right;">{{ number_format($totalHospedajeComprobado, 2) }}</td>
            </tr>
            <!-- Row 5: Total IVA -->
            <tr>
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td style="text-align: left;">TOTAL IVA</td>
                <td style="text-align: right;">{{ number_format($transporteItems->sum('invoice_iva') + $viaticosItems->sum('invoice_iva') + $hospedajeItems->sum('invoice_iva'), 2) }}</td>
                <td></td><td></td><td></td>
                <td style="text-align: right;">{{ number_format($transporteItems->sum('invoice_iva') + $viaticosItems->sum('invoice_iva') + $hospedajeItems->sum('invoice_iva'), 2) }}</td>
            </tr>
            <!-- Row 6: Aprovechamiento -->
            <tr>
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td style="text-align: left;">APROVECHAMIENTO</td>
                <td style="text-align: right;">{{ number_format((min($cuotaPasaje,$totalTransporteComprobado) + min($cuotaViaticos,$totalViaticosComprobado) + min($cuotaHospedaje,$totalHospedajeComprobado)) - ($totalTransporteComprobado + $totalViaticosComprobado + $totalHospedajeComprobado), 2) }}</td>
                <td></td><td></td><td></td>
                <td style="text-align: right;">{{ number_format((min($cuotaPasaje,$totalTransporteComprobado) + min($cuotaViaticos,$totalViaticosComprobado) + min($cuotaHospedaje,$totalHospedajeComprobado)) - ($totalTransporteComprobado + $totalViaticosComprobado + $totalHospedajeComprobado), 2) }}</td>
            </tr>
            <!-- Row 7: Reintegro Santander -->
            <tr>
                <td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $nb }}"></td><td style="{{ $sep }}"></td>
                <td colspan="6" style="text-align: center; font-weight: bold; border-top: 2px solid black;">REINTEGRO A {{ strtoupper($employee->banco ?? 'BANCO') }} CTA CLABE {{ $employee->clabe ?? '' }}</td>
            </tr>
        </tbody>
    </table>

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
