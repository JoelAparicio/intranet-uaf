<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario de Horas Extraordinarias</title>
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
            width: 7.5in;
            margin: 0 auto;
            padding: 0.3in 0.5in 0.75in 0.5in;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .logo {
            max-width: 280px;
            height: auto;
            margin: 0 auto 10px;
            display: block;
        }
        .title {
            font-weight: bold;
            font-size: 14pt;
            text-align: center;
            margin: 20px 0 25px 0;
            text-transform: uppercase;
        }
        .content {
            margin-bottom: 12px;
        }
        /* Campos en línea */
        .field-line {
            display: inline-block;
            border-bottom: 1px solid #000;
            margin: 0 3px;
            padding: 0 3px;
        }
        .field-name {
            min-width: 280px;
        }
        .field-cedula {
            min-width: 150px;
        }
        .field-position {
            min-width: 150px;
        }
        .field-cargo {
            min-width: 200px;
        }
        .field-unidad {
            min-width: 350px;
        }
        .field-fecha {
            min-width: 300px;
        }
        .field-hora {
            min-width: 70px;
        }
        .field-tiempo {
            min-width: 100px;
        }

        /* NUEVO: Estilos para párrafos continuos */
        .paragraph-area {
            margin-bottom: 20px;
        }
        .paragraph-area label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
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
        .paragraph-content.large {
            min-height: 66px; /* Espacio para 3 líneas si es necesario */
        }

        /* Estilos para campos de datos como párrafo continuo */
        .data-field {
            margin-bottom: 15px;
            font-weight: bold;
            line-height: 24px;
        }
        .data-field .field-content {
            text-decoration: underline;
            font-weight: normal;
            display: inline;
        }

        /* Datos en línea justificados */
        .datos-principales {
            text-align: justify;
            margin-bottom: 20px;
        }
        /* Párrafos con etiquetas */
        .form-field {
            margin-bottom: 15px;
            font-weight: bold;
        }
        /* Observación */
        .observation {
            margin: 20px 0;
        }
        /* Firmas */
        .signatures {
            margin-top: 40px;
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

    <h1 class="title">FORMULARIO DE HORAS EXTRAORDINARIAS</h1>

    <!-- Datos como UN SOLO párrafo continuo -->
    <div class="datos-principales">
        <p class="data-field">
            Nombre: <span class="field-content">{{ $nombre ?? '' }}</span> Cédula: <span class="field-content">{{ $cedula ?? '' }}</span> Posición: <span class="field-content">{{ $posicion ?? '' }}</span> Cargo: <span class="field-content">{{ $puesto ?? '' }}</span> Unidad Administrativa en que labora: <span class="field-content">{{ $unidad ?? '' }}</span> Fecha en que realizó el trabajo: <span class="field-content">{{ $fecha_trabajo ?? '' }}</span> Hora de Inicio: <span class="field-content">{{ $hora_inicio ?? '' }}</span> Hora de Culminación: <span class="field-content">{{ $hora_fin ?? '' }}</span> Horas/Minutos Laborados: <span class="field-content">{{ $tiempo_laborado ?? '' }}</span>
        </p>
    </div>

    <!-- Trabajo realizado como párrafo continuo -->
    <div class="paragraph-area">
        <label>Trabajo Realizado:</label>
        <div class="paragraph-content">
            {{ $trabajo_realizado ?? '' }}
        </div>
    </div>

    <!-- Justificación como párrafo continuo -->
    <div class="paragraph-area">
        <label>Justificación de las causas que le impidieron realizar las labores antes descritas durante la<br>
            jornada regular de trabajo:</label>
        <div class="paragraph-content">
            {{ $justificacion ?? '' }}
        </div>
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
