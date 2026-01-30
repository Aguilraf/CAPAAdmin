<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Solicitud de Material</title>
    <style>
        @page {
            margin: 0cm 0cm;
        }
        body {
            font-family: Arial, list-sans-serif;
            font-size: 11pt;
            margin-top: 1cm;
            margin-left: 2cm;
            margin-right: 2cm;
            margin-bottom: 2cm;
            color: #000;
        }
        
        /* Layout Helpers */
        .w-full { width: 100%; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-justify { text-align: justify; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-8 { margin-bottom: 2rem; }
        
        /* Header Logos */
        .header-table {
            width: 100%;
            margin-bottom: 0.5rem;
            border: none;
        }
        .header-table td {
            vertical-align: top;
            text-align: center;
            width: 50%;
        }
        
        /* Content Tables */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        .items-table th, .items-table td {
            border: 1px solid #9ca3af; /* gray-400 */
            padding: 8px;
            text-align: left;
            text-transform: uppercase;
        }
        .items-table th {
            background-color: #f3f4f6; /* gray-100 */
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4cm;
            z-index: -1;
        }
        .footer-bg {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }
        .footer-content {
            position: absolute;
            bottom: 1cm;
            width: 100%;
            text-align: center;
            font-size: 9pt;
            color: #374151; /* gray-700 */
        }
        .footer-content p {
            margin: 2px 0;
        }
        
        /* Signature Block */
        .signature-block {
            margin-top: 1cm;
            text-align: center;
            page-break-inside: avoid;
        }
        .signature-line {
            width: 60%;
            border-top: 1px solid #000;
            margin: 0 auto;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td>
                @if($settings['logo_qroo'] ?? false)
                    <img src="{{ storage_path('app/public/' . $settings['logo_qroo']) }}" style="height: 80px;">
                @else
                    <div style="height: 80px; width: 100%; background: #eee;">[LOGO QROO]</div>
                @endif
                <div style="font-size: 9pt; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px;">
                    Quintana Roo<br>Gobierno del Estado
                </div>
            </td>
            <td>
                @if($settings['logo_unidos'] ?? false)
                    <img src="{{ storage_path('app/public/' . $settings['logo_unidos']) }}" style="height: 60px;">
                @else
                     <div style="height: 60px; width: 100%; background: #eee;">[LOGO UNIDOS]</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Meta Info -->
    <div class="text-right mb-8">
        <p class="font-bold">Asunto: <span style="font-weight: normal;">SOLICITO MATERIAL DE OFICINA</span></p>
        <p>José María Morelos, Quintana Roo, {{ $fecha_formateada }}</p>
    </div>

    <!-- Addressee -->
    <div class="mb-4 uppercase" style="line-height: 1.1;">
        <p class="font-bold" style="margin: 0;">C. {{ $data['destinatario_nombre'] }}</p>
        <p style="margin: 0;">{{ $data['destinatario_cargo'] }}</p>
        <p style="margin: 0;">JOSE MARIA MORELOS</p>
        <p style="margin: 0;">Presente:</p>
    </div>

    <!-- Body -->
    <div class="text-justify mb-4" style="line-height: 1.6;">
        <p style="text-indent: 2em; margin: 0;">
            Con la finalidad de llevar a cabo los trabajos en el Área de {{ $data['solicitante_departamento'] ?? 'departamento de Recursos Financieros' }}, por lo que le solicito el siguiente material:
        </p>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 70%;">Articulo</th>
                <th style="width: 30%;">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['items'] as $item)
            <tr>
                <td>{{ $item['custom_articulo'] ?? 'Material Desconocido' }}</td>
                <td>{{ $item['cantidad'] }} {{ $item['custom_unidad'] ?? 'PIEZAS' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Closing -->
    <div class="text-justify mb-8" style="line-height: 1.6;">
        <p style="text-indent: 2em; margin: 0;">
            Sin otro asunto en particular, me es grato hacer propicia la ocasión para enviarle un cordial saludo.
        </p>
    </div>

    <!-- Signature -->
    <div class="signature-block">
        <p class="font-bold mb-4">ATENTAMENTE</p>
        <div style="height: 1.5cm;"></div> <!-- Space for signature -->
        <div class="signature-line">
            <p class="font-bold uppercase" style="margin: 0;">{{ $data['solicitante_nombre'] }}</p>
            <p class="uppercase" style="font-size: 10pt; margin: 0;">{{ $data['solicitante_cargo'] }}</p>
        </div>
    </div>

    <!-- C.C.P. -->
    <div style="font-size: 7pt; margin-top: 0.5cm; margin-left: 0; text-align: left;">
        <p>C.C.P.-.-MINUTARIO</p>
    </div>

    <!-- Footer -->
    <div class="footer">
        @if($settings['footer_imagen'] ?? false)
            <img class="footer-bg" src="{{ storage_path('app/public/' . $settings['footer_imagen']) }}" alt="Footer Background">
        @else
             <div class="footer-bg" style="border-bottom: 5px solid pink;"></div>
        @endif

        <div class="footer-content">


            <div style="display: inline-block;">
                @if(!empty($settings['footer_organismo']))
                    <p class="font-bold">{{ $settings['footer_organismo'] }}</p>
                @endif
                @if(!empty($settings['footer_direccion']))
                    <p>{{ $settings['footer_direccion'] }}</p>
                @endif
                <p>
                    @if(!empty($settings['footer_telefono']))
                        <span>Tel.: {{ $settings['footer_telefono'] }} </span>
                    @endif
                    @if(!empty($settings['footer_email']))
                        <span>{{ $settings['footer_email'] }}</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</body>
</html>
