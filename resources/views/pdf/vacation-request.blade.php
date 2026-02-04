<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Vacaciones</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt; /* Increased as requested +2pt */
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        .page-container {
            /* Ensures container doesn't add extra height unexpectedly */
            width: 100%;
        }
        .half-page {
            height: 47%; /* Reduced slightly to accommodate extra spacing */
            position: relative;
            padding-top: 0;
        }
        .separator {
            border-bottom: 2px dashed #999;
            margin: 1% 0; /* Relative margin */
            height: 1px;
            width: 100%;
        }
        .header {
            text-align: right;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .addressee {
            margin-bottom: 15px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .content {
            text-align: justify;
            margin-bottom: 15px; /* Reduced from 20px */
        }
        .signatures {
            display: table;
            width: 100%;
            margin-top: 70px; /* Increased from 45px */
        }
        .signature-block {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-line {
            border-top: 1px solid black;
            width: 85%;
            margin: 0 auto;
            margin-top: 70px; /* Increased from 40px */
            padding-top: 5px;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 10pt;
        }
        .signature-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    @php
        use Carbon\Carbon;
        Carbon::setLocale('es');
        $fechaActual = Carbon::now()->isoFormat('D [DE] MMMM [DEL] YYYY');
        
        // 1. Datos del Gerente (Para el Destinatario)
        if (isset($gerente) && $gerente) {
            $nombreDestinatario = $gerente->nombre;
            $cargoDestinatario = $gerente->puesto;
        } else {
            // Fallback si no hay gerente: Usar valores genéricos
            $nombreDestinatario = "A QUIEN CORRESPONDA";
            $cargoDestinatario = "GERENCIA GENERAL";
        }

        // 2. Datos del Jefe Inmediato (Para la firma de Autoriza)
        $jefeInmediatoStr = $empleado->jefe_inmediato ?? "SIN ASIGNAR - JEFE INMEDIATO";
        $parts = explode('-', $jefeInmediatoStr);
        $nombreAutoriza = trim($parts[0]);
        $cargoAutoriza = isset($parts[1]) ? trim($parts[1]) : "JEFE INMEDIATO";
    @endphp

    @foreach($subSolicitudes as $index => $sub)
        <div class="page-container {{ !$loop->first ? 'page-break' : '' }}">
            @for ($i = 0; $i < 2; $i++)
                <div class="half-page">
                    <div class="header">
                        FECHA: {{ mb_strtoupper($fechaActual) }}<br>
                        ASUNTO: VACACIONES
                    </div>

                    <div class="addressee">
                        C. {{ mb_strtoupper($nombreDestinatario) }}<br>
                        {{ mb_strtoupper($cargoDestinatario) }}<br>
                        PRESENTE
                    </div>

                    <div class="content">
                        <p>
                            Por este medio y de la manera más atenta, le solicito <strong>{{ $sub['dias_solicitados'] }}</strong> {{ $sub['dias_solicitados'] == 1 ? 'día' : 'días' }} de descanso
                            correspondiente a <strong>VACACIONES {{ $sub['tipo'] }}</strong> 
                            @if(isset($sub['cuatrimestre']))
                                del <strong>{{ $sub['cuatrimestre'] == 1 ? '1er' : ($sub['cuatrimestre'] == 2 ? '2do' : '3er') }} Cuatrimestre del {{ $sub['anio'] }}</strong>
                            @else
                                del <strong>{{ $sub['anio'] }}</strong>
                            @endif
                            siendo {{ $sub['dias_solicitados'] == 1 ? 'efectivo el día' : 'efectivos los días' }} 
                            @if($sub['dias_solicitados'] == 1)
                                <strong>{{ mb_strtoupper($sub['fecha_inicio']) }}</strong>, por 
                            @else
                                <strong>{{ mb_strtoupper($sub['fecha_inicio']) }} AL {{ mb_strtoupper($sub['fecha_fin']) }}</strong>, por 
                            @endif
                            motivos personales, para incorporarme a laborar el día <strong>{{ mb_strtoupper($sub['fecha_retorno']) }}</strong>, 
                            agradeciendo su atención a la presente, hago oportuno este espacio para enviarle un cordial saludo.
                        </p>
                    </div>

                    <div class="signatures">
                        <div class="signature-block">
                            <div class="signature-title">SOLICITA</div>
                            <div class="signature-line">
                                {{ mb_strtoupper($empleado->nombre) }}<br>
                                {{ mb_strtoupper($empleado->puesto) }}
                            </div>
                        </div>
                        <div class="signature-block">
                            <div class="signature-title">AUTORIZA</div>
                            <div class="signature-line">
                                {{ mb_strtoupper($nombreAutoriza) }}<br>
                                {{ mb_strtoupper($cargoAutoriza) }}
                            </div>
                        </div>
                    </div>
                </div>

                @if($i == 0)
                    <div class="separator"></div>
                @endif
            @endfor
        </div>
    @endforeach
</body>
</html>
