<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Anexo 4 - Fondo Revolvente {{ $requirement->revolving_fund_number }}</title>
    <style>
        @page { margin: 1cm 1.5cm; size: letter portrait; }
        body { font-family: Arial, sans-serif; font-size: 9pt; margin: 0; }

        .header-title { text-align: center; font-weight: bold; font-size: 10pt; margin-bottom: 4px; }
        .header-sub   { text-align: center; font-weight: bold; font-size: 9pt; margin-bottom: 4px; }
        .header-doc   { text-align: center; font-weight: bold; font-size: 9pt; margin-bottom: 8px; }

        .top-box { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .top-box td { border: 1px solid #000; padding: 4px 6px; font-size: 8pt; vertical-align: middle; }

        .concepts-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .concepts-table td { padding: 2px 5px; font-size: 8pt; }
        .concepts-table .chapter-row td { font-weight: bold; border-top: 1px solid #000; }
        .concepts-table .total-row td { font-weight: bold; border-top: 2px solid #000; font-size: 9pt; }
        .text-right { text-align: right; }

        .bank-table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 8pt; }
        .bank-table th { background: #eee; border: 1px solid #000; padding: 3px 5px; text-align: center; }
        .bank-table td { border: 1px solid #000; padding: 3px 5px; }

        .signatures { width: 100%; margin-top: 30px; }
        .sig-col { width: 50%; text-align: center; vertical-align: top; padding: 0 20px; }
        .sig-line { border-top: 1px solid #000; width: 70%; margin: 0 auto 4px; }
        .sig-name { font-weight: bold; text-transform: uppercase; font-size: 8pt; }
        .sig-role { font-size: 7.5pt; text-transform: uppercase; }

        .logo-logos { width: 100%; margin-bottom: 8px; }
    </style>
</head>
<body>

    <!-- Logos header -->
    <table class="logo-logos">
        <tr>
            <td style="text-align: left; width: 15%; vertical-align: middle;">
                @if($settings['logo_qroo'] ?? false)
                    <img src="{{ $settings['logo_qroo'] }}" style="height: 55px;">
                @endif
            </td>
            <td style="text-align: center; width: 70%; vertical-align: middle;">
                <div class="header-title">COMISIÓN DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO DE QUINTANA ROO</div>
                <div class="header-sub">ANEXO 4</div>
                <div class="header-doc">ENTERO DE REPOSICIÓN Y/O CANCELACIÓN DE FONDO REVOLVENTE</div>
            </td>
            <td style="text-align: right; width: 15%; vertical-align: middle;">
                @if($settings['logo_unidos'] ?? false)
                    <img src="{{ $settings['logo_unidos'] }}" style="height: 45px;">
                @endif
            </td>
        </tr>
    </table>

    <!-- Top info boxes -->
    <table class="top-box">
        <tr>
            <td style="width: 25%;">5309.22: <strong>$ {{ number_format($requirement->total, 2) }}</strong></td>
            <td style="width: 25%; text-align: center; font-weight: bold;">MONEDA NACIONAL</td>
            <td style="width: 50%;"></td>
        </tr>
        <tr>
            <td colspan="2">N° DE SOLICITUD Y/O RECIBO</td>
            <td><strong>{{ $requirement->oficio_number }}/{{ $requirement->year }}/FRV-{{ str_pad($requirement->revolving_fund_number, 3, '0', STR_PAD_LEFT) }}</strong></td>
        </tr>
    </table>

    <!-- Enteré text -->
    <p style="font-size: 8pt; text-align: justify; margin: 6px 0;">
        <strong>ENTERÉ:</strong> A LA COMISIÓN DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO DE QUINTANA ROO A TRAVÉS DE LA COORDINACIÓN ADMINISTRATIVA,
        FINANCIERA Y DE ARCHIVOS; LA CANTIDAD DE: $&nbsp;{{ number_format($requirement->total, 2) }}
        ({{ strtoupper($importe_letras) }}) POR CONCEPTO DE ENTERO DE FONDO REVOLVENTE ASIGNADO PARA LA OPERATIVIDAD DEL
        (ORGANISMO OPERADOR JOSE MARIA MORELOS).
    </p>

    <!-- Breakdown by Chapter / Partida -->
    @php
        // Group items by capitulo
        $byCapitulo = $requirement->items->groupBy(fn($i) => $i->partida->capitulo->codigo ?? '0000');
        ksort($byCapitulo->toArray()); // sort by chapter code
    @endphp

    <table class="concepts-table">
        @foreach($byCapitulo as $capCode => $items)
            @php
                $cap = $items->first()->partida->capitulo ?? null;
                $capTotal = $items->sum('amount');
            @endphp
            <tr class="chapter-row">
                <td style="width: 8%;">CAPITULO {{ $capCode }}</td>
                <td style="width: 57%;">{{ strtoupper($cap->nombre ?? '') }}</td>
                <td style="width: 15%;"></td>
                <td style="width: 20%; text-align: right; font-weight: bold;">
                    $ {{ number_format($capTotal, 2) }}
                </td>
            </tr>
            @foreach($items as $item)
                @php
                    $partida = $item->partida;
                    // Sum all items with same partida within this capitulo
                @endphp
                <tr>
                    <td style="padding-left: 15px;">{{ $partida->codigo ?? '' }}</td>
                    <td>{{ strtoupper($partida->nombre ?? '') }}</td>
                    <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                    <td></td>
                </tr>
            @endforeach
        @endforeach

        <!-- ISR Deducción -->
        @if($requirement->retention_isr > 0)
        <tr>
            <td colspan="2" style="padding-left: 15px; font-weight: bold;">DESCUENTO ISR</td>
            <td class="text-right" style="font-weight: bold;">{{ number_format($requirement->retention_isr, 2) }}</td>
            <td></td>
        </tr>
        @endif

        <!-- IVA -->
        <tr style="border-top: 1px solid #000;">
            <td colspan="2" style="padding-left: 15px; font-weight: bold;">I V A &nbsp;&nbsp; ACREDITABLE</td>
            <td class="text-right" style="font-weight: bold;">{{ number_format($requirement->iva, 2) }}</td>
            <td></td>
        </tr>

        <!-- Total -->
        <tr class="total-row">
            <td colspan="2" class="text-right">TOTAL</td>
            <td class="text-right">$ {{ number_format($requirement->total, 2) }}</td>
            <td></td>
        </tr>

        <tr>
            <td colspan="2" class="text-right" style="font-size: 7.5pt;">OTROS DESCUENTOS</td>
            <td class="text-right">0</td>
            <td></td>
        </tr>

        <tr>
            <td colspan="4" style="text-align: right; font-weight: bold; font-size: 9pt; padding-top: 6px;">IMPORTE</td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td colspan="2" class="text-right" style="font-weight: bold; font-size: 11pt; border: 2px solid #000;">
                {{ number_format($requirement->total, 2) }}
            </td>
        </tr>
    </table>

    <p style="text-align: right; font-size: 8pt; margin: 10px 0;">
        JOSE MARIA MORELOS &nbsp; QUINTANA ROO {{ $fecha_lugar }}
    </p>

    <!-- Bank account -->
    @if($requirement->manager)
    <table class="bank-table">
        <thead>
            <tr>
                <th>Banco</th>
                <th>N° CTA/ Clave Interbancaria o Número de Tarjeta</th>
                <th>Beneficiario</th>
            </tr>
        </thead>
        <tbody>
            @php
                $mgr = $requirement->manager;
            @endphp
            <tr>
                <td>{{ strtoupper($mgr->banco ?? 'AZTECA') }}</td>
                <td>{{ $mgr->clabe ?? '' }}</td>
                <td>{{ strtoupper($mgr->nombre ?? '') }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Signatures -->
    <table class="signatures">
        <tr>
            <td class="sig-col">
                <p style="font-weight: bold; margin-bottom: 40px;">ENTERE</p>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $requirement->manager->nombre ?? '' }}</div>
                <div class="sig-role">{{ $requirement->manager->puesto ?? 'GERENTE DEL ORG. OPER. JMM' }}</div>
            </td>
            <td class="sig-col">
                <p style="font-weight: bold; margin-bottom: 40px;">Vo. Bueno</p>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $requirement->manager->nombre ?? '' }}</div>
                <div class="sig-role">GERENTE DEL ORGANISMO OPERADOR JMM,</div>

                <div style="margin-top: 15px; font-size: 7pt; text-align: center; color: #555; border: 1px solid #ccc; padding: 5px;">
                    COMISIÓN DE AGUA POTABLE<br>Y ALCANTARILLADO<br>DEL ESTADO DE QUINTANA ROO<br>
                    ORGANISMO OPERADOR<br>JOSE MARIA MORELOS
                </div>
            </td>
        </tr>
    </table>

    @if($settings['footer_imagen'] ?? false)
    <div style="position: fixed; bottom: -1cm; left: -1.5cm; right: -1.5cm; height: 80px; z-index: -1;">
        <img src="{{ $settings['footer_imagen'] }}" style="width: 100%; height: auto;">
    </div>
    @endif
</body>
</html>
