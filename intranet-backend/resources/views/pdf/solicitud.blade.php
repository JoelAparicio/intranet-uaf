<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Permiso</title>
    <style>
        @page {
            size: letter;
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            margin: 0;
            padding: 0;
            font-size: 11pt;
        }
        .container {
            width: 7in; /* un poco menos del total */
            margin-left: 0.3in; /* empuja un poco hacia la derecha */
            margin-right: auto;
            padding: 0.5in;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .logo {
            max-width: 300px;
            height: auto;
            margin: 0 auto 10px;
            display: block;
        }
        .title {
            font-weight: bold;
            font-size: 14pt;
            text-align: center;
            margin: 15px 0;
            text-transform: uppercase;
        }
        .content {
            text-align: justify;
            line-height: 1.5;
        }

        /* Estilos para párrafos principales */
        .main-paragraph {
            text-align: justify;
            line-height: 24px;
            margin-bottom: 20px;
            font-weight: normal;
        }

        /* Campos subrayados y en negrita */
        .field-content {
            text-decoration: underline;
            font-weight: bold;
            display: inline;
        }

        .field-line {
            display: inline-block;
            min-width: 200px;
            border-bottom: 1px solid #000;
            margin: 0 3px;
        }

        /* Sección de checkboxes */
        .checkbox-section {
            margin: 10px 0;
        }
        .checkbox-title {
            font-weight: bold;
            margin: 5px 0 2px 0;
            text-align: center;
        }

        /* Observación */
        .observation {
            margin: 15px 0;
        }
        .observation-line {
            display: block;
            width: 100%;
            border-bottom: 1px solid #000;
            min-height: 15px;
            margin: 5px 0;
        }
        .paragraph-content {
            min-height: 44px; /* Espacio para 2 líneas */
            padding: 4px 0;
            line-height: 18px;
            text-align: justify;
            word-wrap: break-word;
            overflow-wrap: break-word;
            text-decoration: underline;
        }

        /* Fechas y cálculo de tiempo */
        .dates {
            margin: 15px 0;
        }
        .time-summary {
            margin: 15px 0;
        }

        /* Firmas */
        .signatures {
            margin-top: 55px;
        }
        .signature {
            width: 100%;
            margin-bottom: 28px;
        }
        .signature-line {
            width: 45%;
            border-top: 2px solid #000;
            display: inline-block;
            margin: 0 10px;
            text-align: center;
        }
        .signature-line p {
            margin-top: 5px;
            font-weight: bold;
            font-size: 9pt;
        }
        /* Estilo mínimo para visualizar la cajita del checkbox */
        .checkbox {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 1px solid #000;
            margin-right: 5px;
            cursor: pointer;
            text-align: center;
            line-height: 14px;
        }
    </style>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Manejo de checkboxes: solo se permite marcar una opción por grupo
            const checkboxes = document.querySelectorAll('.checkbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('click', function() {
                    if (this.innerHTML === '✓') {
                        // Quitar marca
                        this.innerHTML = '';
                    } else {
                        // Según el grupo, desmarca las casillas del otro grupo
                        if (this.classList.contains('descontable')) {
                            document.querySelectorAll('.no-descontable').forEach(cb => cb.innerHTML = '');
                        } else if (this.classList.contains('no-descontable')) {
                            document.querySelectorAll('.descontable').forEach(cb => cb.innerHTML = '');
                        }
                        this.innerHTML = '✓';
                    }
                });
            });

            // Calcular tiempo entre fechas (opcional)
            function calcularTiempo() {
                // Lógica para calcular el tiempo entre fechas
                // (se mantiene como referencia pero no es necesario implementarlo aquí)
            }
        });
    </script>
