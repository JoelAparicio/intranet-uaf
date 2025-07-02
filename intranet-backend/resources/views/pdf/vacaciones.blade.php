<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Uso de Vacaciones</title>
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
            font-size: 10pt;
            line-height: 1.2;
        }
        .container {
            width: 7.5in;
            margin-left: 0.25in;
            margin-right: auto;
            padding: 0.25in;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .logo {
            max-width: 250px;
            height: auto;
            margin: 0 auto 5px;
            display: block;
        }
        .header p {
            margin: 1px 0;
            font-size: 10pt;
        }
        .title {
            font-weight: bold;
            font-size: 12pt;
            text-align: center;
            margin: 10px 0 12px 0;
            text-transform: uppercase;
        }
        .content {
            line-height: 1.3;
            text-align: justify;
            font-size: 10pt;
        }
        .content p {
            margin-bottom: 8px;
        }

        /* Estilos para el párrafo principal */
        .main-paragraph {
            text-align: justify;
            line-height: 24px;
            margin-bottom: 20px;
            font-weight: normal; /* Cambiado a normal */
        }

        /* Campos subrayados y en negrita */
        .field-content {
            text-decoration: underline;
            font-weight: bold; /* Agregado bold para los datos */
            display: inline;
        }

        /* Campos subrayados inline */
        .field-line {
            display: inline-block;
            border-bottom: 1px solid #000;
            margin: 0 2px;
            padding: 0 2px;
            text-align: center;
            vertical-align: bottom;
        }
        .field-day {
            min-width: 35px;
        }
        .field-month {
            min-width: 90px;
        }
        .field-year {
            min-width: 55px;
        }
        .field-name {
            min-width: 220px;
            font-weight: bold;
        }
        .field-cedula {
            min-width: 100px;
        }
        .field-position {
            min-width: 70px;
        }
        .field-salary {
            min-width: 70px;
        }
        .field-days {
            min-width: 25px;
        }
        .field-resuelto-no {
            min-width: 50px;
        }
        .field-resuelto-date {
            min-width: 160px;
        }

        /* Destinatario */
        .recipient {
            margin: 12px 0;
        }
        .recipient p {
            margin: 1px 0;
            font-size: 10pt;
        }
        .recipient-name {
            font-weight: bold;
        }

        /* Sección de firmas */
        .signatures {
            margin-top: 35px;
            margin-bottom: 15px;
        }
        .signature-row {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .signature-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 0 5px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 90%;
            margin-top: 45px;
            position: relative;
            margin-left: auto;
            margin-right: auto;
        }
        .signature-img {
            max-height: 40px;
            max-width: 120px;
            position: absolute;
            top: -45px;
            left: 50%;
            transform: translateX(-50%);
        }
        .signature-label {
            margin-top: 3px;
            font-weight: bold;
            font-size: 9pt;
        }

        /* Sección OIRH */
        .oirh-section {
            border: 1.5px solid #000;
            padding: 10px;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .oirh-header {
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
            font-size: 10pt;
        }
        .vacation-title {
            text-align: center;
            margin-bottom: 10px;
            font-size: 10pt;
        }
        .vacation-details p {
            margin-bottom: 5px;
            line-height: 1.2;
            font-size: 9pt;
        }
        .observation-section {
            margin: 12px 0 15px 0;
        }
        .observation-section p {
            font-size: 9pt;
        }
        .observation-field {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 350px;
            margin-left: 5px;
        }

        /* Firmas finales */
        .final-signatures {
            margin-top: 20px;
        }
        .final-row {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .final-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 0 5px;
        }
        .final-line {
            border-top: 1px solid #000;
            width: 90%;
            height: 35px;
            margin: 0 auto;
        }
        .final-label {
            margin-top: 3px;
            font-weight: bold;
            font-size: 8pt;
            line-height: 1.1;
        }

        /* Ajuste para "Atentamente" */
        .atentamente {
            margin-top: 12px;
            margin-bottom: 0;
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

    <h1 class="title">SOLICITUD DE USO DE VACACIONES</h1>

    <!-- Fecha y lugar -->
    <div class="content">
        <p>
            Panamá, <span class="field-line field-day">{{ $dia_carta ?? '' }}</span> de
            <span class="field-line field-month">{{ $mes_carta ?? '' }}</span> de
            <span class="field-line field-year">{{ $anio_carta ?? date('Y') }}</span>.
        </p>
    </div>

    <!-- Destinatario -->
    <div class="recipient">
        <p>Licenciada</p>
        <p class="recipient-name">NEDELKA E. ÁLVAREZ G.</p>
        <p class="recipient-name">Jefa de la Oficina Institucional de Recursos Humanos</p>
        <p>En Su Despacho.</p>
    </div>

    <!-- Contenido principal como párrafo continuo -->
    <div class="content">
        <p class="main-paragraph">
            Le informo que a partir del día <span class="field-content">{{ $dia_inicio ?? '' }}</span> de <span class="field-content">{{ $mes_inicio ?? '' }}</span> de <span class="field-content">{{ $anio_inicio ?? '' }}</span>, el (la) servidor(a) Público(a) <span class="field-content">{{ $nombre ?? '' }}</span>, con cédula de identidad personal No. <span class="field-content">{{ $cedula ?? '' }}</span>, Posición No. <span class="field-content">{{ $posicion ?? '' }}</span>, Salario Mensual B/. <span class="field-content">{{ $salario ?? '' }}</span>, comenzará a hacer uso de <span class="field-content">{{ $dias ?? '' }}</span> días de vacaciones mediante Resuelto No <span class="field-content">{{ $resuelto_no ?? '' }}</span> de <span class="field-content">{{ $resuelto_fecha ?? '' }}</span>, a que tiene derecho, según el Artículo No. 96 del Texto Único de la ley No.9 del 20 de junio de 1994, ordenado por la Ley 23 del 12 de mayo de 2017.
        </p>

        <p>
            Dicho(a) servidor(a) público(a) deberá reincorporarse a sus labores el
            día <span class="field-line field-day">{{ $dia_fin ?? '' }}</span> de
            <span class="field-line field-month">{{ $mes_fin ?? '' }}</span> de
            <span class="field-line field-year">{{ $anio_fin ?? '' }}</span>.
        </p>
    </div>

    <p class="atentamente">Atentamente,</p>

    <!-- Sección de firmas principales -->
    <div class="signatures">
        <div class="signature-row">
            <div class="signature-cell">
                <div class="signature-line" style="position: relative;">
                    @if(isset($firma_path) && $firma_path)
                        <img src="{{ public_path('storage/' . $firma_path) }}" alt="Firma"
                             style="max-height: 40px; position: absolute; top: -45px; left: 50%; transform: translateX(-50%);">
                    @endif
                </div>
                <p class="signature-label">Servidor Público</p>
            </div>
            <div class="signature-cell">
                <div class="signature-line" style="position: relative;">
                    @if(isset($firma_jefe_path) && $firma_jefe_path)
                        <img src="{{ public_path('storage/' . $firma_jefe_path) }}" alt="Firma Jefe"
                             style="max-height: 40px; position: absolute; top: -45px; left: 50%; transform: translateX(-50%);">
                    @endif
                </div>
                <p class="signature-label">Jefe Inmediato</p>
            </div>
            <div class="signature-cell">
                <div class="signature-line" style="position: relative;">
                    @if(isset($firma_rrhh) && $firma_rrhh)
                        <img src="{{ public_path('storage/' . $firma_rrhh) }}" alt="Firma RRHH"
                             style="max-height: 40px; position: absolute; top: -45px; left: 50%; transform: translateX(-50%);">
                    @endif
                </div>
                <p class="signature-label">Enlace de Recursos Humanos</p>
            </div>
        </div>
    </div>

    <!-- Sección OIRH con borde -->
    <div class="oirh-section">
        <p class="oirh-header">Para Uso Exclusivo de la Oficina Institucional de Recursos Humanos Presidencia</p>

        <p class="vacation-title">Vacaciones correspondientes a:</p>

        <div class="vacation-details">
            <p>
                Resuelto No.<span class="field-line field-resuelto-no"></span> de
                <span class="field-line field-month"></span> Periodo de
                <span class="field-line field-month"></span> al
                <span class="field-line field-month"></span>
                (<span class="field-line field-days"></span> días).
            </p>

            <p>
                Resuelto No.<span class="field-line field-resuelto-no"></span> de
                <span class="field-line field-month"></span> Periodo de
                <span class="field-line field-month"></span> al
                <span class="field-line field-month"></span>
                (<span class="field-line field-days"></span> días).
            </p>

            <p>
                Resuelto No.<span class="field-line field-resuelto-no"></span> de
                <span class="field-line field-month"></span> Periodo de
                <span class="field-line field-month"></span> al
                <span class="field-line field-month"></span>
                (<span class="field-line field-days"></span> días).
            </p>
        </div>

        <div class="observation-section">
            <p>Observación: <span class="observation-field"></span></p>
        </div>

        <!-- Firmas finales dentro de la sección OIRH -->
        <div class="final-signatures">
            <div class="final-row">
                <div class="final-cell">
                    <div class="final-line"></div>
                    <p class="final-label">Revisado y Registrado por</p>
                </div>
                <div class="final-cell">
                    <div class="final-line"></div>
                    <p class="final-label">Autorizado por la<br>Jefa de la OIRH</p>
                </div>
                <div class="final-cell">
                    <div class="final-line"></div>
                    <p class="final-label">Fecha</p>
                </div>
            </div>
        </div>
    </div>

</div>
</body>
</html>
