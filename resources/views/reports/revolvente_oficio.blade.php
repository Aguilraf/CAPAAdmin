<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Oficio Fondo Revolvente {{ $requirement->revolving_fund_number }}</title>
    <style>
        @page { margin: 1.5cm 2cm 2.5cm; }
        body { font-family: Arial, sans-serif; font-size: 13pt; line-height: 1.5; margin: 0; }

        .header-logos { width: 100%; margin-bottom: 10px; }

        .oficio-info { text-align: center; margin-bottom: 15px; font-size: 10pt; font-weight: bold; line-height: 1.4; }

        .asunto-block { text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 10px; }

        .leyenda-anio { text-align: center; font-size: 9pt; margin-bottom: 20px; font-style: italic; }

        .place-date { text-align: right; margin-bottom: 20px; font-weight: bold; font-size: 11pt; }

        .addressee { font-weight: bold; margin-bottom: 30px; font-size: 12pt; text-transform: uppercase; }
        .addressee p { margin: 2px 0; }

        .content { text-align: justify; margin-bottom: 30px; font-size: 12pt; }
        .content p { text-indent: 50px; margin-bottom: 15px; text-align: justify; }

        .closing { text-align: center; margin-top: 10px; font-size: 11pt; }

        .signature-area { margin-top: 60px; text-align: center; }
        .signature-name { font-weight: bold; font-size: 10pt; text-transform: uppercase; }
        .signature-role { font-size: 10pt; text-transform: uppercase; font-weight: bold; }

        .logo-right-seal { position: absolute; right: 1cm; top: 0; width: 100px; }

        .ccp { font-size: 10pt; margin-top: 30px; line-height: 1.4; }

        .footer {
            position: fixed;
            bottom: -1.5cm;
            left: -2cm;
            right: -2cm;
            height: 90px;
            text-align: center;
            z-index: -1000;
        }
        .footer img { width: 100%; height: auto; }
    </style>
</head>
<body>

    <!-- Logos -->
    <table class="header-logos">
        <tr>
            <td style="text-align: left; width: 20%; vertical-align: middle;">
                @if($settings['logo_qroo'] ?? false)
                    <img src="{{ $settings['logo_qroo'] }}" style="height: 70px;">
                @endif
            </td>
            <td style="width: 60%;"></td>
            <td style="text-align: right; width: 20%; vertical-align: middle;">
                @if($settings['logo_unidos'] ?? false)
                    <img src="{{ $settings['logo_unidos'] }}" style="height: 60px;">
                @endif
            </td>
        </tr>
    </table>

    <br>

    <!-- Oficio number / Asunto -->
    <div style="text-align: right; margin-bottom: 15px; text-transform: uppercase; font-size: 11pt; font-weight: bold; line-height: 1.6;">
        <div>No. De oficio: CAPA/JMM/G/{{ $requirement->oficio_number }}/{{ $requirement->year }}</div>
        <div>Asunto: SOLICITUD DE REPOSICION DE FONDO REVOLVENTE No. {{ $requirement->revolving_fund_number }}-{{ $requirement->year }}</div>
        <div>JOSE MARIA MORELOS QUINTANA ROO A {{ $fecha_formateada }}</div>
    </div>

    <div style="text-align: center; font-size: 10pt; margin-bottom: 20px; font-style: italic;">"{{ $settings['leyenda_anio'] ?? '' }}"</div>

    <!-- Destinatario -->
    <div class="addressee">
        <p>C. {{ strtoupper($destinatario->nombre ?? 'HECTOR SEGUNDO MASEGOSA LLANAS') }}</p>
        <p>COORD. ADMVO. FINANCIERO Y DE ARCHIVO DE LA CAPA.</p>
        <p>PRESENTE:</p>
    </div>

    <!-- Body -->
    <div class="content">
        <p>Por medio de la presente, me permito enviar a Usted facturas para reposicion del Fondo Revolvente {{ $requirement->revolving_fund_number }}-{{ $requirement->year }}, por un importe de $&nbsp;{{ number_format($requirement->total, 2) }} (Son: {{ ucfirst($importe_letras) }}) por Gastos Menores ocasionados por este organismo operador.</p>
        <p>Sin otro asunto en particular, me es grato hacer propicia la ocasión para enviarle un cordial saludo.</p>
    </div>

    <!-- Firma -->
    <div style="text-align: center; margin-top: 20px;">
        <p style="font-weight: bold; margin-bottom: 50px;">ATENTAMENTE</p>
        <div style="border-top: 1px solid #000; width: 50%; margin: 0 auto 4px; padding-top: 4px;">
            <div style="font-weight: bold; text-transform: uppercase; font-size: 11pt;">{{ $requirement->manager->nombre ?? 'C. LUIS DANIEL HEREDIA DUARTE' }}</div>
            <div style="font-size: 11pt; text-transform: uppercase; font-weight: bold;">{{ $requirement->manager->puesto ?? 'GERENTE DEL ORGANISMO OPER. JMM.' }}</div>
        </div>
    </div>

    <div class="ccp">
        C.C.P. ARCHIVO
    </div>

    @if($settings['footer_imagen'] ?? false)
    <div class="footer">
        <img src="{{ $settings['footer_imagen'] }}">
    </div>
    @endif
</body>
</html>
