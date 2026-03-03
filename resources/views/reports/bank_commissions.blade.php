<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comisiones Bancarias</title>
    <style>
        @page { margin: 1.5cm 1.5cm 2.5cm 1.5cm; size: letter portrait; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9.5pt; margin: 0; padding: 0; color: #333; line-height: 1.2; }
        
        .header-section { margin-bottom: 25px; position: relative; }
        .logos { margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .logo-capa { height: 60px; }
        .logo-gobiernomx { height: 50px; }
        
        .header-text { text-align: center; margin-top: -10px; }
        .header-main { font-weight: bold; font-size: 11.5pt; color: #000; margin-bottom: 2px; text-transform: uppercase; }
        .header-sub  { font-weight: bold; font-size: 10.5pt; color: #444; margin-bottom: 5px; text-transform: uppercase; }
        .header-info { font-weight: bold; font-size: 10.5pt; color: #000; margin-top: 10px; text-transform: uppercase; }
        
        .period-date { position: absolute; right: 0; top: 90px; font-weight: bold; font-size: 10.5pt; color: #000; }

        table.main-table { width: 100%; border-collapse: collapse; margin-top: 50px; border: 2px solid #000; }
        table.main-table thead th { 
            border: 1.5px solid #000; 
            padding: 8px 4px; 
            font-size: 9.5pt; 
            text-align: center; 
            background-color: #f5f5f5;
            color: #000;
            text-transform: uppercase;
        }
        table.main-table tbody td { 
            border: 1px solid #000; 
            padding: 6px 6px; 
            font-size: 9.5pt; 
            vertical-align: middle;
            color: #000;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .footer-totals { margin-top: 0; border-top: none; }
        .totals-row td { padding: 4px 6px; font-weight: bold; font-size: 10.5pt; color: #000; }
        
        .page-footer {
            position: fixed;
            bottom: 0.5cm;
            right: 0;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>

    <div class="header-section">
        <div class="logos">
            @if(isset($settings['logo_path']) && file_exists($settings['logo_path']))
                <img src="{{ $settings['logo_path'] }}" class="logo-capa">
            @endif
        </div>
        
        <div class="header-text">
            <div class="header-main">COMISIÓN DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO DE QUINTANA ROO</div>
            <div class="header-sub">ORGANISMO OPERADOR: JOSÉ MARÍA MORELOS</div>
            <div class="header-info">COMISIONES BANCARIAS: {{ $bank_info }}</div>
        </div>

        <div class="period-date">
            {{ $period }}
        </div>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 15%;">Fecha valor</th>
                <th style="width: 35%;">Narrativa</th>
                <th style="width: 20%;">Referencia de cliente</th>
                <th style="width: 15%;">IMP.<br>CARGADO</th>
                <th style="width: 15%;">IMP.<br>CARGADO</th>
            </tr>
        </thead>
        <tbody>
            @php
                $subtotalIva = 0; 
                $subtotalSpei = 0; 
                $monthYearLabel = $period ?? '---';
            @endphp
            @foreach($requirement->items as $item)
                @php
                    $isIva = str_contains(str_replace('.', '', strtoupper($item->description)), 'IVA');
                    $valIva = $isIva ? $item->amount : 0;
                    $valSpei = !$isIva ? $item->amount : 0;
                    $subtotalIva += $valIva;
                    $subtotalSpei += $valSpei;
                @endphp
                <tr>
                    <td class="text-center">{{ $item->invoice_date ? $item->invoice_date->format('d/m/Y') : '-' }}</td>
                    <td>{{ strtoupper($item->description) }}</td>
                    <td class="text-center">{{ $item->invoice_folio ?: '-' }}</td>
                    <td class="text-right">{{ $valIva > 0 ? number_format($valIva, 2) : '-' }}</td>
                    <td class="text-right">{{ $valSpei > 0 ? number_format($valSpei, 2) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="3" class="text-right font-bold" style="border: 1px solid #000;">SUBTOTAL</td>
                <td class="text-right font-bold" style="border: 1px solid #000;">{{ number_format($subtotalIva, 2) }}</td>
                <td class="text-right font-bold" style="border: 1px solid #000;">{{ number_format($subtotalSpei, 2) }}</td>
            </tr>
            <tr class="totals-row">
                <td colspan="3" class="text-right font-bold" style="border: none; padding-top: 15px;">TOTAL</td>
                <td colspan="2" class="text-center font-bold" style="border: 1px solid #000; padding-top: 5px; font-size: 11pt;">
                    {{ number_format($subtotalIva + $subtotalSpei, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <script type="text/php">
        if ( isset($pdf) ) {
            $font = $fontMetrics->get_font("Arial", "normal");
            $pdf->page_text(480, $pdf->get_height() - 35, "PAGINA {PAGE_NUM} DE {PAGE_COUNT}", $font, 9, array(0,0,0));
        }
    </script>
</body>
</html>
