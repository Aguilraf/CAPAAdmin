<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibos de Pago</title>
    <style>
        @page { margin: 0.5cm 1.5cm; }
        
        body { font-family: Arial, sans-serif; font-size: 11pt; margin: 0; padding: 0.3cm; }
        
        .page-break { page-break-after: always; }
        .page-break:last-child { page-break-after: avoid; }
        
        .header { text-align: center; margin-bottom: 15px; text-transform: uppercase; }
        .header-logos { width: 100%; margin-bottom: 5px; }
        .header-title { font-weight: bold; font-size: 12pt; margin-bottom: 3px; }
        .header-subtitle { font-weight: bold; font-size: 11pt; }
        
        .place-date { text-align: center; text-transform: uppercase; margin-bottom: 25px; font-weight: bold; }
        
        .amount-box { text-align: right; margin-bottom: 25px; font-weight: bold; font-size: 12pt; }
        
        .body-text { text-align: justify; line-height: 1.6; margin-bottom: 100px; }
        .amount-literal { font-style: italic; }
        
        .recibi-section { text-align: center; margin-bottom: 110px; }
        .recibi-label { font-weight: bold; margin-bottom: 30px; display: block; }
        .recibi-line { border-bottom: 1px solid #000; width: 60%; margin: 0 auto 5px auto; }
        .recibi-name { font-weight: bold; text-transform: uppercase; }
        
        .signatures { width: 100%; margin-top: 10px; }
        .signatures td { 
            width: 33.33%; 
            text-align: center; 
            vertical-align: top; 
            padding: 0 5px;
        }
        .sig-title { font-weight: bold; margin-bottom: 50px; text-transform: uppercase; font-size: 11pt; }
        .sig-line { border-top: 1px solid #000; width: 90%; margin: 0 auto 5px auto; }
        .sig-name { font-weight: bold; font-size: 10pt; text-transform: uppercase; }
        .sig-role { font-size: 8.5pt; text-transform: uppercase; line-height: 1.1; }

        .footer { 
            position: fixed; 
            bottom: {{ $settings['footer_margin_bottom'] ?? -40 }}px; 
            left: -40px; 
            right: -40px; 
            height: 140px; 
            z-index: -1000;
        }
        .footer img { width: 100%; height: auto; }

        .footer-text {
            position: fixed;
            bottom: 0px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8.5pt;
            color: #444;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    @foreach($payments as $payment)
    <div class="page-break">
        <!-- Header Logos -->
        <table class="header-logos">
            <tr>
                <td style="text-align: left; width: 25%;">
                    @if($settings['logo_qroo'] ?? false)
                        <img src="{{ $settings['logo_qroo'] }}" style="height: 55px;">
                    @endif
                </td>
                <td style="width: 50%;"></td>
                <td style="text-align: right; width: 25%;">
                     @if($settings['logo_unidos'] ?? false)
                        <img src="{{ $settings['logo_unidos'] }}" style="height: 45px;">
                    @endif
                </td>
            </tr>
        </table>

        <div class="header">
            <div class="header-title">COMISIÓN DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO</div>
            <div class="header-title">DE QUINTANA ROO</div>
            <div class="header-subtitle">Organismo Operador: {{ mb_strtoupper($settings['footer_organismo'] ?? $payment->organismo->nombre ?? 'DIRECCION GENERAL') }}</div>
        </div>

        <div class="place-date">
            {{ mb_strtoupper($payment->organismo->ubicacion ?? 'JOSE MARIA MORELOS, QUINTANA ROO') }}; A {{ mb_strtoupper($payment->fecha_formateada) }}
        </div>

        <div class="amount-box">
            BUENO POR $ {{ number_format($payment->amount, 2) }}
        </div>

        <div class="body-text">
            RECIBI DE LA COMISION DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO DE QUINTANA ROO “ORGANISMO OPERADOR, {{ mb_strtoupper($settings['footer_organismo'] ?? $payment->organismo->nombre ?? 'DIRECCION GENERAL') }}” LA CANTIDAD DE <strong>$ {{ number_format($payment->amount, 2) }}</strong> (SON: <span class="amount-literal">{{ $payment->amount_letters }}</span>), 
            
            @if(!$payment->requirement_id)
                POR CONCEPTO:
            @endif
            
            {{ mb_strtoupper($payment->concept) }}, PAGADO SEGÚN {{ mb_strtoupper($payment->payment_type) }} 
            @if($payment->payment_type === 'cheque')
                NO.
            @else
                CON CLAVE DE RASTREO NO.
            @endif
            <strong>{{ $payment->reference }}</strong>
        </div>

        <div class="recibi-section">
            <span class="recibi-label">RECIBI</span>
            <div class="recibi-line"></div>
            <div class="recibi-name">C. {{ $payment->beneficiary }}</div>
        </div>

        <!-- Signatures -->
        <table class="signatures">
            <tr>
                <td>
                    <div class="sig-title">ELABORÓ</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $payment->elaboratedBy->nombre ?? 'N/A' }}</div>
                    <div class="sig-role">{{ $payment->elaboratedBy->puesto ?? 'DEPTO. REC. FINANCIEROS' }}</div>
                </td>
                <td>
                    <div class="sig-title">FORMULÓ</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $payment->formulatedBy->nombre ?? 'N/A' }}</div>
                    <div class="sig-role">{{ $payment->formulatedBy->puesto ?? 'SUBGERENTE ADMINISTRATIVO' }}</div>
                </td>
                <td>
                    <div class="sig-title">AUTORIZA</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $payment->authorizedBy->nombre ?? 'N/A' }}</div>
                    <div class="sig-role">{{ $payment->authorizedBy->puesto ?? 'GERENTE DEL ORGANISMO OPERADOR' }}</div>
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
            <div>Organismo Operador: {{ $settings['footer_organismo'] ?? 'DIRECCION GENERAL' }}</div>
            <div>{{ $settings['footer_direccion'] ?? '' }}</div>
            <div>Tel.: {{ $settings['footer_telefono'] ?? '' }}</div>
            <div>{{ $settings['footer_email'] ?? '' }}</div>
        </div>
    </div>
    @endforeach
</body>
</html>
