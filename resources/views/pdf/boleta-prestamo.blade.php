<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta #{{ $prestamo->codigo }}</title>
    <style>
        @page {
            size: letter;
            margin: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            background: #fff;
        }
        .container {
            width: 100%;
            height: 48vh; /* Mitad de carta aprox */
            border-bottom: 2px dashed #ccc;
            padding-bottom: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .logo-section {
            display: table-cell;
            width: 20%;
            vertical-align: middle;
        }
        .logo {
            width: 80px;
            height: auto;
        }
        .company-info {
            display: table-cell;
            width: 50%;
            vertical-align: middle;
            font-size: 10px;
            line-height: 1.4;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .ticket-info {
            display: table-cell;
            width: 30%;
            text-align: right;
            vertical-align: top;
        }
        .ticket-number {
            font-size: 24px;
            font-weight: bold;
            color: #057894ff;
        }
        .ticket-label {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #555;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            background: #f0f0f0;
            padding: 2px 2px;
            margin-bottom: 2px;
            border-left: 4px solid #333;
        }

        .grid {
            display: table;
            width: 100%;
            margin-bottom: 2px;
        }
        .col {
            display: table-cell;
            padding-right: 10px;
        }
        .field {
            margin-bottom: 4px;
        }
        .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        .value {
            font-size: 11px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }
        th {
            background: #f0f0f0;
            text-align: left;
            padding: 4px 8px;
            border-bottom: 1px solid #ddd;
        }
        td {
            padding: 4px 8px;
            border-bottom: 1px solid #eee;
        }

        .terms {
            font-size: 8px;
            text-align: justify;
            color: #000000ff;
            margin-top: 10px;
            line-height: 1.2;
            border: 1px solid #eee;
            padding: 8px;
        }

        .signatures {
            margin-top: 30px;
            display: table;
            width: 100%;
        }
        .sig-box {
            display: table-cell;
            width: 45%;
            text-align: center;
        }
        .sig-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto;
            padding-top: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .totals {
            text-align: right;
            font-size: 12px;
            margin-top: 10px;
        }
        .total-row {
            margin-bottom: 2px;
        }
        .total-big {
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }

        .watermark {
            position: absolute;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(0,0,0,0.03);
            font-weight: bold;
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="watermark">ORIGINAL</div>
        
        <div class="header">
            <div class="logo-section">
                <!-- Ajusta la ruta del logo si es necesario o úsala base64 -->
                @if(file_exists(public_path('LOGO2026-01.jpg')))
                    <img src="{{ public_path('LOGO2026-01.jpg') }}" class="logo">
                @else
                    <div style="font-size:20px; font-weight:bold;">PRESTAMAX</div>
                @endif
            </div>
            <div class="company-info">
                <div class="company-name">Casa de Empeños "ZENTEVEDLU"</div>
                <div>Av. Panamericana / Av. V. de Loreto</div>
                <div>Cochabamba - Bolivia</div>
                <div><strong>Whatsapp:</strong> 60763882 | <strong>NIT:</strong> 8420256011</div>
            </div>
            <div class="ticket-info">
                <div class="ticket-label">CÓDIGO DE PRÉSTAMO</div>
                <div class="ticket-number">{{ $prestamo->codigo }}</div>
                <div style="font-size: 9px; color: #666; margin-top: 4px;">COPIA CLIENTE</div>
            </div>
        </div>

        <div class="grid">
            <div class="col" style="width: 60%;">
                <div class="section-title">DATOS DEL CLIENTE</div>
                <div class="grid">
                    <div class="col">
                        <div class="field"><span class="label">NOMBRE:</span> <span class="value">{{ $prestamo->cliente->nombre }}</span></div>
                        <div class="field"><span class="label">CI/NIT:</span> <span class="value">{{ $prestamo->cliente->ci }}</span></div>
                    </div>
                </div>
            </div>
            <div class="col" style="width: 40%;">
                <div class="section-title">DETALLES DEL PRÉSTAMO</div>
                <table style="width: 100%; margin-top: 5px;">
                    <tr>
                        <td style="border: none; padding: 2px 0;">
                            <div class="label">FECHA INICIO</div>
                            <div class="value">{{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y') }}</div>
                        </td>
                        <td style="border: none; padding: 2px 0; text-align: right;">
                            <div class="label">VENCIMIENTO</div>
                            <div class="value" style="color: #944805ff;">{{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->addDays(30)->format('d/m/Y') }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: none; padding-top: 2px;">
                            <div style="background: #f9f9f9; padding: 4px; border-radius: 4px; border: 1px solid #eee;">
                                <span class="label">MULTA RETRASO:</span> 
                                <span class="value" style="color: #c11306ff; margin-left: 5px;">{{ $prestamo->multa_por_retraso ?? '--' }}</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section-title">ARTÍCULOS EN GARANTÍA PRENDARIA</div>
        <table>
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>ARTÍCULO</th>
                    <th>DESCRIPCIÓN / OBSERVACIONES</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($prestamo->articulos as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><b>{{ $item->nombre_articulo }}</b></td>
                    <td>{{ $item->descripcion }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row"><span class="label">CAPITAL PRESTADO:</span> <span class="total-big">Bs {{ number_format($prestamo->monto, 2) }}</span></div>
        </div>

        <div class="terms">
            <strong>TÉRMINOS Y CONDICIONES:</strong>
            1. El deudor declara haber recibido el monto detallado a su entera satisfacción.
            2. El plazo del contrato es de 30 días calendario. Vencido el plazo, se generará una mora automática.
            3. La falta de pago o renovación por más de 90 días facultará a la Casa de Empeños a disponer del bien prendado para recuperar el capital, sin necesidad de notificación judicial.
            4. El deudor garantiza ser el legítimo propietario de los bienes dejados en garantía.
        </div>

        <div class="signatures">
            <div class="sig-box">
                <div class="sig-line">FIRMA DEL CLIENTE</div>
                <div style="font-size: 8px; margin-top: 2px;">{{ $prestamo->cliente->nombre }}</div>
            </div>
            <div class="sig-box">
                <!-- Espacio para sello opcional -->
                <div class="sig-line">FIRMA AUTORIZADA</div>
                <div style="font-size: 8px; margin-top: 2px;">CASA DE EMPEÑOS</div>
            </div>
        </div>
    </div>



</body>
</html>
