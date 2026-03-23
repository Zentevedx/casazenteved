<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta #{{ $prestamo->codigo }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'UFCSans';
            src: url('data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(public_path('Fuentes/UFCSans-Bold.ttf'))) }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        @font-face {
            font-family: 'UFCSans';
            src: url('data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(public_path('Fuentes/UFCSans-Regular.ttf'))) }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        :root {
            --dark: #0f172a;
            --gray: #475569;
            --light-gray: #f8fafc;
            --border: #cbd5e1;
            --accent: #014959ff; /* Deep Orange for highlights */
            --light-accent: #fff7ed; /* Very faint orange background */
            --danger: #dc2626; /* Red for Multa */
        }

        @page {
            size: letter;
            margin: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 10px 20px; 
            color: var(--dark);
            background: #ffffff;
            font-size: 8px; 
            line-height: 1.2;
            -webkit-print-color-adjust: exact;
        }

        .receipt-container {
            width: 100%;
            /* Auto height allows the container to snap tightly, removing extra signature space! */
            height: auto; 
            max-height: 48vh; 
            box-sizing: border-box;
            background: #fff;
            display: flex;
            flex-direction: column;
            overflow: hidden; 
        }

        .ufc-font {
            font-family: 'UFCSans', sans-serif !important;
            letter-spacing: 0.5px;
        }

        .highlight-orange {
            color: var(--accent);
            font-weight: bold;
        }

        .highlight-red {
            color: var(--danger);
            font-weight: bold;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--dark);
            padding-bottom: 6px;
            margin-bottom: 8px;
            flex-shrink: 0;
        }

        .header-left {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .logo-img {
            height: 50px; 
        }

        .company-name {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            margin: 0 0 2px 0;
        }

        .company-details {
            font-size: 9px;
            color: var(--gray);
            line-height: 1.3;
        }

        .ticket-badge {
            text-align: right;
        }

        .ticket-badge-label {
            font-size: 9px;
            color: var(--gray);
            font-weight: 700;
            text-transform: uppercase;
        }

        .ticket-number {
            font-size: 30px;
            line-height: 1;
        }

        /* Lean columns for data */
        .main-columns {
            display: flex;
            gap: 15px;
            margin-bottom: 6px;
            flex-shrink: 0;
        }

        .col {
            flex: 1;
        }

        .section-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            background: var(--light-gray);
            padding: 3px 6px;
            margin-bottom: 4px;
            border-left: 2px solid var(--dark);
        }

        .data-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 3px;
            border-bottom: 1px dotted var(--border);
            padding-bottom: 2px;
        }

        .data-label {
            color: var(--gray);
            font-weight: 600;
            font-size: 9px;
        }

        .data-value {
            font-weight: 700;
            font-size: 9px;
            text-align: right;
        }

        /* Minimal Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 6px;
            flex-shrink: 1; 
        }

        .items-table th {
            background: var(--light-gray);
            font-weight: 700;
            text-transform: uppercase;
            padding: 3px 5px;
            text-align: left;
            border-top: 1px solid var(--dark);
            border-bottom: 1px solid var(--dark);
        }

        .items-table td {
            padding: 3px 5px;
            border-bottom: 1px solid var(--border);
        }

        .item-name {
            font-weight: 700;
        }

        /* Compact Terms block */
        .terms-text {
            font-size: 8px;
            color: var(--gray);
            text-align: justify;
            line-height: 1.3;
            border-left: 2px solid var(--gray);
            padding-left: 8px;
            margin-bottom: 12px;
            margin-top: 12px;
        }

        /* Signatures snap tightly above footer */
        .signatures-grid {
            display: flex;
            justify-content: space-between;
            flex-shrink: 0;
            padding: 0 10px;
            margin-top: 30px; /* Reduces huge empty space by snapping */
        }

        .signature-box {
            width: 35%;
            text-align: center;
        }

        .signature-line {
            height: 1px;
            background: var(--dark);
            margin-bottom: 2px;
        }

        .signature-name {
            font-size: 9px;
            font-weight: 800;
            color: var(--dark);
            text-transform: uppercase;
        }
        
    </style>