</head>
<body>
<div class="container">
    <!-- Encabezado con logo -->
    <div class="header">
        <img class="logo" src="{{ public_path('images/Image_001.png') }}" alt="Gobierno Nacional Con Paso Firme">
        <p><strong>MINISTERIO DE LA PRESIDENCIA</strong></p>
        <p>Oficina Institucional de Recursos Humanos</p>
    </div>

    <h1 class="title">SOLICITUD DE PERMISO</h1>

    <!-- Contenido principal como párrafo continuo -->
    <div class="content">
        <p class="main-paragraph">
            Por este medio yo <span class="field-content">{{ $nombre ?? '' }}</span> Servidor Público (a) con cédula N° <span class="field-content">{{ $cedula ?? '' }}</span> que actualmente desempeño el puesto de <span class="field-content">{{ $puesto ?? '' }}</span> con la posición N° <span class="field-content">{{ $posicion ?? '' }}</span> en la Unidad Administrativa <span class="field-content">{{ $unidad ?? '' }}</span>, solicito permiso para ausentarme de mi puesto por motivo de:
        </p>
    </div>

    <!-- Sección de checkboxes -->
    <div class="checkbox-section">
        <p class="checkbox-title"><strong>Permisos Descontables</strong></p>
        <div style="line-height: 1.3;">
            <p><span class="checkbox descontable">@if($motivo === 'Enfermedad') X @endif</span> ENFERMEDAD</p>
            <p><span class="checkbox descontable">@if($motivo === 'Duelo') X @endif</span> DUELO</p>
            <p><span class="checkbox descontable">@if($motivo === 'Matrimonio') X @endif</span> MATRIMONIO</p>
            <p><span class="checkbox descontable">@if($motivo === 'Nacimiento de hijos') X @endif</span> NACIMIENTO DE HIJOS</p>
            <p><span class="checkbox descontable">@if($motivo === 'Enfermedades de parientes cercanos') X @endif</span> ENFERMEDADES DE PARIENTES CERCANOS</p>
            <p><span class="checkbox descontable">@if($motivo === 'Eventos academicos puntuales') X @endif</span> EVENTOS ACADÉMICOS PUNTUALES</p>
            <p><span class="checkbox descontable">@if($motivo === 'Permisos personales') X @endif</span> PERMISOS PERSONALES</p>
        </div>

        <p class="checkbox-title"><strong>Permisos no Descontables</strong></p>
        <div style="line-height: 1.3;">
            <p><span class="checkbox no-descontable">@if($motivo === 'Mision oficial') X @endif</span> MISIÓN OFICIAL</p>
            <p><span class="checkbox no-descontable">@if($motivo === 'Seminarios') X @endif</span> SEMINARIOS</p>
            <p>
                <span class="checkbox no-descontable">@if($motivo === 'Otros') X @endif</span> OTROS, ESPECIFIQUE:
                <span class="field-content">{{ $otro_motivo ?? '' }}</span>
            </p>
        </div>
    </div>

    <!-- Observación como párrafo continuo -->
    <div class="observation">
        <p><strong>Observación:</strong></p>
        <div class="paragraph-content">
            {{ $observacion ?? '' }}
        </div>
    </div>

    <!-- Fechas y Cálculo de Tiempo -->
    <div class="dates">
        <p>Desde la (s) <span class="field-line" style="min-width: 60px;">{{ $hora_inicio ?? '' }}</span> horas del día <span class="field-line" style="min-width: 60px;">{{ $dia_inicio ?? '' }}</span> del mes de <span class="field-line" style="min-width: 150px;">{{ $mes_inicio ?? '' }}</span> de <span class="field-line" style="min-width: 80px;">{{ $anio_inicio ?? '' }}</span>.</p>
        <p>Hasta la (s) <span class="field-line" style="min-width: 60px;">{{ $hora_fin ?? '' }}</span> horas del día <span class="field-line" style="min-width: 60px;">{{ $dia_fin ?? '' }}</span> del mes de <span class="field-line" style="min-width: 150px;">{{ $mes_fin ?? '' }}</span> de <span class="field-line" style="min-width: 80px;">{{ $anio_fin ?? '' }}</span>.</p>
    </div>

    <div class="time-summary">
        <p>Tiempo Utilizado: <span class="field-line" style="min-width: 80px;">{{ $dias ?? '' }}</span> día (s)
            <span class="field-line" style="min-width: 80px;">{{ $horas ?? '' }}</span> hora (s)
            <span class="field-line" style="min-width: 80px;">{{ $minutos ?? '' }}</span> minuto (s)
        </p>
    </div>

    <!-- Firmas -->
    <div class="signatures">
        <div class="signature">
            <div class="signature-line" style="position: relative;">
                @if(isset($firma_path) && $firma_path)
                    <img src="{{ public_path('storage/' . $firma_path) }}" alt="Firma"
                         style="max-height: 70px; position: absolute; top: -62px; left: 55%; transform: translateX(-50%);">
                @endif
                <p>Servidor Público</p>
            </div>
            <div class="signature-line" style="position: relative;">
                @if(isset($fecha_firma1))
                    <p style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); font-size: 10pt;">
                        {{ $fecha_firma1 }}
                    </p>
                @endif
                <p>Fecha</p>
            </div>
        </div>
        <div class="signature">
            <div class="signature-line" style="position: relative;">
                @if(isset($firma_jefe_path) && $firma_jefe_path)
                    <img src="{{ public_path('storage/' . $firma_jefe_path) }}" alt="Firma Jefe"
                         style="max-height: 70px; position: absolute; top: -58px; left: 55%; transform: translateX(-50%);">
                @endif
                <p>Jefe Inmediato</p>
            </div>
            <div class="signature-line" style="position: relative;">
                @if(isset($fecha_firma2))
                    <p style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); font-size: 10pt;">
                        {{ $fecha_firma2 }}
                    </p>
                @endif
                <p>Fecha</p>
            </div>
        </div>
        <div class="signature">
            <div class="signature-line" style="position: relative;">
                @if(isset($firma_rrhh) && $firma_rrhh)
                    <img src="{{ public_path('storage/' . $firma_rrhh) }}" alt="Firma RRHH"
                         style="max-height: 70px; position: absolute; top: -50px; left: 55%; transform: translateX(-50%);">
                @endif
                <p>Enlace de Recursos Humanos</p>
            </div>
            <div class="signature-line" style="position: relative;">
                @if(isset($fecha_firma3))
                    <p style="position: absolute; top: -25px; left: 50%; transform: translateX(-50%); font-size: 10pt;">
                        {{ $fecha_firma3 }}
                    </p>
                @endif
                <p>Fecha</p>
            </div>
        </div>
        <div class="signature">
            <div class="signature-line">
                <p>Jefe de la O.I.R.H</p>
            </div>
            <div class="signature-line">
                <p>Fecha</p>
            </div>
        </div>
    </div>

</div>
</body>
</html>
