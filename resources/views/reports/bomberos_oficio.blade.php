<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Oficio Bomberos {{ $requirement->requirement_number }}</title>
    <style>
        @page { margin: 1cm 2cm; }
        body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5; }
        .header-logos { width: 100%; margin-bottom: 20px; }
        .header-logos td { vertical-align: middle; }
        .logo-qroo { height: 70px; }
        .logo-unidos { height: 60px; }
        
        .oficio-info { text-align: right; margin-bottom: 30px; line-height: 1.2; font-weight: bold; }
        .oficio-info div { margin-bottom: 5px; }

        .place-date { text-align: right; margin-bottom: 40px; font-weight: bold; }
        
        .leyenda-anio { text-align: center; font-size: 9pt; margin-bottom: 20px; font-style: italic; }

        .addressee { font-weight: bold; margin-bottom: 40px; }
        .addressee p { margin: 0; line-height: 1.2; }
        
        .subject { font-weight: bold; margin-bottom: 20px; }
        
        .content { text-align: justify; margin-bottom: 40px; }
        .content p { text-indent: 40px; margin-bottom: 15px; }
        
        .closing { margin-bottom: 60px; text-align: center; }
        
        .signature-box { text-align: center; margin-top: 80px; width: 60%; margin-left: auto; margin-right: auto; }
        .signature-line { border-top: 1px solid #000; width: 100%; margin-bottom: 5px; }
        .signature-name { font-weight: bold; font-size: 11pt; text-transform: uppercase; }
        .signature-role { font-size: 10pt; text-transform: uppercase; font-weight: bold; }
        
        .footer { 
            position: fixed; 
            bottom: -30px; 
            left: -2cm; 
            right: -2cm; 
            height: 100px; 
            text-align: center;
            z-index: -1000;
        }
        .footer img { width: 100%; height: auto; }
        
        .ccp { font-size: 8pt; margin-top: 20px; line-height: 1.2; }
    </style>
</head>
<body>
    <table class="header-logos">
        <tr>
            <td style="text-align: left;">
                @if($settings['logo_qroo'] ?? false)
                    <img src="{{ $settings['logo_qroo'] }}" class="logo-qroo">
                @endif
            </td>
            <td style="text-align: right;">
                @if($settings['logo_unidos'] ?? false)
                    <img src="{{ $settings['logo_unidos'] }}" class="logo-unidos">
                @endif
            </td>
        </tr>
    </table>

    <div class="oficio-info">
        <div>No. de oficio: CAPA/JMM/G/{{ $requirement->oficio_number }}/{{ $requirement->year }}-FB-{{ str_pad($requirement->requirement_number, 2, '0', STR_PAD_LEFT) }}</div>
        <div>Asunto: REPOSICION {{ str_pad($requirement->requirement_number, 2, '0', STR_PAD_LEFT) }}/{{ $requirement->year }}</div>
    </div>

    <div class="place-date">
        JOSÉ MARÍA MORELOS, QUINTANA ROO; {{ $fecha_formateada }}
    </div>

    <div class="leyenda-anio">
        "{{ $settings['leyenda_anio'] }}"
    </div>

    <div class="addressee">
        <p>{{ $destinatario->nombre ?? 'LIC. NORMA SANCHEZ CASTILLO' }}</p>
        <p>{{ $destinatario->puesto ?? 'COORDINADOR COMERCIAL DE LA CAPA' }}</p>
        <p class="presente">Presente</p>
    </div>

    <div class="content">
        <p>Por medio de la presente me permito enviar a usted la <strong>REPOSICION</strong> del <strong>Fondo de Bomberos</strong> NUM. <strong>{{ str_pad($requirement->requirement_number, 2, '0', STR_PAD_LEFT) }}/{{ $requirement->year }}</strong> del pago de 15% a Operadores Bomberos por cobranza de consumo de Agua Potable correspondiente a la facturación del mes de <strong>{{ strtoupper($requirement->month_billed ?? '') }}-{{ $requirement->year_billed ?? '' }}</strong> recaudado en <strong>{{ strtoupper($requirement->month_charged ?? '') }}-{{ $requirement->year_charged ?? '' }}</strong> por un monto de <strong>${{ number_format($requirement->total, 2) }}</strong> (Son: {{ $importe_letras }}).</p>
        
        <p>Sin otro asunto en particular, me es grato hacer propicia la ocasión para enviarle un cordial saludo.</p>
    </div>

    <div class="closing">
        <p>ATENTAMENTE</p>
        <div class="signature-box">
            <div class="signature-name">{{ $requirement->manager->nombre ?? 'C. LUIS DANIEL HEREDIA DUARTE' }}</div>
            <div class="signature-role">{{ $requirement->manager->puesto ?? 'GERENTE DEL ORGANISMO OPERADOR' }}</div>
        </div>
    </div>

    <div class="ccp">
        C.C.P.- C. {{ $requirement->items->first()->employee->nombre ?? '' }}, Subgerente Comercial. Organismo Operador<br>
        C.C.P.- ARCHIVO
    </div>

    @if($settings['footer_imagen'] ?? false)
    <div class="footer">
        <img src="{{ $settings['footer_imagen'] }}">
    </div>
    @endif
</body>
</html>
