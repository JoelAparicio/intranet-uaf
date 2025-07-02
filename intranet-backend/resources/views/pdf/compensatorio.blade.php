<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Uso de Tiempo Compensatorio</title>
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
            width: 7in;
            margin-left: 0.3in;
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

        /* Estilos para el párrafo principal */
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

        /* Observación */
        .observation {
            margin: 20px 0;
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

        /* Firmas */
        .signatures {
            margin-top: 50px;
        }
        .signature-row {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .signature-cell {
            display: table-cell;
            width: 45%;
            vertical-align: bottom;
        }
        .signature-space {
            display: table-cell;
            width: 10%;
        }
        .signature-line {
            border-top: 2px solid #000;
            width: 100%;
            text-align: center;
            position: relative;
            padding-top: 5px;
        }
        .signature-line p {
            margin-top: 5px;
            font-weight: bold;
            font-size: 10pt;
        }
        .signature-img {
            max-height: 70px;
            position: absolute;
            top: -65px;
            left: 50%;
            transform: translateX(-50%);
        }
        .fecha-firma {
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 10pt;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Encabezado con logo -->
    <div class="header">
        <img class="logo" src="{{ public_path('images/Image_001.png') }}" alt="Gobierno Nacional Con Paso Firme">
        <p><strong>MINISTERIO DE LA PRESIDENCIA</strong></p>
        <p>Oficina Institucional de Recursos Humanos</p>
    </div>

    <h1 class="title">SOLICITUD DE USO DE TIEMPO COMPENSATORIO</h1>

    <!-- Contenido principal como párrafo continuo -->
    <div class="content">
        <p class="main-paragraph">
            Por este medio yo <span class="field-content">{{ $nombre ?? '' }}</span> Servidor Público (a) con cédula N° <span class="field-content">{{ $cedula ?? '' }}</span> que actualmente ocupo el puesto de <span class="field-content">{{ $puesto ?? '' }}</span> con la posición N° <span class="field-content">{{ $posicion ?? '' }}</span> en la Unidad Administrativa <span class="field-content">{{ $unidad ?? '' }}</span>, solicito el uso de <span class="field-content">{{ $cantidad_tiempo ?? '' }}</span> (horas / días) del tiempo compensatorio que tengo acumulado en virtud de haber laborado en jornadas extraordinarias. Dicho tiempo lo haré efectivo el <span class="field-content">{{ $dia_inicio ?? '' }}</span> de <span class="field-content">{{ $mes_inicio ?? '' }}</span> de <span class="field-content">{{ $anio_inicio ?? '' }}</span> desde las <span class="field-content">{{ $hora_inicio ?? '' }}</span> hasta las <span class="field-content">{{ $hora_fin ?? '' }}</span> del <span class="field-content">{{ $dia_fin ?? '' }}</span> de <span class="field-content">{{ $mes_fin ?? '' }}</span> de <span class="field-content">{{ $anio_fin ?? '' }}</span>.
        </p>
    </div>

    <!-- Observación como párrafo continuo -->
    <div class="observation">
        <p><strong>Observación:</strong></p>
        <div class="paragraph-content">
            {{ $observacion ?? '' }}
        </div>
    </div>

    <!-- Sección de firmas -->
    <div class="signatures">
        <div class="signature-row">
            <div class="signature-cell">
                <div class="signature-line" style="position: relative;">
                    @if(isset($firma_path) && $firma_path)
                        <img src="{{ public_path('storage/' . $firma_path) }}" alt="Firma" class="signature-img">
                    @endif
                    <p>Servidor Público</p>
                </div>
            </div>
            <div class="signature-space"></div>
            <div class="signature-cell">
                <div class="signature-line" style="position: relative;">
                    @if(isset($fecha_firma1))
                        <p class="fecha-firma">{{ $fecha_firma1 }}</p>
                    @endif
                    <p>Fecha</p>
                </div>
            </div>
        </div>

        <div class="signature-row">
            <div class="signature-cell">
                <div class="signature-line" style="position: relative;">
                    @if(isset($firma_jefe_path) && $firma_jefe_path)
                        <img src="{{ public_path('storage/' . $firma_jefe_path) }}" alt="Firma Jefe" class="signature-img">
                    @endif
                    <p>Jefe Inmediato</p>
                </div>
            </div>
            <div class="signature-space"></div>
            <div class="signature-cell">
                <div class="signature-line" style="position: relative;">
                    @if(isset($fecha_firma2))
                        <p class="fecha-firma">{{ $fecha_firma2 }}</p>
                    @endif
                    <p>Fecha</p>
                </div>
            </div>
        </div>

        <div class="signature-row">
            <div class="signature-cell">
                <div class="signature-line" style="position: relative;">
                    @if(isset($firma_rrhh) && $firma_rrhh)
                        <img src="{{ public_path('storage/' . $firma_rrhh) }}" alt="Firma RRHH" class="signature-img">
                    @endif
                    <p>Enlace de Recursos Humanos</p>
                </div>
            </div>
            <div class="signature-space"></div>
            <div class="signature-cell">
                <div class="signature-line" style="position: relative;">
                    @if(isset($fecha_firma3))
                        <p class="fecha-firma">{{ $fecha_firma3 }}</p>
                    @endif
                    <p>Fecha</p>
                </div>
            </div>
        </div>

        <div class="signature-row">
            <div class="signature-cell">
                <div class="signature-line">
                    <p>Jefe de la O.I.R.H</p>
                </div>
            </div>
            <div class="signature-space"></div>
            <div class="signature-cell">
                <div class="signature-line">
                    <p>Fecha</p>
                </div>
            </div>
        </div>
    </div>

</div>
</body>
</html>
