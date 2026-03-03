    <style>
        @page { margin: 4.6cm 1cm 3cm 1cm; size: letter landscape; }
        body { font-family: Arial, sans-serif; font-size: 7.2pt; margin: 0; }

        .header {
            position: fixed;
            top: -4.2cm;
            left: 0px;
            right: 0px;
            height: 4.2cm;
        }

        .page-header { width: 100%; margin-bottom: 2px; }

        .report-title { font-weight: bold; font-size: 9pt; text-align: center; margin-bottom: 3px; }
        .report-sub   { font-size: 8pt; text-align: center; margin-bottom: 2px; }

        table.main-table { width: 100%; border-collapse: collapse; table-layout: fixed; border: 1px solid #000; }
        table.main-table th {
            background: #ddd;
            border: 1px solid #000;
            padding: 3px 2px;
            font-size: 6.8pt;
            text-align: center;
            vertical-align: middle;
        }
        table.main-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            font-size: 6.8pt;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .subtotal-row td { font-weight: bold; border-top: 2px solid #000; background: #f9f9f9; padding: 4px; }
        .total-row td { font-weight: bold; border-top: 2px solid #000; background: #e0e0e0; font-size: 7.5pt; padding: 5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .footer {
            position: fixed;
            bottom: -2.5cm;
            left: 0;
            right: 0;
            height: 100px;
            text-align: center;
        }

        .signatures-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .sig-cell { 
            text-align: center; 
            vertical-align: top; 
            width: 16.6%; 
            padding: 0 2px; 
            font-size: 6.2pt;
        }
        .sig-line { border-top: 1px solid #000; width: 95%; margin: 0 auto 2px; }
        .sig-label { font-weight: bold; font-size: 6.8pt; margin-bottom: 25px; }
        .sig-name { font-weight: bold; text-transform: uppercase; font-size: 6.2pt; line-height: 1; }
        .sig-puesto { font-size: 5.4pt; line-height: 1; word-spacing: -0.6px; }

        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <!-- Fixed Footer Container (appears on all pages) -->
    <div class="footer">
        <table class="signatures-table">
            <tr>
                <td class="sig-cell">
                    <div class="sig-label">ELABORÓ</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $firmas['elaboro']?->nombre ?? 'HEREDIA DUARTE LUIS DANIEL' }}</div>
                    <div class="sig-puesto">{{ strtoupper($firmas['elaboro']?->puesto ?? 'GERENTE ORG. OPER. JMM') }}</div>
                </td>
                <td class="sig-cell">
                    <div class="sig-label">REVISÓ</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $firmas['reviso']?->nombre ?? 'CERVANTES SANCHEZ MARIANO' }}</div>
                    <div class="sig-puesto">{{ strtoupper($firmas['reviso']?->puesto ?? 'SUBGERENTE ADMINISTRATIVO') }}</div>
                </td>
                <td class="sig-cell">
                    <div class="sig-label">AUTORIZÓ</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $firmas['autorizo']?->nombre ?? 'HEREDIA DUARTE LUIS DANIEL' }}</div>
                    <div class="sig-puesto">{{ strtoupper($firmas['autorizo']?->puesto ?? 'GERENTE ORG. OPER. JMM') }}</div>
                </td>
                <td class="sig-cell">
                    <div class="sig-label">VALIDÓ</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $firmas['valido']?->nombre ?? 'LIC. JOAQUIN ISRAEL PEREZ MALDONADO' }}</div>
                    <div class="sig-puesto">{{ strtoupper($firmas['valido']?->puesto ?? 'DIRECTOR DE RECURSOS MATERIALES Y DE ARCHIVO') }}</div>
                </td>
                <td class="sig-cell">
                    <div class="sig-label">Vo. Bo.</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $firmas['vobo']?->nombre ?? 'CINDY BLAISDEL NOVELO NOVELO' }}</div>
                    <div class="sig-puesto">{{ strtoupper($firmas['vobo']?->puesto ?? 'DIRECTOR DE RECURSOS FINANCIEROS') }}</div>
                </td>
                <td class="sig-cell">
                    <div class="sig-label">MINISTRECE</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $firmas['ministrece']?->nombre ?? 'HECTOR SEGUNDO MASEGOSA LLANAS' }}</div>
                    <div class="sig-puesto">{{ strtoupper($firmas['ministrece']?->puesto ?? 'COORD. ADMVO. FINANCIERO Y DE ARCHIVO DE LA CAPA') }}</div>
                </td>
            </tr>
        </table>

        @if($settings['footer_imagen'] ?? false)
            <div style="margin-top: 10px; width: 100%;">
                <img src="{{ $settings['footer_imagen'] }}" style="width: 100%; height: auto;">
            </div>
        @endif
    </div>

    @php
        $start = $requirement->start_date ? \Carbon\Carbon::parse($requirement->start_date) : \Carbon\Carbon::now()->startOfMonth();
        $end = $requirement->end_date ? \Carbon\Carbon::parse($requirement->end_date) : \Carbon\Carbon::now()->endOfMonth();
        $startStr = $start->format('d') . ' DE ' . strtoupper($start->translatedFormat('F'));
        $endStr   = $end->format('d') . ' DE ' . strtoupper($end->translatedFormat('F'));
        $yearStr  = $end->format('Y');
        $oficioFull = 'CAPA/JMM/G/' . ($requirement->oficio_number ?? '---') . '/' . ($requirement->year ?? '2026') . '/FRV-' . str_pad($requirement->revolving_fund_number ?? '0', 3, '0', STR_PAD_LEFT);
    @endphp


    <!-- Persistent Header Container -->
    <div class="header">
        <!-- Logo and Title Table -->
        <table class="page-header" style="margin-top: 5px;">
            <tr>
                <td style="width: 15%; text-align: left; vertical-align: middle;">
                    @if($settings['logo_qroo'] ?? false)
                        <img src="{{ $settings['logo_qroo'] }}" style="height: 48px;">
                    @endif
                </td>
                <td style="width: 70%; text-align: center; vertical-align: middle;">
                    <div style="font-weight: bold; font-size: 7.5pt; text-align: center; margin-bottom: 1px;">Anexo 4 Asignación de Fondo Revolvente</div>
                    <div style="font-size: 7.5pt; text-align: center; margin-bottom: 2px;">Comisión de Agua Potable y Alcantarillado del Estado de Quintana Roo</div>
                    <div style="font-weight: bold; font-size: 8.5pt; text-align: center;">
                        CÉDULA DE CONTROL DE REPOSICIÓN Y/O CANCELACIÓN DE FONDO REVOLVENTE
                    </div>
                </td>
                <td style="width: 15%; text-align: right; vertical-align: middle;">
                    @if($settings['logo_unidos'] ?? false)
                        <img src="{{ $settings['logo_unidos'] }}" style="height: 38px;">
                    @endif
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 2px;">
            <tr>
                <td style="font-weight: bold; font-size: 7.5pt; text-align: left; padding: 0;">
                    PERIODO: DEL {{ $startStr }} AL {{ $endStr }} DEL {{ $yearStr }}
                </td>
                <td style="font-weight: bold; font-size: 7.5pt; text-align: right; padding: 0;">
                </td>
            </tr>
        </table>

        <!-- Meta Info Table (Top of Each Page) -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 2px; font-size: 7.5pt;">
            <tr>
                <td style="border: 1px solid #000; padding: 2px 5px; width: 22%;">UNIDAD ADMINISTRATIVA<br>RESPONSABLE DEL FONDO</td>
                <td style="border: 1px solid #000; padding: 2px 5px; width: 33%; vertical-align: bottom;">ORGANISMO OPERADOR : JOSE MARIA MORELOS</td>
                <td style="border: 1px solid #000; padding: 2px 5px; width: 15%; text-align: center; vertical-align: middle;">REPOSICIÓN:</td>
                <td style="border: 1px solid #000; padding: 2px 5px; width: 5%; text-align: center; background: #e0e0e0; vertical-align: middle;"><strong>X</strong></td>
                <td style="border: 1px solid #000; padding: 2px 5px; width: 10%; vertical-align: middle;">CANCELACIÓN:</td>
                <td style="border: 1px solid #000; padding: 2px 5px; width: 15%;"></td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 2px 5px;">RESPONSABLE DEL FONDO<br>REVOLVENTE:</td>
                <td style="border: 1px solid #000; padding: 2px 5px;">C. {{ strtoupper($requirement->manager->nombre ?? 'LUIS DANIEL HEREDIA DUARTE') }}</td>
                <td style="border: 1px solid #000; padding: 2px 5px;" colspan="3">NÚMERO DE SOLICITUD DE</td>
                <td style="border: 1px solid #000; padding: 2px 5px; text-align: center;">{{ $oficioFull }}</td>
            </tr>
        </table>
    </div>

    @php
        // Subtotal management - Trim folios to avoid grouping issues with invisible spaces
        $items = $items->map(function($i) {
            $i->invoice_folio = trim($i->invoice_folio);
            return $i;
        });
        
        // Column Totals Init
        $grandSubtotal = 0; $grandIva = 0; $grandDiscount = 0;
        $grandIeps = 0; $grandRetIsr = 0; $grandRetIva = 0; $grandTotal = 0;
    @endphp

    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 9%;">PROVEEDOR</th>
                <th rowspan="2" style="width: 7%;">N° DE FACTURA</th>
                <th rowspan="2" style="width: 7%;">FECHA DE FACTURA</th>
                <th rowspan="2" style="width: 20%;">CONCEPTO DE ADQUISICIÓN</th>
                <th rowspan="2" style="width: 14%;">OBJETO DEL GASTO</th>
                <th colspan="7">IMPORTE DE GASTOS EFECTUADOS EN LA REPOSICIÓN POR FACTURA</th>
                <th rowspan="2" style="width: 7%;">Total</th>
            </tr>
            <tr>
                <th style="width: 5%;">Subtotal</th>
                <th style="width: 4%;">I.V.A</th>
                <th style="width: 4%;">DESC.</th>
                <th style="width: 4%;">I.E.P.S</th>
                <th style="width: 4%;">RET. ISR</th>
                <th style="width: 4%;">RET. IVA</th>
                <th style="width: 5%;">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $count = 0; 

                // Primary Sort: Objeto del Gasto (partida code)
                $items = $items->sortBy(function($i) {
                    return $i->partida->codigo ?? 'ZZZZZ'; // Fallback to zzz so items without code go at the end
                });

                // Grouping: Provider + Folio + Date. 
                // Grouping after sort will preserve the sorted order for the first item of each group
                $invoiceGroups = $items->groupBy(function($i) {
                    $f = trim($i->invoice_folio ?? '');
                    if (!$f || $f == '0') return 'unique_item_' . $i->id;
                    $p = trim($i->provider_name ?? 'NO_PROV');
                    $d = $i->invoice_date ? $i->invoice_date->format('Ymd') : 'NO_DATE';
                    return $p . '|' . $f . '|' . $d;
                });
            @endphp
            @foreach($invoiceGroups as $invoiceKey => $invoiceItems)
                @php
                    $groupRowspan = $invoiceItems->count();
                    $groupTotal    = $invoiceItems->sum('amount');
                    $first = $invoiceItems->first();
                    
                    // Invoice-level data: if specific fields are zero, fallback to sum of parts/amount
                    $invSubtotal = $first->invoice_subtotal ?: $invoiceItems->sum('invoice_subtotal');
                    if ($invSubtotal == 0) $invSubtotal = $groupTotal;
                    
                    $invIva      = $first->invoice_iva ?: $invoiceItems->sum('invoice_iva');
                    $invDiscount = $first->invoice_discount ?: $invoiceItems->sum('invoice_discount');
                    $invIeps     = $first->invoice_ieps ?: $invoiceItems->sum('invoice_ieps');
                    $invRetIsr   = $first->invoice_retention_isr ?: $invoiceItems->sum('invoice_retention_isr');
                    $invRetIva   = $first->invoice_retention_iva ?: $invoiceItems->sum('invoice_retention_iva');
                    
                    // Accumulate totals for the final footer
                    $grandSubtotal += $invSubtotal; 
                    $grandIva      += $invIva;
                    $grandDiscount += $invDiscount; 
                    $grandIeps     += $invIeps;
                    $grandRetIsr   += $invRetIsr;   
                    $grandRetIva   += $invRetIva;
                    $grandTotal    += $groupTotal;

                    // Header data for the group
                    $pName = $first->provider_name ?? 'SIN PROVEEDOR';
                    $fNum = trim($first->invoice_folio ?? '');
                    if (!$fNum && $first->uuid) $fNum = $first->uuid;
                    // UUID Truncation: first two segments
                    if ($fNum && strlen($fNum) > 18) {
                        $parts = explode('-', $fNum);
                        $fNum = ($parts[0] ?? '') . (isset($parts[1]) ? '-' . $parts[1] : '');
                    }
                    $fDate = $first->invoice_date ? $first->invoice_date->format('d/m/Y') : '';
                @endphp

                @foreach($invoiceItems as $item)
                @php
                    $iSub    = $item->invoice_subtotal ?: 0;
                    $iIva    = $item->invoice_iva ?: 0;
                    $iDisc   = $item->invoice_discount ?: 0;
                    $iIeps   = $item->invoice_ieps ?: 0;
                    $iIsr    = $item->invoice_retention_isr ?: 0;
                    $iRetIva = $item->invoice_retention_iva ?: 0;
                    $iImp    = $item->amount ?: 0;
                @endphp
                <tr>
                    {{-- Provider: Always render td to maintain 13 columns. Remove top border for "merged" look if not first --}}
                    <td style="border: 1px solid #000; font-weight: bold; font-size: 6.5pt; text-align: center; vertical-align: middle; {{ !$loop->first ? 'border-top: none;' : '' }} {{ !$loop->last ? 'border-bottom: none;' : '' }}">
                        {{ $loop->first ? strtoupper($pName) : '' }}
                    </td>
                    <td class="text-center" style="border: 1px solid #000; font-size: 6.2pt; vertical-align: middle; {{ !$loop->first ? 'border-top: none;' : '' }} {{ !$loop->last ? 'border-bottom: none;' : '' }}">
                        {{ $loop->first ? ($fNum ?: 'S/F') : '' }}
                    </td>
                    <td class="text-center" style="border: 1px solid #000; font-size: 6.2pt; vertical-align: middle; {{ !$loop->first ? 'border-top: none;' : '' }} {{ !$loop->last ? 'border-bottom: none;' : '' }}">
                        {{ $loop->first ? $fDate : '' }}
                    </td>

                    <td style="font-size: 6.5pt; border: 1px solid #000;">
                        {{ strtoupper($item->description ?: ($item->partida ? $item->partida->nombre : '---')) }}
                    </td>
                    <td style="font-size: 6.5pt; border: 1px solid #000;">
                        {{ $item->partida->codigo ?? '' }} {{ strtoupper($item->partida->nombre ?? '') }}
                    </td>

                    {{-- Financial Detail Columns (Per Item) --}}
                    <td class="text-right" style="border: 1px solid #000;">{{ number_format($iSub, 2) }}</td>
                    <td class="text-right" style="border: 1px solid #000;">{{ number_format($iIva, 2) }}</td>
                    <td class="text-right" style="border: 1px solid #000;">{{ number_format($iDisc, 2) }}</td>
                    <td class="text-right" style="border: 1px solid #000;">{{ number_format($iIeps, 2) }}</td>
                    <td class="text-right" style="border: 1px solid #000;">{{ number_format($iIsr, 2) }}</td>
                    <td class="text-right" style="border: 1px solid #000;">{{ number_format($iRetIva, 2) }}</td>
                    <td class="text-right" style="border: 1px solid #000;">{{ number_format($iImp, 2) }}</td>
                    
                    {{-- Total Column: Always render td to maintain 13 columns. Merged look if not first --}}
                    <td class="text-right font-bold" style="font-weight: bold; border: 1px solid #000; vertical-align: middle; {{ !$loop->first ? 'border-top: none;' : '' }} {{ !$loop->last ? 'border-bottom: none;' : '' }}">
                        {{ $loop->first ? number_format($groupTotal, 2) : '' }}
                    </td>
                </tr>
                @php $count++; @endphp
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTALES</td>
                <td class="text-right">{{ number_format($grandSubtotal, 2) }}</td>
                <td class="text-right">{{ number_format($grandIva, 2) }}</td>
                <td class="text-right">{{ number_format($grandDiscount, 2) }}</td>
                <td class="text-right">{{ number_format($grandIeps, 2) }}</td>
                <td class="text-right">{{ number_format($grandRetIsr, 2) }}</td>
                <td class="text-right">{{ number_format($grandRetIva, 2) }}</td>
                <td class="text-right">{{ number_format($grandTotal, 2) }}</td>
                <td class="text-right" style="border: 2px solid #000;">{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    <script type="text/php">
        if ( isset($pdf) ) {
            $font = $fontMetrics->get_font("Arial", "bold");
            $pdf->page_text(680, 55, "PAGINA {PAGE_NUM} DE {PAGE_COUNT}", $font, 7.5, array(0,0,0));
        }
    </script>
</body>
</html>
