<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Anexo 2 - {{ $employee->primer_apellido }}</title>
    <style>
        @page { margin: 0.5cm 0.8cm; }
        body { font-family: Arial, sans-serif; font-size: 7.5pt; margin: 0; padding: 0; line-height: 1.15; color: #000; }
        
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
        .logo-qroo { height: 45px; }
        .logo-capa { height: 55px; }
        .logo-unidos { height: 40px; }
        
        .title-section { text-align: center; margin-bottom: 5px; }
        .title-main { font-size: 10pt; font-weight: bold; margin-bottom: 2px; }
        .title-sub { font-size: 8pt; font-weight: bold; margin-bottom: 2px; }
        
        .form-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; }
        .form-table td, .form-table th { border: 1px solid #000; padding: 2px 4px; vertical-align: middle; text-align: center; word-wrap: break-word; }
        .bg-gray { background-color: #ffffff; font-weight: bold; font-size: 7pt; }
        
        .label-row td { background-color: #ffffff; font-weight: normal; font-size: 7pt; height: 20px; }
        .value-row td { font-weight: bold; font-size: 7.5pt; height: 25px; }
        
        .section-header { font-weight: bold; font-size: 7.5pt; text-align: center; margin-bottom: 2px; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 3px; text-align: center; font-size: 7pt; }
        .items-table th { font-weight: bold; height: 30px; }
        
        .signatures { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .signatures td { width: 33.33%; text-align: center; vertical-align: top; padding: 0 5px; }
        .sig-box { margin-top: 35px; border-top: 1px solid #000; padding-top: 3px; display: inline-block; width: 90%; }
        .sig-label { font-weight: bold; font-size: 7.5pt; margin-bottom: 5px; }
        .sig-name { font-weight: bold; font-size: 7.5pt; text-transform: uppercase; }
        .sig-puesto { font-weight: bold; font-size: 7.5pt; text-transform: uppercase; }

        .footer-note { font-size: 7pt; text-align: justify; margin-top: 15px; border-top: 0px; }
        
        .no-border { border: none !important; }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }
        
        /* Specific widths for the first table based on image 2 */
        .col-ejercicio { width: 10%; }
        .col-trimestre { width: 10%; }
        .col-plaza { width: 15%; }
        .col-nivel { width: 10%; }
        .col-puesto { width: 15%; }
        .col-cargo { width: 15%; }
        .col-area { width: 15%; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 25%; text-align: left; vertical-align: middle;">
                @if($settings['logo_qroo'] ?? false)
                    <img src="{{ $settings['logo_qroo'] }}" class="logo-qroo" style="height: 40px; margin-right: 5px;">
                @endif
                @if($settings['logo_unidos'] ?? false)
                    <img src="{{ $settings['logo_unidos'] }}" class="logo-unidos" style="height: 35px; vertical-align: middle;">
                @endif
            </td>
            <td style="width: 50%; text-align: center; vertical-align: middle;">
                <div class="title-main">ANEXO 2</div>
                <div style="font-size: 7.5pt; font-weight: bold; margin-top: 5px;">OFICIO DE COMISION No. {{ $oficioNumber }}</div>
                <div style="font-size: 7pt; font-weight: bold; white-space: nowrap;">ORDEN DE MINISTRACION DE VIATICOS, PASAJES Y HOSPEDAJE (DEVENGADO)</div>
            </td>
            <td style="width: 25%; text-align: right; vertical-align: middle;">
                @if($settings['logo_capa_header'] ?? false)
                    <img src="{{ $settings['logo_capa_header'] }}" class="logo-capa" style="height: 45px;">
                @else
                    <img src="{{ $settings['logo_capa'] ?? '' }}" class="logo-capa" style="height: 45px;">
                @endif
            </td>
        </tr>
    </table>

    <!-- First Table: General Info -->
    <table class="form-table" style="margin-top: 5px;">
        <tr class="label-row">
            <td class="col-ejercicio">Ejercicio</td>
            <td class="col-trimestre">Trimestre</td>
            <td class="col-plaza">Tipo de Plaza y número de empleado</td>
            <td class="col-nivel">Clave o nivel del puesto</td>
            <td class="col-puesto">Denominación del puesto</td>
            <td class="col-cargo">Denominación del cargo</td>
            <td class="col-area">Área de adscripción</td>
        </tr>
        <tr class="value-row">
            <td>{{ $requirement->travelAllowance->exercise_year ?? date('Y') }}</td>
            <td>{{ $requirement->travelAllowance->quarter ?? 'I' }}</td>
            <td>{{ $employee->tipo_plaza ?? 'N/A' }}<br>{{ $employee->clave }}</td>
            <td>{{ $employee->nivel }}</td>
            <td>{{ $employee->puesto }}</td>
            <td>{{ $employee->cargo }}</td>
            <td>{{ $employee->departamento }}</td>
        </tr>
    </table>

    <!-- Second Table: Personal & Commission Details -->
    <table class="form-table">
        <tr class="label-row">
            <td colspan="3" style="width: 50%;">Nombre completo de la persona comisionada</td>
            <td rowspan="2" style="width: 50%;">Denominación del encargo o comisión</td>
        </tr>
        <tr class="label-row">
            <td style="width: 15%;">Nombre(s)</td>
            <td style="width: 17%;">Primer Apellido</td>
            <td style="width: 18%;">Segundo Apellido</td>
        </tr>
        <tr class="value-row">
            <td>{{ $employee->primer_nombre ?? $employee->nombre }}</td>
            <td>{{ $employee->primer_apellido }}</td>
            <td>{{ $employee->segundo_apellido }}</td>
            <td rowspan="4" style="vertical-align: top; text-align: justify; font-size: 7pt; font-weight: normal; padding: 5px; border-bottom: 1pt solid #000;">
                {{ $requirement->travelAllowance->justification }}
            </td>
        </tr>
        <tr class="label-row">
            <td style="width: 15%;">Banco:</td>
            <td colspan="2" style="text-align: left;">{{ $employee->banco ?? 'N/A' }}</td>
        </tr>
        <tr class="value-row">
            <td class="label-row">CLABE interbancaria:</td>
            <td colspan="2" style="text-align: left;">{{ $employee->clabe ?? 'N/A' }}</td>
        </tr>
        <tr class="label-row">
            <td style="width: 15%; border-bottom: 1pt solid #000;">R.F.C.:</td>
            <td colspan="2" style="text-align: left; border-bottom: 1pt solid #000;">{{ $employee->rfc }}</td>
        </tr>
    </table>

    <!-- Third Table: Location -->
    <table class="form-table">
        <tr class="label-row">
            <td colspan="3" style="width: 45%;">Lugar de adscripción de la persona comisionada</td>
            <td colspan="3" style="width: 45%;">Lugar del encargo o comisión</td>
            <td rowspan="2" style="width: 10%;">Tipo de viaje (Nacional / Internacional)</td>
        </tr>
        <tr class="label-row">
            <td>País</td>
            <td>Estado</td>
            <td>Ciudad</td>
            <td>País</td>
            <td>Estado</td>
            <td>Ciudad</td>
        </tr>
        <tr class="value-row">
            <td>{{ $requirement->travelAllowance->origin_country ?? 'México' }}</td>
            <td>{{ $requirement->travelAllowance->origin_state ?? 'QUINTANA ROO' }}</td>
            <td>{{ $requirement->travelAllowance->origin_city ?? 'JOSÉ MARÍA MORELOS' }}</td>
            <td>{{ $requirement->travelAllowance->destination_country ?? 'MEXICO' }}</td>
            <td>{{ $requirement->travelAllowance->destination_state ?? 'QUINTANA ROO' }}</td>
            <td>{{ $requirement->travelAllowance->destination_city }}</td>
            <td>NACIONAL</td>
        </tr>
    </table>

    <!-- Fourth Table: Transport & Duration -->
    <table class="form-table">
        <tr class="label-row">
            <td colspan="3" style="width: 30%;">Medio de Traslado a la Comisión</td>
            <td colspan="2" style="width: 15%;">Hospedaje/días de pernocta</td>
            <td style="width: 25%;">Tipo de Divisa y de Cambio</td>
            <td colspan="2" style="width: 30%;">Periodo del encargo o comisión</td>
        </tr>
        <tr class="label-row">
            <td style="width: 10%;">Tipo de Transporte</td>
            <td style="width: 10%;">Marca y modelo</td>
            <td style="width: 10%;">Placas</td>
            <td style="width: 7.5%;">¿SI/NO?</td>
            <td style="width: 7.5%;">DÍAS</td>
            <td style="font-weight: bold;">MONEDA NACIONAL</td>
            <td style="width: 15%;">Salida (día/mes/año)</td>
            <td style="width: 15%;">Regreso (día/mes/año)</td>
        </tr>
        <tr class="value-row">
            <td>{{ $requirement->travelAllowance->transport_type }}</td>
            <td>{{ $requirement->travelAllowance->vehicle->brand ?? 'N/A' }} {{ $requirement->travelAllowance->vehicle->model_year ?? '' }}</td>
            <td>{{ $requirement->travelAllowance->vehicle->plate_number ?? 'N/A' }}</td>
            <td>{{ $pernoctas > 0 ? 'SI' : 'NO' }}</td>
            <td>{{ $pernoctas }}</td>
            <td>PESO MEXICANO</td>
            <td>{{ \Carbon\Carbon::parse($requirement->travelAllowance->departure_date)->format('d/m/Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($requirement->travelAllowance->return_date)->format('d/m/Y') }}</td>
        </tr>
    </table>

    <!-- Fifth Table: Items Breakdown -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 10%;">Clave de partidas</th>
                <th style="width: 15%;">Denominación de la partida</th>
                <th style="width: 12%;">(Tarifa Diaria) Según Tabulador</th>
                <th style="width: 13%;">Número de días de la comisión/pernocta</th>
                <th style="width: 12%;">Gastos asignados para la comisión</th>
                <th style="width: 12%;">Gastos ejercidos en la comisión</th>
                <th style="width: 12%;">Gastos no erogados durante la comisión</th>
                <th style="width: 14%;">Importe asignado para la(s) partida(s) presupuestal(es)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumAsignado = 0;
            @endphp
            @foreach($requirement->items as $item)
                @php
                    $isHalfDay = ($formattedDays == '0.5');
                    $assigned = $isHalfDay ? ($limitPerDay / 2) : $item->amount;
                    
                    // Gastos ejercidos is invoice total but capped by assigned budget
                    $invoiceTotal = $item->invoice_total ?? 0;
                    $exercised = min($invoiceTotal, $assigned);
                    
                    // Fixed column requirements:
                    // Col 7 (Gastos no erogados) is always 0
                    // Col 8 (Importe asignado...) repeats Assigned Budget (Col 5)
                    
                    $sumAsignado += $assigned;
                @endphp
                <tr>
                    <td>{{ $item->partida->codigo }}</td>
                    <td class="text-left">{{ $item->partida->nombre }}</td>
                    <td>$ {{ number_format($limitPerDay, 2) }}</td>
                    <td>{{ $formattedDays }}</td>
                    <td>$ {{ number_format($assigned, 2) }}</td>
                    <td>$ {{ number_format($exercised, 2) }}</td>
                    <td>$ 0.00</td>
                    <td>$ {{ number_format($assigned, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Sixth Table: Budget Code -->
    <table class="form-table" style="margin-top: 5px;">
        <tr class="label-row">
            <td style="width: 30%;">Clave presupuestal asignada para la Unidad Responsable</td>
            <td style="text-align: center; font-weight: bold; font-size: 8pt;">{{ $budgetCode }}</td>
        </tr>
    </table>

    <!-- Seventh Table: Report Details -->
    <table class="form-table" style="margin-top: 5px;">
        <tr class="label-row">
            <td style="width: 25%;">Fecha de entrega del Informe de la comisión</td>
            <td style="width: 75%; text-align: left;">Hipervínculo al informe de la comisión</td>
        </tr>
        <tr class="value-row">
            <td style="font-weight: bold;">{{ $reportDate ? \Carbon\Carbon::parse($reportDate)->format('d/m/Y') : '' }}</td>
            <td style="text-align: left; font-size: 6.5pt; font-weight: normal; overflow-wrap: break-word;">
                {{ $reportLink ?? '' }}
            </td>
        </tr>
    </table>

    <!-- Signatures -->
    <div style="text-align: center; margin-top: 10px;">
        <table class="signatures">
            <tr>
                <td class="sig-label">LA PERSONA COMISIONADA</td>
                <td class="sig-label">TITULAR SUPERIOR</td>
                <td class="sig-label">TITULAR AUTORIZADOR</td>
            </tr>
            <tr>
                <td>
                    <div class="sig-box">
                        <div class="sig-name">C. {{ $employee->nombre }} {{ $employee->primer_apellido }} {{ $employee->segundo_apellido }}</div>
                        <div class="sig-puesto">{{ $employee->cargo }}</div>
                    </div>
                </td>
                <td>
                    <div class="sig-box">
                        <div class="sig-name">C. {{ $superior ? $superior->nombre . ' ' . $superior->primer_apellido . ' ' . $superior->segundo_apellido : ($employee->jefe_inmediato ?? '__________________________') }}</div>
                        <div class="sig-puesto">{{ $superior->cargo ?? 'JEFE INMEDIATO' }}</div>
                    </div>
                </td>
                <td>
                    <div class="sig-box">
                        <div class="sig-name">C. {{ $autorizador ? $autorizador->nombre . ' ' . $autorizador->primer_apellido . ' ' . $autorizador->segundo_apellido : '__________________________' }}</div>
                        <div class="sig-puesto">{{ $autorizador->puesto ?? 'GERENTE' }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-note">
        Me comprometo a comprobar, el importe asignado en concepto de viáticos y/o pasajes, por el monto otorgado y con la documentación correspondiente, y en su caso reintegrar los importes no devengados, dentro de un periodo máximo de 5 días al término de la comisión, en el evento de omitir esta obligación, autorizo me sea descontado el importe correspondiente de mi sueldo en la quincena que aplique.
    </div>

</body>
</html>
