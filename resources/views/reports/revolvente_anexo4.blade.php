<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Anexo 4 - Fondo Revolvente {{ $requirement->revolving_fund_number }}</title>
    <style>
        @page { margin: 1cm 1.5cm; size: letter portrait; }
        body { font-family: Arial, sans-serif; font-size: 9.5pt; margin: 0; }

        .header-title { text-align: center; font-weight: bold; font-size: 10.5pt; margin-bottom: 4px; white-space: nowrap; }
        .header-sub   { text-align: center; font-weight: bold; font-size: 10.5pt; margin-bottom: 4px; }
        .header-doc   { text-align: center; font-weight: bold; font-size: 9.5pt; margin-bottom: 0px; text-transform: uppercase; white-space: nowrap; }

        .info-header-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .amount-box { border: 1.5px solid #000; padding: 4px 10px; font-weight: bold; font-size: 11pt; display: inline-block; min-width: 120px; text-align: right; }
        .currency-label { font-weight: bold; font-size: 9.5pt; margin-top: 2px; }
        
        .concepts-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; margin-top: 15px; }
        .concepts-table td { padding: 2px 5px; font-size: 8pt; }
        .concepts-table .chapter-row td { font-weight: bold; }
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

        .logo-logos { width: 100%; margin-bottom: 0px; }
    </style>
