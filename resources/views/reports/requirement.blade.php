<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Requerimiento {{ $requirement->formatted_number }}</title>
    <style>
        @page { margin: 0.5cm 1cm; }
        
        body { font-family: Arial, sans-serif; font-size: 10pt; margin: 0; padding: 0.5cm; }
        
        .header { text-align: center; margin-bottom: 20px; }
        .header-logos { width: 100%; margin-bottom: 10px; }
        .header-title { font-weight: bold; font-size: 11pt; }
        .header-subtitle { font-weight: bold; font-size: 10pt; }
        .req-info { text-align: right; margin: 10px 0; font-weight: bold; }
        .year-legend { text-align: center; font-style: italic; font-size: 9pt; margin-bottom: 10px; }
        .place-date { text-align: right; text-transform: uppercase; margin-bottom: 20px; }
        
        .addressee { font-weight: bold; margin-bottom: 20px; text-transform: uppercase; }
        
        .body-text { text-align: justify; line-height: 1.5; margin-bottom: 20px; }
        .amount-text { font-style: italic; }
        
        .breakdown-table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 20px; }
        .breakdown-table td { padding: 5px; vertical-align: top; }
        
        .due-date { text-align: center; font-weight: bold; margin: 20px 0; text-transform: uppercase; }
        
        .signatures { width: 100%; margin-top: 100px; }
        .signatures td { 
            width: 33%; 
            text-align: center; 
            vertical-align: top; 
            padding: 0 10px;
        }
        .sig-title { font-weight: bold; margin-bottom: 50px; }
        .sig-name { font-weight: bold; font-size: 9pt; border-top: 1px solid #000; padding-top: 5px; text-transform: uppercase;}
        .sig-role { font-size: 8pt; text-transform: uppercase;}

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
            bottom: -20px; /* Lowered by 30px (3 lines) */
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #555;
            line-height: 1.2;
        }
    </style>
