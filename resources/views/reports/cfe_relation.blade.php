<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Relación de Recibos CFE</title>
    <style>
        @page { margin: 50px 50px 120px 50px; }
        body { font-family: Arial, sans-serif; font-size: 9pt; margin: 0; padding: 0; }
        
        /* Header Logos */
        .header-logos { width: 100%; margin-bottom: 10px; }
        .header-logos td { vertical-align: middle; }
        
        /* Header Text */
        .header-text { text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 15px; line-height: 1.4; }
        .header-text .title { font-size: 9pt; margin-top: 5px; }
        .header-text .subtitle { font-size: 3.5pt; margin-top: 3px; line-height: 1.0; }
        
        /* Table */
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 6pt; }
        .table th, .table td { border: 1px solid black; padding: 3px; text-align: center; }
        .table th { background-color: #f0f0f0; font-weight: bold; }
        .row-even { background-color: #f9f9f9; }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }
        
        /* Signatures */
        .signatures { width: 100%; margin-top: 80px; }
        .signatures td { text-align: center; border: none; vertical-align: top; }
        .sig-title { font-weight: bold; margin-bottom: 50px; }
        .sig-name { font-weight: bold; font-size: 9pt; border-top: 1px solid #000; padding-top: 5px; text-transform: uppercase; }
        .sig-role { font-size: 8pt; text-transform: uppercase; }
        
        /* Footer */
        .footer { 
            position: fixed; 
            bottom: {{ $settings['footer_margin_bottom'] ?? -30 }}px; 
            left: 0; 
            right: 0; 
            height: 100px; 
            text-align: center;
            z-index: -1000;
        }
        .footer img { width: 100%; height: auto; }

        .footer-text {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #555;
            line-height: 1.2;
        }

        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    
    @php $globalRowIndex = 0; @endphp
    
    @foreach($pages as $page)
    <!-- Page {{ $page['pageNumber'] }} -->
    
    <!-- Header Logos -->
    <table class="header-logos">
        <tr>
            <td style="text-align: left; width: 20%;">
                @if($settings['logo_qroo'] ?? false)
                    <img src="{{ $settings['logo_qroo'] }}" style="height: 60px;">
                @endif
            </td>
            <td style="text-align: center; width: 60%;">
                <div class="header-text">
                    COMISIÓN DE AGUA POTABLE Y ALCANTARILLADO<br>
                    DEL ESTADO DE QUINTANA ROO<br>
                    ORGANISMO OPERADOR: JOSÉ MARÍA MORELOS
                </div>
            </td>
            <td style="text-align: right; width: 20%;">
                @if($settings['logo_unidos'] ?? false)
                    <img src="{{ $settings['logo_unidos'] }}" style="height: 60px;">
                @endif
            </td>
        </tr>
    </table>

    <div style="text-align: right; font-size: 8pt; margin-bottom: 10px;">
        HOJA {{ $page['pageNumber'] }}/{{ $page['totalPages'] }}
    </div>

    <div style="text-align: center; font-weight: bold; line-height: 1.0; margin-bottom: 15px;">
        <div style="font-size: 11pt; margin: 0; padding: 0;">RELACIÓN DE RECIBOS DE CFE QUE AMPARA EL REQ. ECO. NUM: {{ $requirement->formatted_number }}</div>
        <div style="font-size: 8pt; margin: 0; padding: 0;">FACTURACION DE {{ \Carbon\Carbon::parse($requirement->start_date)->format('d/m/Y') }} AL {{ \Carbon\Carbon::parse($requirement->end_date)->format('d/m/Y') }} CON VENCIMIENTO {{ $requirement->description }}</div>
        <div style="font-size: 11pt; margin: 0; padding: 0;">JOSÉ MARÍA MORELOS, QUINTANA ROO A {{ \Carbon\Carbon::now()->format('d') }} DE {{ strtoupper(\Carbon\Carbon::now()->translatedFormat('F')) }} DEL {{ \Carbon\Carbon::now()->format('Y') }}</div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>R.P.U</th>
                <th>POBLADO</th>
                <th>DIRECCIÓN</th>
                <th>FOLIO FISCAL</th>
                <th>SUBTOTAL</th>
                <th>IVA</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($page['receipts'] as $receipt)
                @php
                    // Extract poblado (before comma) and direccion (after comma)
                    $parts = explode(',', $receipt->description, 2);
                    $poblado = trim($parts[0] ?? '');
                    $direccion = trim($parts[1] ?? '');
                    $rowClass = ($globalRowIndex % 2 == 0) ? 'row-even' : '';
                    $globalRowIndex++;
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>{{ $receipt->rpu }}</td>
                    <td class="text-left">{{ $poblado }}</td>
                    <td class="text-left">{{ $direccion }}</td>
                    <td>{{ $receipt->uuid }}</td>
                    <td class="text-right">${{ number_format($receipt->subtotal, 2) }}</td>
                    <td class="text-right">${{ number_format($receipt->iva, 2) }}</td>
                    <td class="text-right">${{ number_format($receipt->total, 2) }}</td>
                </tr>
            @endforeach
            <!-- Filas vacías para rellenar hoja (26 total rows per page) -->
            @for($i = 0; $i < (26 - count($page['receipts'])); $i++)
                @php
                    $rowClass = ($globalRowIndex % 2 == 0) ? 'row-even' : '';
                    $globalRowIndex++;
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr class="font-bold row-even" style="font-size: 7pt; font-weight: bold;">
                @if($page['isLastPage'])
                    <td colspan="4" class="text-right">TOTAL</td>
                    <td class="text-right">${{ number_format($grandSubtotal, 2) }}</td>
                    <td class="text-right">${{ number_format($grandIva, 2) }}</td>
                    <td class="text-right">${{ number_format($grandTotal, 2) }}</td>
                @else
                    <td colspan="4" class="text-right">SUBTOTAL</td>
                    <td class="text-right">${{ number_format($page['pageSubtotal'], 2) }}</td>
                    <td class="text-right">${{ number_format($page['pageIva'], 2) }}</td>
                    <td class="text-right">${{ number_format($page['pageTotal'], 2) }}</td>
                @endif
            </tr>
        </tfoot>
    </table>

    @if($page['isLastPage'])
    <!-- Signatures (only on last page) -->
    <table class="signatures">
        <tr>
            <td>
                <div class="sig-title">ELABORÓ</div>
            </td>
            <td>
                <div class="sig-title">Vo. Bo.</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="sig-name">{{ $requirement->elaborator->nombre ?? 'N/A' }}</div>
                <div class="sig-role">{{ $requirement->elaborator->puesto ?? '' }}</div>
            </td>
            <td>
                <div class="sig-name">{{ $requirement->manager->nombre ?? 'N/A' }}</div>
                <div class="sig-role">{{ $requirement->manager->puesto ?? '' }}</div>
            </td>
        </tr>
    </table>
    @endif

    @if(!$page['isLastPage'])
    <!-- Page break (not on last page) -->
    <div class="page-break"></div>
    @endif

    @endforeach

    <!-- Footer Image -->
    @if($settings['footer_imagen'] ?? false)
    <div class="footer">
        <img src="{{ $settings['footer_imagen'] }}">
    </div>
    @endif

    <!-- Footer Text -->
    <div class="footer-text">
        <div>Comisión de Agua Potable y Alcantarillado</div>
        <div>{{ $settings['footer_organismo'] ?? 'Organismo Operador: José María Morelos' }}</div>
        <div>{{ $settings['footer_direccion'] ?? '' }}</div>
        <div>Tel.: {{ $settings['footer_telefono'] ?? '' }}</div>
        <div>{{ $settings['footer_email'] ?? '' }}</div>
    </div>
</body>
</html>