</head>
<body>
    <table class="logo-logos">
        <tr>
            <td style="text-align: left; width: 15%; vertical-align: middle;">
                @if($settings['logo_qroo'] ?? false)
                    <img src="{{ $settings['logo_qroo'] }}" style="height: 60px;">
                @endif
            </td>
            <td style="text-align: center; width: 85%; vertical-align: middle; padding-right: 15%;">
                <div class="header-title">COMISIÓN DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO DE QUINTANA ROO</div>
                <div class="header-sub">ANEXO 4</div>
                <div class="header-doc">ENTERO DE REPOSICIÓN Y/O CANCELACIÓN DE FONDO REVOLVENTE</div>
            </td>
        </tr>
    </table>

    <!-- Header Box Section -->
    <div style="width: 100%; margin-top: 5px;">
        <!-- Amount Box Row -->
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 70%;"></td>
                <td style="width: 30%; text-align: right;">
                    <div style="font-weight: bold; font-size: 11pt; text-align: right;">
                        $ {{ number_format($requirement->total, 2) }}
                    </div>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="text-align: right;">
                    <div class="currency-label">MONEDA NACIONAL</div>
                </td>
            </tr>
        </table>

        <!-- Solicitud Row -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 5px;">
            <tr>
                <td style="width: 55%; text-align: right; font-weight: bold; font-size: 10.5pt; padding-right: 15px;">
                    N° DE SOLICITUD Y/O RECIBO
                </td>
                <td style="width: 2px; border-left: 2px solid #000; height: 30px;"></td>
                <td style="padding-left: 15px; font-weight: bold; font-size: 10.5pt; vertical-align: middle;">
                    CAPA/JMM/G/{{ $requirement->oficio_number }}/{{ $requirement->year }}/FRV-{{ str_pad($requirement->revolving_fund_number, 3, '0', STR_PAD_LEFT) }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Enteré text -->
    <p style="font-size: 8pt; text-align: justify; margin: 0px 0 4px 0; line-height: 1.1;">
        <strong>ENTERÉ:</strong> A LA COMISIÓN DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO DE QUINTANA ROO A TRAVÉS DE LA COORDINACIÓN ADMINISTRATIVA,
        FINANCIERA Y DE ARCHIVOS; LA CANTIDAD DE: $&nbsp;{{ number_format($requirement->total, 2) }}
        ({{ strtoupper($importe_letras) }}) POR CONCEPTO DE ENTERO DE FONDO REVOLVENTE ASIGNADO PARA LA OPERATIVIDAD DEL
        (ORGANISMO OPERADOR JOSE MARIA MORELOS).
    </p>

    <!-- Breakdown by Chapter / Partida -->
    @php
        // Group items safely by chapter code, sort chapters, and sort items inside by partida code
        $byCapitulo = $requirement->items->groupBy(fn($i) => $i->partida?->capitulo?->codigo ?? '0000')
            ->sortKeys()
            ->map(fn($items) => $items->sortBy(fn($i) => $i->partida?->codigo ?? '00000'));
    @endphp

    <table class="concepts-table">
        @foreach($byCapitulo as $capCode => $items)
            @php
                $cap = $items->first()?->partida?->capitulo ?? null;
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
    </table>

    <table class="concepts-table" style="margin-top: 5px;">
        <!-- Descuentos -->
        @if(($extraTotals['discount'] ?? 0) > 0)
        <tr>
            <td style="width: 8%;"></td>
            <td style="width: 57%; padding-left: 15px; font-weight: bold;">DESCUENTO / BONIFICACIONES</td>
            <td style="width: 15%; font-weight: bold;" class="text-right ">{{ number_format($extraTotals['discount'], 2) }}</td>
            <td style="width: 20%;"></td>
        </tr>
        @endif

        <!-- IEPS -->
        @if(($extraTotals['ieps'] ?? 0) > 0)
        <tr>
            <td style="width: 8%;"></td>
            <td style="width: 57%; padding-left: 15px; font-weight: bold;">I.E.P.S.</td>
            <td style="width: 15%; font-weight: bold;" class="text-right ">{{ number_format($extraTotals['ieps'], 2) }}</td>
            <td style="width: 20%;"></td>
        </tr>
        @endif

        <!-- RETENCION ISR -->
        @if(($extraTotals['ret_isr'] ?? 0) > 0)
        <tr>
            <td style="width: 8%;"></td>
            <td style="width: 57%; padding-left: 15px; font-weight: bold;">RETENCION I.S.R.</td>
            <td style="width: 15%; font-weight: bold;" class="text-right ">{{ number_format($extraTotals['ret_isr'], 2) }}</td>
            <td style="width: 20%;"></td>
        </tr>
        @endif

        <!-- RETENCION IVA -->
        @if(($extraTotals['ret_iva'] ?? 0) > 0)
        <tr>
            <td style="width: 8%;"></td>
            <td style="width: 57%; padding-left: 15px; font-weight: bold;">RETENCION I.V.A.</td>
            <td style="width: 15%; font-weight: bold;" class="text-right ">{{ number_format($extraTotals['ret_iva'], 2) }}</td>
            <td style="width: 20%;"></td>
        </tr>
        @endif

        <!-- IVA -->
        <tr>
            <td style="width: 8%;"></td>
            <td style="width: 57%; padding-left: 15px; font-weight: bold;">I V A &nbsp;&nbsp; ACREDITABLE</td>
            <td style="width: 15%; font-weight: bold;" class="text-right ">{{ number_format($extraTotals['iva'] ?: ($requirement->iva ?? 0), 2) }}</td>
            <td style="width: 20%;"></td>
        </tr>

        <!-- Total -->
        <tr>
            <td style="width: 8%;"></td>
            <td style="width: 57%; text-align: right; font-weight: bold; font-size: 9pt;">TOTAL</td>
            <td style="width: 15%; font-weight: bold; font-size: 9pt;" class="text-right ">$ {{ number_format($requirement->total, 2) }}</td>
            <td style="width: 20%;"></td>
        </tr>

    </table>

    <!-- Final IMPORTE Box and Date -->
    <table style="width: 100%; margin-top: 15px;">
        <tr>
            <td style="width: 70%;"></td>
            <td style="width: 30%; text-align: center;">
                <div style="font-weight: bold; font-size: 9pt; margin-bottom: 3px; text-align: center; margin-left: 20px;">IMPORTE</div>
                <div style="border: 2px solid #000; font-weight: bold; font-size: 11pt; padding: 4px 10px; display: inline-block; min-width: 120px; text-align: right;">
                    {{ number_format($requirement->total, 2) }}
                </div>
            </td>
        </tr>
    </table>

    <p style="text-align: right; font-size: 9.5pt; margin: 15px 0 5px 0; text-transform: uppercase;">
        JOSE MARIA MORELOS QUINTANA ROO {{ $fecha_lugar }}.
    </p>

    <!-- Bank account -->
    <p style="font-size: 9pt; margin-bottom: 2px;">Abonar (o abonado en caso de cancelación) a la cuenta:</p>
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
                <td>AZTECA</td>
                <td>01720158496007</td>
                <td>{{ strtoupper($mgr->nombre ?? '') }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Signatures -->
    <div style="width: 100%; margin-top: 10px; text-align: center;">
        <div style="width: 50%; margin: 0 auto 20px auto;">
            <p style="font-weight: bold; margin-bottom: 20px; font-size: 9pt;">ENTERE</p>
            <div style="border-top: 1px solid #000; width: 90%; margin: 0 auto 4px;"></div>
            <div style="font-weight: bold; text-transform: uppercase; font-size: 8pt;">
                {{ $requirement->manager?->nombre ?? '' }}
            </div>
            <div style="font-size: 7.5pt; text-transform: uppercase;">GERENTE DEL ORG. OPER. JMM</div>
        </div>

        <div style="width: 50%; margin: 0 auto;">
            <p style="font-weight: bold; margin-bottom: 20px; font-size: 9pt;">Vo. Bueno</p>
            <div style="border-top: 1px solid #000; width: 90%; margin: 0 auto 4px;"></div>
            <div style="font-weight: bold; text-transform: uppercase; font-size: 8pt;">
                C. {{ $requirement->manager?->nombre ?? '' }}
            </div>
            <div style="font-size: 7.5pt; text-transform: uppercase;">GERENTE DEL ORGANISMO OPERADOR JMM,</div>
        </div>
    </div>

    @if($settings['footer_imagen'] ?? false)
    <div style="position: fixed; bottom: -1cm; left: -1.5cm; right: -1.5cm; height: 80px; z-index: -1;">
        <img src="{{ $settings['footer_imagen'] }}" style="width: 100%; height: auto;">
    </div>
    @endif
</body>
</html>
