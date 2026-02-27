<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cédula Fondo Revolvente {{ $requirement->revolving_fund_number }}</title>
    <style>
        @page { margin: 0.8cm 1cm; size: letter landscape; }
        body { font-family: Arial, sans-serif; font-size: 7pt; margin: 0; }

        .page-header { width: 100%; margin-bottom: 6px; }

        .report-title { font-weight: bold; font-size: 8pt; text-align: center; margin-bottom: 3px; }
        .report-sub   { font-size: 7.5pt; text-align: center; margin-bottom: 2px; }

        table.main-table { width: 100%; border-collapse: collapse; }
        table.main-table th {
            background: #ddd;
            border: 1px solid #000;
            padding: 2px 3px;
            font-size: 6.5pt;
            text-align: center;
            vertical-align: middle;
        }
        table.main-table td {
            border: 1px solid #888;
            padding: 2px 3px;
            font-size: 6.5pt;
            vertical-align: middle;
        }
        .subtotal-row td { font-weight: bold; border-top: 2px solid #000; background: #f5f5f5; }
        .total-row td { font-weight: bold; border-top: 2px solid #000; background: #e0e0e0; font-size: 7pt; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .signatures-area { margin-top: 15px; width: 100%; }
        .sig-cell { text-align: center; vertical-align: top; padding: 0 10px; width: 20%; font-size: 7pt; }
        .sig-line { border-top: 1px solid #000; width: 90%; margin: 0 auto 3px; }
        .sig-name { font-weight: bold; text-transform: uppercase; }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="page-header">
        <tr>
            <td style="width: 13%; text-align: left; vertical-align: middle;">
                @if($settings['logo_qroo'] ?? false)
                    <img src="{{ $settings['logo_qroo'] }}" style="height: 50px;">
                @endif
            </td>
            <td style="width: 60%; text-align: center; vertical-align: middle;">
                <div class="report-title">Comisión de Agua Potable y Alcantarillado del Estado de Quintana Roo</div>
                <div class="report-sub">Organismo Operador José María Morelos</div>
                <div style="font-weight: bold; font-size: 8.5pt; text-align: center; margin-top: 3px;">
                    Cédula de Control de Erogaciones Y/O Cancelación de Fondo Revolvente
                </div>
            </td>
            <td style="width: 13%; text-align: right; vertical-align: middle;">
                @if($settings['logo_unidos'] ?? false)
                    <img src="{{ $settings['logo_unidos'] }}" style="height: 40px;">
                @endif
            </td>
        </tr>
    </table>

    <!-- Meta info row -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px; font-size: 7pt;">
        <tr>
            <td style="border: 1px solid #000; padding: 2px 5px; width: 20%;">
                <strong>PERIODO:</strong><br>
                AL {{ $fecha_lugar }}
            </td>
            <td style="border: 1px solid #000; padding: 2px 5px; width: 25%;">
                <strong>ORGANISMO OPERADOR:</strong> JOSE MARIA MORELOS<br>
                <strong>RESPONSABLE DEL FONDO:</strong> {{ strtoupper($requirement->manager->nombre ?? '') }}
            </td>
            <td style="border: 1px solid #000; padding: 2px 5px; width: 30%; text-align: center;">
                <strong>REPOSICIÓN</strong><br>
                N° de Solicitud: {{ $requirement->oficio_number }}/{{ $requirement->year }}/FRV-{{ str_pad($requirement->revolving_fund_number, 3, '0', STR_PAD_LEFT) }}
            </td>
            <td style="border: 1px solid #000; padding: 2px 5px; width: 25%;">
                <strong>ANULACIÓN:</strong><br>
                FIRMA Y SELLO DEL BANCO
            </td>
        </tr>
    </table>

    <!-- Main data table -->
    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 7%;">PROVEEDOR</th>
                <th rowspan="2" style="width: 5%;">N° DE FACTURA</th>
                <th rowspan="2" style="width: 6%;">FECHA DE FACTURA</th>
                <th rowspan="2" style="width: 18%;">CONCEPTO DE ADQUISICIÓN</th>
                <th rowspan="2" style="width: 12%;">OBJETO DEL GASTO</th>
                <th colspan="7" style="width: 40%;">IMPORTE DE GASTOS EFECTUADOS EN LA REPOSICIÓN POR FACTURA</th>
                <th rowspan="2" style="width: 6%;">Total</th>
                <th rowspan="2" style="width: 6%;">SELLO</th>
            </tr>
            <tr>
                <th>Subtotal</th>
                <th>I.V.A</th>
                <th>DESCUENTO</th>
                <th>I.E.P.S</th>
                <th>RET. ISR</th>
                <th>RET. IVA</th>
                <th>IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Group by invoice folio
                $invoiceGroups = $items->groupBy('invoice_folio');
                $grandSubtotal = 0; $grandIva = 0; $grandDiscount = 0;
                $grandIeps = 0; $grandRetIsr = 0; $grandRetIva = 0; $grandTotal = 0;
            @endphp

            @foreach($invoiceGroups as $folio => $invoiceItems)
                @php
                    $firstItem = $invoiceItems->first();
                    $rowspan  = $invoiceItems->count();
                    $isFirst  = true;
                    // Totals for this invoice group
                    $invSubtotal = $invoiceItems->sum('invoice_subtotal');
                    $invIva      = $invoiceItems->sum('invoice_iva');
                    $invDiscount = $invoiceItems->sum('invoice_discount');
                    $invIeps     = $invoiceItems->sum('invoice_ieps');
                    $invRetIsr   = $invoiceItems->sum('invoice_retention_isr');
                    $invRetIva   = $invoiceItems->sum('invoice_retention_iva');
                    $invTotal    = $invoiceItems->sum('amount');

                    $grandSubtotal += $invSubtotal; $grandIva += $invIva;
                    $grandDiscount += $invDiscount; $grandIeps += $invIeps;
                    $grandRetIsr   += $invRetIsr;   $grandRetIva += $invRetIva;
                    $grandTotal    += $invTotal;
                @endphp

                @foreach($invoiceItems as $item)
                <tr>
                    @if($isFirst)
                    <td rowspan="{{ $rowspan }}" style="font-weight: bold; font-size: 6pt;">
                        {{ strtoupper($item->provider_name ?? '') }}
                    </td>
                    <td rowspan="{{ $rowspan }}" class="text-center">
                        {{ $folio }}<br>
                        <span style="font-size: 5.5pt;">{{ substr($item->uuid ?? '', 0, 8) }}...</span>
                        <br><span style="font-size: 5.5pt;">Fecha: {{ $item->invoice_date }}</span>
                    </td>
                    <td rowspan="{{ $rowspan }}" class="text-center">
                        {{ $item->invoice_date }}
                    </td>
                    @php $isFirst = false; @endphp
                    @endif

                    <td style="font-size: 6pt;">{{ strtoupper($item->description ?? '') }}</td>
                    <td style="font-size: 6pt;">
                        {{ $item->partida->codigo ?? '' }} {{ strtoupper($item->partida->nombre ?? '') }}
                    </td>

                    @if($loop->first)
                    {{-- Show invoice-level financial data on first row of group --}}
                    <td class="text-right" rowspan="{{ $rowspan }}">{{ $invSubtotal > 0 ? number_format($invSubtotal, 2) : '-' }}</td>
                    <td class="text-right" rowspan="{{ $rowspan }}">{{ $invIva > 0 ? number_format($invIva, 2) : '-' }}</td>
                    <td class="text-right" rowspan="{{ $rowspan }}">{{ $invDiscount > 0 ? number_format($invDiscount, 2) : '-' }}</td>
                    <td class="text-right" rowspan="{{ $rowspan }}">{{ $invIeps > 0 ? number_format($invIeps, 2) : '-' }}</td>
                    <td class="text-right" rowspan="{{ $rowspan }}">{{ $invRetIsr > 0 ? number_format($invRetIsr, 2) : '-' }}</td>
                    <td class="text-right" rowspan="{{ $rowspan }}">{{ $invRetIva > 0 ? number_format($invRetIva, 2) : '-' }}</td>
                    <td class="text-right" rowspan="{{ $rowspan }}">{{ number_format($item->amount, 2) }}</td>
                    <td class="text-right font-bold" rowspan="{{ $rowspan }}" style="font-weight: bold;">{{ number_format($invTotal, 2) }}</td>
                    <td rowspan="{{ $rowspan }}"></td>
                    @endif
                </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTALES</td>
                <td class="text-right">{{ number_format($grandSubtotal, 2) }}</td>
                <td class="text-right">{{ number_format($grandIva, 2) }}</td>
                <td class="text-right">{{ $grandDiscount > 0 ? number_format($grandDiscount, 2) : '0.00' }}</td>
                <td class="text-right">{{ $grandIeps > 0 ? number_format($grandIeps, 2) : '0.00' }}</td>
                <td class="text-right">{{ number_format($grandRetIsr, 2) }}</td>
                <td class="text-right">{{ $grandRetIva > 0 ? number_format($grandRetIva, 2) : '0.00' }}</td>
                <td class="text-right">{{ number_format($grandTotal, 2) }}</td>
                <td class="text-right" style="border: 2px solid #000;">{{ number_format($grandTotal, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Signatures -->
    <table class="signatures-area">
        <tr>
            <td class="sig-cell">
                <p style="font-weight: bold; margin-bottom: 30px; text-align:center;">ELABORÓ</p>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $requirement->elaborator->nombre ?? '' }}</div>
                <div>{{ strtoupper($requirement->elaborator->puesto ?? 'JEFE ADMINISTRATIVO') }}</div>
            </td>
            <td class="sig-cell">
                <p style="font-weight: bold; margin-bottom: 30px; text-align:center;">REVISÓ</p>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $requirement->coordinator->nombre ?? '' }}</div>
                <div>{{ strtoupper($requirement->coordinator->puesto ?? 'COORD. ADMINISTRATIVO FINANCIERO Y DE ARCHIVO') }}</div>
            </td>
            <td class="sig-cell">
                <p style="font-weight: bold; margin-bottom: 30px; text-align:center;">AUTORIZÓ</p>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $requirement->manager->nombre ?? '' }}</div>
                <div>{{ strtoupper($requirement->manager->puesto ?? 'GERENTE DEL ORGANISMO OPER. JMM') }}</div>
            </td>
            <td class="sig-cell" style="width: 30%; text-align: center;">
                <p style="font-weight: bold; margin-bottom: 15px; text-align:center;">APROBÓ</p>
                <div class="sig-line"></div>
                <div class="sig-name" style="font-size: 6.5pt;">
                    COORDINACIÓN ADMINISTRATIVA, FINANCIERA Y DE ARCHIVO
                </div>
                <div style="font-size: 6pt;">DE LA COMISIÓN DE AGUA POTABLE Y ALCANTARILLADO</div>
            </td>
        </tr>
    </table>

    @if($settings['footer_imagen'] ?? false)
    <div style="position: fixed; bottom: -0.8cm; left: -1cm; right: -1cm; height: 70px; z-index: -1;">
        <img src="{{ $settings['footer_imagen'] }}" style="width: 100%; height: auto;">
    </div>
    @endif
</body>
</html>