</head>
<body>
    
    <!-- Header Logos -->
    <table class="header-logos">
        <tr>
            <td style="text-align: left; width: 20%;">
                @if($settings['logo_qroo'] ?? false)
                    <img src="{{ $settings['logo_qroo'] }}" style="height: 60px;">
                @endif
            </td>
            <td style="width: 60%;"></td>
            <td style="text-align: right; width: 20%;">
                 @if($settings['logo_unidos'] ?? false)
                    <img src="{{ $settings['logo_unidos'] }}" style="height: 50px;">
                @endif
            </td>
        </tr>
    </table>

    <div class="header">
        <div class="header-title">COMISIÓN DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO DE QUINTANA ROO</div>
        <div class="header-subtitle">ORGANISMO OPERADOR: JOSÉ MARÍA MORELOS</div>
    </div>

    <div class="req-info">
        REQUERIMIENTO: {{ $requirement->formatted_number }}
    </div>

    <div class="year-legend">
        "{{ $settings['leyenda_anio'] ?? '2026, Año del 40 Aniversario de la Creación del Himno del Estado Libre y Soberano de Quintana Roo' }}"
    </div>

    <div class="place-date">
        CD. JOSÉ MARÍA MORELOS, QUINTANA ROO; {{ $fecha_formateada }}
    </div>

    <div class="addressee">
        C. {{ $data['destinatario_nombre'] }}<br>
        {{ $data['destinatario_cargo'] }}<br>
        PRESENTE
    </div>
    <br><br>

    <div class="body-text">
        POR MEDIO DEL PRESENTE ME PERMITO SOLICITAR A USTED, ME SEAN ENVIADOS LOS RECURSOS PARA CUBRIR GASTOS DEL ORGANISMO OPERADOR JOSÉ MARÍA MORELOS POR UN MONTO DE <strong>${{ number_format($requirement->total, 2) }}</strong> (SON: <span class="amount-text">{{ $importe_letras }}</span>) POR LOS SIGUIENTES CONCEPTOS: {{ $requirement->type === 'cfe' ? 'PAGO POR EL SERVICIO DE ENERGIA ELECTRICA QUE COMPRENDE EL PERIODO DEL ' . ($requirement->start_date ? $requirement->start_date->format('d/m/y') : '') . ' AL ' . ($requirement->end_date ? $requirement->end_date->format('d/m/y') : '') . ', DIFERENTES COMUNIDADES DEL MUNICIPIO DE JOSE MARIA MORELOS.' : $requirement->description }}
    </div>
    <br><br>

    <!-- Financial Breakdown -->
    <table class="breakdown-table">
        <!-- Define Column Widths for alignment -->
        <colgroup>
            <col style="width: 15%;">
            <col style="width: 8%;">
            <col style="width: 37%;">
            <col style="width: 20%;">
            <col style="width: 20%;">
        </colgroup>

        <!-- Loop items -->
        @foreach($requirement->items as $item)
        <tr>
            <td style="font-weight: bold; text-align: left;">CAPITULO</td>
            <td style="font-weight: bold; text-align: left;">{{ $item->capitulo->codigo ?? '3000' }}</td>
            <td style="font-weight: bold; text-align: left;">{{ $item->capitulo->nombre ?? 'SERVICIOS GENERALES' }}</td>
            <td></td>
            <td class="col-amount font-bold" style="font-weight: bold; text-align: right;">${{ number_format($item->amount, 2) }}</td>
        </tr>
        <tr>
            <!-- Partida Code -->
            <td style="font-weight: bold; text-align: left; padding-left: 20px;">{{ number_format((int)($item->partida->codigo ?? '31101')) }}</td> 
            <!-- Partida Name: Spanning 2 columns to prevent wrapping -->
            <td colspan="2" style="font-weight: bold; text-align: left;">{{ $item->partida->nombre ?? 'ENERGIA ELECTRICA' }}</td>
            <!-- Partida Amount (Middle Column - Yellow Area) -->
            <td class="col-amount font-bold" style="font-weight: bold; text-align: right;">${{ number_format($item->amount, 2) }}</td>
            <td></td> 
        </tr>
        @endforeach
        
        <tr>
            <td style="font-weight: bold; text-align: left; padding-left: 20px;">IVA</td>
            <td></td>
            <td></td>
            <td></td>
            <td class="col-amount font-bold" style="text-align: right;">${{ number_format($requirement->iva, 2) }}</td>
        </tr>
        
        <tr>
            <td></td>
            <td colspan="2" style="font-weight: bold; text-align: left;">
                <span style="display: inline-block; width: 100%; text-align: center;">
                    ACREDITABLE <span style="margin-left: 20px;">{{ number_format($requirement->iva, 2) }}</span>
                </span>
            </td>
            <td></td>
            <td></td>
        </tr>
        
        <tr><td colspan="5" style="height: 10px;"></td></tr>
        
        <tr>
            <td></td>
            <td></td>
            <!-- Total Label -->
            <td style="font-weight: bold; text-align: right;">TOTAL</td>
            <td></td>
            <td class="col-amount font-bold" style="border-top: 2px solid #000; text-align: right;">${{ number_format($requirement->total, 2) }}</td>
        </tr>
    </table>

    @if($requirement->type === 'cfe' && $requirement->description)
        <br>
        <div class="due-date">
            {{ $requirement->description }}
        </div>
    @endif

    <!-- Signatures -->
    <table class="signatures">
        <tr>
            <td>
                <div class="sig-title">ELABORÓ</div>
                <div class="sig-name">{{ $requirement->elaborator->nombre ?? 'N/A' }}</div>
                <div class="sig-role">{{ $requirement->elaborator->puesto ?? '' }}</div>
            </td>
            <td>
                <div class="sig-title">Vo. Bo.</div>
                <div class="sig-name">{{ $requirement->manager->nombre ?? 'N/A' }}</div>
                <div class="sig-role">{{ $requirement->manager->puesto ?? '' }}</div>
            </td>
            <td>
                <div class="sig-title">AUTORIZÓ</div>
                <div class="sig-name">{{ $data['destinatario_nombre'] }}</div>
                <div class="sig-role">{{ $data['destinatario_cargo'] }}</div>
            </td>
        </tr>
    </table>
    
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
