<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: letter;
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: {{ $settings['layout_font_size'] ?? '8' }}pt;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-top: {{ $settings['layout_header_mt'] ?? '0' }}px;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .title {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }
        .subtitle {
            font-size: 11pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .logo-container {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .logo-left {
            float: left;
            width: 40%;
        }
        .logo-right {
            float: right;
            width: 40%;
            text-align: right;
        }
        .logo-state {
            max-height: 80px;
            width: {{ $settings['layout_logo_state_w'] ?? '150' }}px;
            object-fit: contain;
        }
        .logo-campaign {
            max-height: 60px;
            width: {{ $settings['layout_logo_campaign_w'] ?? '130' }}px;
            object-fit: contain;
        }
        .info-line {
            clear: both;
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            padding-top: 10px;
            margin-bottom: {{ $settings['layout_info_mb'] ?? '10' }}px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: {{ $settings['layout_table_mt'] ?? '10' }}px;
        }
        th {
            border: 2px solid #000;
            padding: 5px;
            font-size: {{ $settings['layout_font_size'] ?? '8' }}pt;
            font-weight: bold;
            background-color: #f0f0f0;
            text-align: center;
        }
        td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: {{ $settings['layout_font_size'] ?? '8' }}pt;
        }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .bg-stripe { background-color: #f2f7ff; }
        .page-break { page-break-after: always; }
        
        .footer-signatures {
            margin-top: 30px;
        }
        .signature-box {
            width: 45%;
            float: left;
            text-align: center;
            border-top: 1px solid #000;
            margin: 30px 2.5% 0 2.5%;
            padding-top: 5px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .footer-image-container {
            position: fixed;
            bottom: {{ $settings['layout_footer_bottom'] ?? '-20' }}px;
            left: -1cm;
            right: -1cm;
            width: calc(100% + 2cm);
            height: {{ $settings['layout_footer_h'] ?? '80' }}px;
            z-index: -1;
            text-align: center;
        }
        .subtotal-row td {
            font-weight: bold;
            background-color: #f5f5f5;
            border: 2px solid #000;
        }
    </style>
</head>
<body>
    @php
        $chunks = $captures->chunk(26);
        $totalPageCount = count($chunks);
        $grandTotalSubtotal = 0;
        $grandTotalCommission = 0;
        $grandTotalTotal = 0;
    @endphp

    @foreach($chunks as $pageIndex => $pageCaptures)
        @php
            $pageSubtotalSub = 0;
            $pageSubtotalComm = 0;
            $pageSubtotalTotal = 0;
        @endphp

        <div class="footer-image-container">
            @if(isset($settings['report_logo_footer']) && $settings['report_logo_footer'])
                <img src="{{ storage_path('app/public/' . $settings['report_logo_footer']) }}" style="width: 100%; height: auto; display: block;">
            @endif
        </div>

        <div class="header">
            <div style="float: right; font-size: 8pt; font-weight: bold;">HOJA {{ $pageIndex + 1 }}/{{ $totalPageCount }}</div>
            <h1 class="title">{{ $settings['report_title'] ?? 'COMISION DE AGUA POTABLE Y ALCANTARILLADO' }}</h1>
            <h2 class="subtitle">{{ $settings['report_subtitle'] ?? 'ORGANISMO OPERADOR : JOSE MARIA MORELOS' }}</h2>
            
            <div class="logo-container">
                <div class="logo-left">
                    @if(isset($settings['report_logo_state']) && $settings['report_logo_state'])
                        <img src="{{ storage_path('app/public/' . $settings['report_logo_state']) }}" class="logo-state">
                    @else
                        <div style="font-size: 8pt; border: 1px dashed #ccc; padding: 10px;">LOGO ESTATAL</div>
                    @endif
                </div>
                <div class="logo-right">
                    @if(isset($settings['report_logo_campaign']) && $settings['report_logo_campaign'])
                        <img src="{{ storage_path('app/public/' . $settings['report_logo_campaign']) }}" class="logo-campaign">
                    @else
                        <div style="font-size: 8pt; border: 1px dashed #ccc; padding: 10px;">LOGO CAMPAÑA</div>
                    @endif
                    <div style="margin-top: 5px; font-size: 8pt; font-weight: bold;">
                        FONDO BOMBEROS ({{ $year ?? date('Y') }}) : {{ $requirement_number ?? 'S/N' }}
                    </div>
                </div>
                <div style="clear: both;"></div>
            </div>

            <div class="info-line">
                JOSE MARIA MORELOS, QUINTANA ROO; A {{ \Carbon\Carbon::parse($assignment_date)->translatedFormat('d') }} DE {{ strtoupper(\Carbon\Carbon::parse($assignment_date)->translatedFormat('F')) }} DEL {{ \Carbon\Carbon::parse($assignment_date)->translatedFormat('Y') }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">COMUNIDADES</th>
                    <th style="width: 32%;">N O M B R E S</th>
                    <th style="width: 15%;">SUBTOTAL</th>
                    <th style="width: 12%;">15%</th>
                    <th style="width: 16%;">T O T A L</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pageCaptures as $c)
                    @php
                        $pageSubtotalSub += $c->subtotal;
                        $pageSubtotalComm += $c->commission;
                        $pageSubtotalTotal += $c->total;
                        
                        $grandTotalSubtotal += $c->subtotal;
                        $grandTotalCommission += $c->commission;
                        $grandTotalTotal += $c->total;
                    @endphp
                    <tr class="{{ $loop->even ? 'bg-stripe' : '' }}">
                        <td style="font-weight: bold; text-transform: uppercase;">{{ $c->community->name }}</td>
                        <td style="text-transform: uppercase;">{{ $c->firefighter->name }}</td>
                        <td class="text-right">{{ number_format($c->subtotal, 2) }}</td>
                        <td class="text-right" style="color: #666;">{{ number_format($c->commission, 2) }}</td>
                        <td class="text-right font-bold">{{ number_format($c->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="subtotal-row">
                    <td colspan="2" class="text-right">SUBTOTAL PÁGINA</td>
                    <td class="text-right">$ {{ number_format($pageSubtotalSub, 2) }}</td>
                    <td class="text-right">$ {{ number_format($pageSubtotalComm, 2) }}</td>
                    <td class="text-right">$ {{ number_format($pageSubtotalTotal, 2) }}</td>
                </tr>
                @if($pageIndex + 1 == $totalPageCount)
                    <tr class="subtotal-row" style="background-color: #e0e0e0;">
                        <td colspan="2" class="text-right font-bold" style="font-size: 10pt;">TOTAL GENERAL</td>
                        <td class="text-right" style="font-size: 10pt;">$ {{ number_format($grandTotalSubtotal, 2) }}</td>
                        <td class="text-right" style="font-size: 10pt;">$ {{ number_format($grandTotalCommission, 2) }}</td>
                        <td class="text-right" style="border: 3px solid #000; font-size: 10pt;">$ {{ number_format($grandTotalTotal, 2) }}</td>
                    </tr>
                @endif
            </tfoot>
        </table>

        @if($pageIndex + 1 == $totalPageCount)
            <div class="footer-signatures">
                <div class="signature-box">
                    {{ $settings['report_signer1_name'] ?? 'Nombre y Firma' }}<br>
                    <span style="font-weight: normal; font-size: 7pt;">{{ $settings['report_signer1_position'] ?? 'Responsable de Captura' }}</span>
                </div>
                <div class="signature-box">
                    {{ $settings['report_signer2_name'] ?? 'Sello de Recibido' }}<br>
                    <span style="font-weight: normal; font-size: 7pt;">{{ $settings['report_signer2_position'] ?? 'Organismo Operador CAPA' }}</span>
                </div>
                <div style="clear: both;"></div>
            </div>
        @endif

        @if($pageIndex + 1 < $totalPageCount)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