</head>
<body>

    <div class="receipt-container">
        
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                @if(file_exists(public_path('LOGO2026-01.jpg')))
                    <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('LOGO2026-01.jpg'))) }}" class="logo-img">
                @else
                    <div style="font-size: 20px; font-weight: 800; line-height: 1;">PRESTAMAX</div>
                @endif
                <div>
                    <h1 class="company-name">CASA DE EMPEÑOS "ZENTEVEDLU"</h1>
                    <div class="company-details">
                        Av. Panamericana / V. de Loreto | Cochabamba<br>
                        <strong>NIT:</strong> 8420256011 | <strong>WA:</strong> 60763882
                    </div>
                </div>
            </div>
            
            <div class="ticket-badge">
                <div class="ticket-badge-label">CÓDIGO PRÉSTAMO</div>
                <div class="ticket-number highlight-orange ufc-font">{{ $prestamo->codigo }}</div>
            </div>
        </div>

        <!-- 2 Columns -->
        <div class="main-columns">
            <div class="col">
                <div class="section-title">Datos del Titular</div>
                <div class="data-row">
                    <span class="data-label">Nombre Comercial:</span>
                    <span class="data-value">{{ mb_strimwidth($prestamo->cliente->nombre, 0, 35, "...") }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Documento C.I.:</span>
                    <span class="data-value">{{ $prestamo->cliente->ci }}</span>
                </div>
            </div>
            
            <div class="col">
                <div class="section-title">Contrato y Capital</div>
                
                <!-- FECHAS JUNTAS -->
                <div class="data-row">
                    <span class="data-label">Emisión / Venc. (30D):</span>
                    <span class="data-value">
                        {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y') }} &nbsp;|&nbsp; 
                        <span style="font-weight: 800; color: var(--dark); text-decoration: underline;">
                            {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->addDays(30)->format('d/m/Y') }}
                        </span>
                    </span>
                </div>
                
                <div class="data-row">
                    <span class="data-label">Multa por Retraso:</span>
                    <span class="data-value highlight-red ufc-font" style="font-size: 11px;">Bs {{ number_format($prestamo->multa_por_retraso ?? 0, 2) }}</span>
                </div>
                
                <!-- CAPITAL PRESTADO JUNTO CON EL CONTRATO -->
                <div class="data-row" style="border: 1px solid var(--accent); padding: 4px; margin-top: 4px; border-radius: 4px; border-bottom: 1px solid var(--accent);">
                    <span class="data-label" style="color: var(--dark); font-weight: 800;">CAPITAL PRESTADO:</span>
                    <span class="data-value highlight-orange ufc-font" style="font-size: 13px;">Bs {{ number_format($prestamo->monto, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="section-title" style="margin-bottom: 2px;">Garantía Prendaria (Física)</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th width="8%">#</th>
                    <th width="42%">Artículo Nominal</th>
                    <th width="50%">Descripción / Observaciones Técnicas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($prestamo->articulos as $index => $item)
                <tr>
                    <td style="font-weight: 700; color: var(--dark);">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td><span class="item-name">{{ $item->nombre_articulo }}</span></td>
                    <td style="color: var(--gray);">{{ mb_strimwidth($item->descripcion, 0, 75, "...") }}</td>
                </tr>
                @endforeach
                @if($prestamo->articulos->count() == 0)
                <tr><td colspan="3" style="text-align: center; color: var(--gray); font-style: italic;">Sin artículos registrados bajo este código.</td></tr>
                @endif
            </tbody>
        </table>

        <!-- Bottom Layout: Terms -->
        <div class="terms-text">
            <strong>TÉRMINOS Y CONDICIONES: 1.</strong> El deudor declara recibir el capital detallado a su libre satisfacción. <strong>2.</strong> Plazo estricto de 30 días calendario. El vencimiento del mismo genera obligaciones de mora automáticamente. <strong>3.</strong> La falta absoluta de pago o renovación documentada de esta boleta tras 90 días faculta legal y ejecutivamente a la Casa de Empeños "ZENTEVEDLU" a enajenar, liquidar o rematar la garantía física para recuperación del capital emitido sin necesidad ni responsabilidad de notificaciones procesales adicionales. <strong>4.</strong> El cliente testifica civil y penalmente ser propietario legal de la prenda.
        </div>

        <!-- Signatures directly below terms due to auto-height -->
        <div class="signatures-grid">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-name">{{ mb_strimwidth($prestamo->cliente->nombre, 0, 30, "...") }}</div>
                <div style="font-size: 6px; color: var(--gray);">C.I. {{ $prestamo->cliente->ci }} - Firma del Prestatario / Titular</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-name">CASA DE EMPEÑOS "ZENTEVEDLU"</div>
                <div style="font-size: 6px; color: var(--gray);">Sello y Firma Aprobada Administración</div>
            </div>
        </div>
        
    </div>

</body>
</html>
