<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Anexo 15 Bomberos {{ $requirement->requirement_number }}</title>
    <style>
        @page { margin: 0.5cm 1cm; }
        body { font-family: Arial, sans-serif; font-size: 9.5pt; line-height: 1.3; }
        
        .header-table { width: 100%; margin-bottom: 5px; }
        .logo-qroo { height: 45px; }
        .logo-capa { height: 45px; }
        
        .title-container { text-align: center; font-weight: bold; margin-bottom: 15px; }
        .title-main { font-size: 10pt; }
        .title-anexo { font-size: 11pt; margin-top: 5px; }
        
        .top-subtitle { text-align: center; font-weight: bold; margin-bottom: 10px; text-transform: uppercase; border: 1px solid #000; padding: 2px; }
        
        .amount-box { float: right; border: 1.5px solid #000; padding: 5px 15px; font-weight: bold; margin-bottom: 5px; }
        
        .folio-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .folio-table td { border: 1.5px solid #000; padding: 5px; text-align: center; }
        .folio-label { font-size: 8pt; font-weight: bold; width: 20%; background-color: #f0f0f0; }
        .folio-value { font-size: 9pt; width: 30%; }

        .entere-text { text-align: justify; margin-bottom: 15px; line-height: 1.4; }
        
        .breakdown-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .breakdown-table td { padding: 4px; border: none; }
        .breakdown-item { font-weight: bold; }
        .amount-cell { text-align: right; font-weight: bold; width: 15%; border-bottom: 1px solid #000 !important; }
        .empty-cell { width: 10%; }
        
        .total-section { margin-top: 10px; width: 40%; float: right; }
        .total-box { border: 1.5px solid #000; padding: 5px; text-align: center; font-weight: bold; font-size: 10pt; }
        
        .date-place { text-align: right; margin-top: 20px; text-transform: uppercase; font-weight: bold; }
        
        .bank-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .bank-table th, .bank-table td { border: 1.5px solid #000; padding: 4px; text-align: center; }
        .bank-table th { background-color: #f0f0f0; font-size: 8pt; }
        
        .signatures-table { width: 100%; margin-top: 40px; border-collapse: collapse; }
        .signatures-table td { width: 50%; text-align: center; vertical-align: top; padding: 0 40px; }
        .sig-label { font-weight: bold; margin-bottom: 40px; }
        .sig-name { font-weight: bold; padding-top: 5px; text-transform: uppercase; }
        .sig-role { font-size: 8pt; text-transform: uppercase; font-weight: bold; }
        
        .footer { 
            position: fixed; 
            bottom: -30px; 
            left: -1cm; 
            right: -1cm; 
            height: 100px; 
            text-align: center;
            z-index: -1000;
        }
        .footer img { width: 100%; height: auto; }
        
        .clear { clear: both; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 20%;"><img src="{{ $settings['logo_qroo'] }}" class="logo-qroo"></td>
            <td style="width: 60%;" class="title-container">
                <div class="title-main">COMISIÓN DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO DE QUINTANA ROO</div>
                <div class="title-anexo">ANEXO 15</div>
            </td>
            <td style="width: 20%; text-align: right;"><img src="{{ $settings['logo_capa'] ?? $settings['logo_qroo'] }}" class="logo-capa"></td>
        </tr>
    </table>

    <div class="top-subtitle">
        ENTERO DE REPOSICION Y/O CANCELACIÓN DE FONDO FIJO PARA PAGO DE COMISIONES A LOS RESPONSABLES DEL COBRO DEL SERVICIO DE AGUA EN LAS DIFERENTES COMUNIDADES RURALES
    </div>

    <div class="amount-box">
        BUENO POR $ {{ number_format($requirement->total, 2) }}<br>
        <span style="font-size: 7pt; font-weight: normal;">MONEDA NACIONAL</span>
    </div>
    <div class="clear"></div>

    <table class="folio-table" style="width: 40%; float: right; margin-top: 5px;">
        <tr>
            <td class="folio-label">N° DE SOLICITUD Y/O RECIBO</td>
            <td class="folio-value">CAPA/JMM/G/{{ $requirement->oficio_number }}/{{ $requirement->year }}/FB-01</td>
        </tr>
    </table>
    <div class="clear"></div>

    <div class="entere-text" style="margin-top: 10px;">
        <strong>ENTERÉ:</strong> A LA COMISIÓN DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO DE QUINTANA ROO A TRAVÉS DE LA COORDINACIÓN ADMINISTRATIVA, FINANCIERA Y DE ARCHIVOS; LA CANTIDAD DE: <strong>$ {{ number_format($requirement->total, 2) }}</strong> (son: {{ $importe_letras }} ) POR CONCEPTO DE ENTERO DE REPOSICION DE FONDO DE BOMBEROS NUMERO <strong>{{ str_pad($requirement->requirement_number, 2, '0', STR_PAD_LEFT) }}/{{ $requirement->year }}</strong> CORRESPONDIENTE A LA FACTURACIÓN DEL MES DE <strong>{{ strtoupper($requirement->month_billed ?? '') }}-{{ $requirement->year_billed ?? '' }}</strong> RECAUDADO EN <strong>{{ strtoupper($requirement->month_charged ?? '') }}-{{ $requirement->year_charged ?? '' }}</strong> PARA PAGO DE COMISIONES A LOS RESPONSABLES DEL COBRO DEL SERVICIO DE AGUA EN LAS DIFERENTES COMUNIDADES RURALES ASIGNADO PARA LA OPERATIVIDAD DEL ORGANISMO OPERADOR JOSE MARIA MORELOS.
    </div>

    <table class="breakdown-table">
        @foreach($requirement->items as $item)
        <tr>
            <td class="breakdown-item" colspan="3">CAPITULO {{ $item->partida->capitulo->codigo ?? '3000' }} {{ $item->partida->capitulo->nombre ?? 'SERVICIOS GENERALES' }}.</td>
            <td class="empty-cell"></td>
            <td class="amount-cell">$ {{ number_format($item->amount, 2) }}</td>
        </tr>
        <tr>
            <td style="padding-left: 20px;">{{ $item->partida->codigo ?? '34201' }}</td>
            <td colspan="2">{{ $item->partida->nombre ?? 'SERVICIO DE COBRANZA, INVESTIGACIÓN CREDITICIA Y SIMILAR' }}</td>
            <td style="text-align: right; padding-right: 15px;">{{ number_format($item->amount, 2) }}</td>
            <td></td>
        </tr>
        @endforeach
        
        <tr>
            <td colspan="3">IVA ACREDITABLE</td>
            <td class="empty-cell"></td>
            <td class="amount-cell">-</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: right; padding-right: 40px; font-weight: bold;">TOTAL</td>
            <td class="empty-cell"></td>
            <td class="amount-cell">$ {{ number_format($requirement->total, 2) }}</td>
        </tr>
    </table>

    <div class="total-section">
        <p style="text-align: right; font-weight: bold; margin-bottom: 2px;">IMPORTE</p>
        <div class="total-box">$ {{ number_format($requirement->total, 2) }}</div>
    </div>
    <div class="clear"></div>

    <div class="date-place">
        JOSE MARIA MORELOS, QUINTANA ROO {{ $fecha_formateada }}
    </div>

    <p style="font-weight: bold; margin-bottom: 2px;">Abonar (o abonado en caso de cancelación) a la cuenta:</p>
    <table class="bank-table">
        <thead>
            <tr>
                <th>Banco</th>
                <th>N° CTA/ Clave Interbancaria</th>
                <th>Beneficiario</th>
            </tr>
        </thead>
        <tbody>
            @php $item = $requirement->items->first(); @endphp
            <tr>
                <td>{{ $subgerente->banco ?? ($item->employee->banco ?? 'AZTECA') }}</td>
                <td>{{ $subgerente->clabe ?? ($item->employee->clabe ?? '01720154967600') }}</td>
                <td>{{ $subgerente->nombre ?? ($item->employee->nombre ?? 'ING. JOSUE RODRIGUEZ PAMPLONA') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signatures-vertical" style="margin-top: 30px; text-align: center;">
        <div style="margin-bottom: 40px;">
            <div class="sig-label">ENTERÉ</div>
            <div style="margin-top: 40px;">
                <div class="sig-name">{{ $subgerente->nombre ?? 'ING. JOSUE RODRIGUEZ PAMPLONA' }}</div>
                <div class="sig-role">{{ $subgerente->puesto ?? 'SUBGERENTE COMERCIAL' }}</div>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <div class="sig-label" style="font-weight: normal;">Vo. Bueno</div>
            <div style="margin-top: 40px;">
                <div class="sig-name">{{ $requirement->manager->nombre ?? 'C. LUIS DANIEL HEREDIA DUARTE' }}</div>
                <div class="sig-role">{{ $requirement->manager->puesto ?? 'GERENTE' }}</div>
            </div>
        </div>
    </div>

    @if($settings['footer_imagen'] ?? false)
    <div class="footer">
        <img src="{{ $settings['footer_imagen'] }}">
    </div>
    @endif
</body>
</html>
