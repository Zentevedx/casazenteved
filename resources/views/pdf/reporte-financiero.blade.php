<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; margin: 0; padding: 10px; font-size: 10px; }
        .header { text-align: center; border-bottom: 2px solid #057894; padding-bottom: 5px; margin-bottom: 10px; }
        .logo { font-size: 18px; font-weight: bold; color: #057894; text-transform: uppercase; }
        .report-title { font-size: 14px; margin-top: 2px; color: #555; }
        .stats-container { width: 100%; margin-bottom: 15px; }
        .stat-box { display: inline-block; width: 30%; padding: 5px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; margin-right: 2%; margin-bottom: 5px; vertical-align: top; }
        .stat-label { font-size: 8px; text-transform: uppercase; color: #777; margin-bottom: 2px; }
        .stat-value { font-size: 12px; font-weight: bold; color: #000; }
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; background: #057894; color: #fff; padding: 4px 8px; margin-top: 15px; margin-bottom: 5px; border-radius: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th { background: #f2f2f2; text-align: left; padding: 3px; border-bottom: 1px solid #999; font-weight: bold; font-size: 9px; }
        td { padding: 3px; border-bottom: 1px solid #eee; font-size: 9px; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
        .text-right { text-align: right; }
        .text-danger { color: #d9534f; }
        .badge { display: inline-block; padding: 1px 3px; border-radius: 2px; font-size: 8px; font-weight: bold; color: white; }
        .bg-green { background-color: #28a745; }
        .bg-blue { background-color: #007bff; }
        .bg-red { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">PRESTAMAX - CASA DE EMPEÑOS</div>
        <div class="report-title">{{ $titulo }}</div>
        <div style="font-size: 10px; color: #888;">Periodo: {{ $periodo }} | Generado: {{ $fecha_generacion }}</div>
    </div>

    <!-- Resumen de Totales (Visible en todos los reportes) -->
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-label">Préstamos</div>
            <div class="stat-value">Bs {{ number_format($prestamos, 2) }}</div>
            <div style="font-size: 8px;">Cant: {{ $cantidad_prestamos }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Intereses</div>
            <div class="stat-value">Bs {{ number_format($intereses, 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Cap. Recuperado</div>
            <div class="stat-value">Bs {{ number_format($capital_recuperado, 2) }}</div>
        </div>
        <div class="stat-box" style="border-left: 3px solid #d9534f;">
            <div class="stat-label">Gastos</div>
            <div class="stat-value">Bs {{ number_format($gastos, 2) }}</div>
        </div>
        <div class="stat-box" style="background: #eef9f1; border-color: #5cb85c;">
            <div class="stat-label">Utilidad Bruta</div>
            <div class="stat-value" style="color: #2e7d32;">Bs {{ number_format($intereses - $gastos, 2) }}</div>
        </div>
    </div>

    <!-- ESTADO DE CARTERA (Solo en Resumen) -->
    @if($tipo === 'resumen')
    <div class="stats-container" style="margin-top: 15px; border-top: 1px solid #ddd; padding-top: 15px;">
        <div style="width: 100%; margin-bottom: 10px; font-weight: bold; color: #555; font-size: 11px;">ESTADO DE CARTERA ACTUAL</div>
        <div class="stat-box">
            <div class="stat-label">Dinero en Calle</div>
            <div class="stat-value">Bs {{ number_format($cartera_total ?? 0, 2) }}</div>
            <div style="font-size: 9px;">Total Prestado</div>
        </div>
        <div class="stat-box">
            <div class="stat-label" style="color: #059669;">Cartera Vigente</div>
            <div class="stat-value" style="color: #059669;">Bs {{ number_format($cartera_vigente ?? 0, 2) }}</div>
            <div style="font-size: 9px;">Recuperable</div>
        </div>
        <div class="stat-box">
            <div class="stat-label" style="color: #dc2626;">En Riesgo / Remate</div>
            <div class="stat-value" style="color: #dc2626;">Bs {{ number_format($cartera_remate ?? 0, 2) }}</div>
            <div style="font-size: 9px;">> 3 meses inact.</div>
        </div>
    </div>
    @endif
    
    <!-- CONTENIDO DINÁMICO SEGÚN TIPO -->

    <!-- 1. RESUMEN O REMATE -->
    @if($tipo === 'resumen' || $tipo === 'remate')
        @if($tipo === 'remate')
            <div class="section-title">DETALLE DE PRÉSTAMOS EN REMATE</div>
        @else
            <div class="section-title">RESUMEN DE PRÉSTAMOS EN RIESGO DE REMATE</div>
        @endif
        
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>CÓDIGO</th>
                    <th>CLIENTE</th>
                    <th>ARTÍCULOS</th>
                    <th>MONTO</th>
                    <th>ÚLTIMO PAGO INT.</th>
                    <th>FECHA PRÉSTAMO</th>
                    <th class="text-right">TIEMPO SIN PAGO</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prestamosRemate as $p)
                    @php
                        $uP = $p->pagos->where('tipo_pago', 'Interes')->sortByDesc('fecha_pago')->first();
                        $fRef = $uP ? \Carbon\Carbon::parse($uP->fecha_pago) : \Carbon\Carbon::parse($p->fecha_prestamo);
                        $meses = $fRef->diffInMonths(now())*1;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $p->codigo }}</strong></td>
                        <td>{{ $p->cliente->nombre }}</td>
                        <td style="font-size: 9px; color: #555;">
                            {{ $p->articulos->pluck('nombre')->join(', ') }}
                        </td>
                        <td>Bs {{ number_format($p->monto, 2) }}</td>
                        <td>{{ $uP ? \Carbon\Carbon::parse($uP->fecha_pago)->format('d/m/Y') : 'NINGUNO' }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->fecha_prestamo)->format('d/m/Y') }}</td>
                        <td class="text-right text-danger"><strong>{{ $meses }} meses</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #999;">¡Excelente! No hay préstamos en situación de remate.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <!-- 2. DETALLE DE PRÉSTAMOS -->
    @if($tipo === 'prestamos')
        <div class="section-title">DETALLE DE PRÉSTAMOS OTORGADOS</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>FECHA</th>
                    <th>CÓDIGO</th>
                    <th>CLIENTE</th>
                    <th>ARTÍCULO</th>
                    <th>ESTADO</th>
                    <th class="text-right">MONTO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($listas['prestamos'] as $p)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->fecha_prestamo)->format('d/m/Y H:i') }}</td>
                        <td><strong>{{ $p->codigo }}</strong></td>
                        <td>{{ $p->cliente->nombre }}</td>
                        <td>{{ $p->articulos->first()->nombre ?? 'Varios' }}</td>
                        <td>
                            @if($p->estado == 'Activo') <span class="badge bg-blue">ACTIVO</span>
                            @elseif($p->estado == 'Pagado') <span class="badge bg-green">PAGADO</span>
                            @else <span class="badge bg-red">{{ $p->estado }}</span>
                            @endif
                        </td>
                        <td class="text-right">Bs {{ number_format($p->monto, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- 3. DETALLE DE INTERESES O CAPITAL -->
    @if($tipo === 'intereses' || $tipo === 'capital')
        <div class="section-title">DETALLE DE {{ $tipo === 'intereses' ? 'INTERESES COBRADOS' : 'CAPITAL RECUPERADO' }}</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>FECHA PAGO</th>
                    <th>PRÉSTAMO</th>
                    <th>CLIENTE</th>
                    <th class="text-right">MONTO</th>
                </tr>
            </thead>
            <tbody>
                @php $lista = $tipo === 'intereses' ? $listas['intereses'] : $listas['capital']; @endphp
                @foreach($lista as $pago)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y H:i') }}</td>
                        <td><strong>{{ $pago->prestamo->codigo ?? 'N/A' }}</strong></td>
                        <td>{{ $pago->prestamo->cliente->nombre ?? 'N/A' }}</td>
                        <td class="text-right">Bs {{ number_format($pago->monto_pagado, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- 4. DETALLE DE GASTOS -->
    @if($tipo === 'gastos')
        <div class="section-title">DETALLE DE GASTOS OPERATIVOS</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>FECHA</th>
                    <th>DESCRIPCIÓN / CONCEPTO</th>
                    <th class="text-right">MONTO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($listas['gastos'] as $gasto)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($gasto->fecha)->format('d/m/Y H:i') }}</td>
                        <td>{{ $gasto->descripcion ?? $gasto->concepto }}</td>
                        <td class="text-right">Bs {{ number_format($gasto->monto, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Este documento es un reporte financiero oficial generado por el sistema PRESTAMAX.
    </div>
</body>
</html>
