<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Oficio de Comisión</title>
    <style>
        @page {
            margin: 1.5cm 2cm 1.5cm 2cm;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000;
        }
        body {
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .header {
            width: 100%;
            position: relative;
            height: 100px;
            margin-bottom: 10px;
        }
        .logo-left {
            position: absolute;
            left: 0;
            top: 0;
            width: 80px;
        }
        .logo-right {
            position: absolute;
            right: 0;
            top: 0;
            width: 200px;
            text-align: right;
        }
        .header-info {
            text-align: right;
            margin-top: 110px;
            font-weight: bold;
            font-size: 10pt;
        }
        .header-info div {
            margin-bottom: 2px;
        }

        .section-center {
            text-align: center;
            margin-top: 60px;
            margin-bottom: 30px;
        }
        .leyenda {
            font-size: 9pt;
            font-style: italic;
            margin-bottom: 15px;
            color: #444;
        }
        .fecha {
            font-weight: bold;
        }

        .destinatario {
            margin-bottom: 40px;
            font-weight: bold;
            line-height: 1.2;
        }
        .destinatario p {
            margin: 0;
        }
        .presente {
            margin-top: 10px !important;
        }

        .cuerpo {
            text-align: justify;
            margin-bottom: 25px;
            text-indent: 50px;
            line-height: 1.6;
        }
        
        .despedida {
            text-align: justify;
            margin-bottom: 60px;
            text-indent: 50px;
        }

        .firma-container {
            text-align: center;
            margin-top: 40px;
        }
        .firma-texto {
            font-weight: bold;
            margin-bottom: 60px;
        }
        .firma-nombre {
            font-weight: bold;
            margin: 0;
        }
        .firma-cargo {
            font-weight: bold;
            margin: 0;
            font-size: 10pt;
        }

        .footer-ccp {
            margin-top: 50px;
            font-size: 8pt;
            line-height: 1.2;
        }
        .footer-ccp p {
            margin: 0;
        }

        .uppercase {
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    @php
        use Carbon\Carbon;
        Carbon::setLocale('es');
        $fechaActual = Carbon::now();
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $mes = strtoupper($meses[$fechaActual->month - 1]);
        
        $start = Carbon::parse($commission->start_date);
        $end = Carbon::parse($commission->end_date);
        
        if($start->isSameDay($end)){
            $fechas_comision = "EL DIA " . $start->format('d') . " DE " . strtoupper($meses[$start->month - 1]);
        } elseif($start->month == $end->month) {
            $fechas_comision = "LOS DIAS DEL " . $start->format('d') . " AL " . $end->format('d') . " DE " . strtoupper($meses[$start->month - 1]);
        } else {
            $fechas_comision = "LOS DIAS DEL " . $start->format('d') . " DE " . strtoupper($meses[$start->month - 1]) . " Y " . $end->format('d') . " DE " . strtoupper($meses[$end->month - 1]);
        }
    @endphp

    <div class="header">
        <div class="logo-left">
            @if(isset($settings['logo_qroo']))
                <img src="{{ $settings['logo_qroo'] }}" width="80">
            @endif
        </div>
        <div class="logo-right">
            @if(isset($settings['logo_unidos']))
                <img src="{{ $settings['logo_unidos'] }}" width="200">
            @endif
        </div>
        
        <div class="header-info uppercase">
            <div>Oficio: CAPA/G/AT/{{ str_pad($commission->oficio_number, 3, '0', STR_PAD_LEFT) }}/{{ $start->format('Y') }}</div>
            <div>ASUNTO: COMISION</div>
        </div>
    </div>

    <div class="section-center">
        <div class="leyenda">
            "2026, Año del 40 Aniversario de la Creación del Himno del Estado Libre y Soberano de Quintana Roo"
        </div>
        <div class="fecha uppercase">
            JOSE MA. MORELOS, QUINTANA ROO A {{ $fechaActual->format('d') }} DE {{ $mes }} DE {{ $fechaActual->format('Y') }}
        </div>
    </div>

    <div class="destinatario uppercase">
        <p>C. {{ $commission->empleado->nombre_completo }}</p>
        <p>{{ $commission->empleado->cargo ?: 'CARGO NO DEFINIDO' }}</p>
        <p class="presente">PRESENTE</p>
    </div>

    <div class="cuerpo uppercase">
        POR MEDIO DE LA PRESENTE ME PERMITO COMISIONARLO {{ $fechas_comision }} DEL AÑO EN CURSO, {{ strtoupper($commission->reason) }}
        @if($commission->vehicle)
            PARA TAL EFECTO, HAGO DE SU CONOCIMIENTO QUE SE LE HA ASIGNADO EL VEHÍCULO {{ $commission->vehicle->brand }} {{ $commission->vehicle->vehicle_type }} CON PLACAS {{ $commission->vehicle->plate_number }}.
        @endif
    </div>

    <div class="despedida uppercase">
        SIN OTRO PARTICULAR, ME ES GRATO HACER PROPICIA LA OCASIÓN PARA ENVIARLE UN CORDIAL SALUDO.
    </div>

    <div class="firma-container">
        <div class="firma-texto">
            ATENTAMENTE
        </div>
        
        <div class="firma-nombre uppercase">
            @if($gerente)
                {{ $gerente->puesto && str_contains(strtolower($gerente->puesto), 'ing.') ? '' : 'ING. ' }}{{ $gerente->nombre_completo }}
            @else
                INGRESE NOMBRE DEL GERENTE
            @endif
        </div>
        <div class="firma-cargo uppercase">
            GERENTE DEL ORGANISMO OPERADOR
        </div>
    </div>

    <div class="footer-ccp">
        <p>C.C.P. MINUTARIO</p>
        <p>LDHD*ERHK*norma*</p>
    </div>

</body>
</html>
