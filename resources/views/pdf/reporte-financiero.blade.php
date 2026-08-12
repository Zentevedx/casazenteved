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

    
    
    <!-- CONTENIDO DINÁMICO SEGÚN TIPO -->

    <!-- 1. RESUMEN -->
    @if($tipo === 'resumen')
        <div class="section-title">RESUMEN DE PRÉSTAMOS EN RIESGO DE REMATE</div>
        @php $totalRemate = 0; @endphp
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>CÓDIGO</th>
                    <th>CLIENTE</th>
                    <th>ARTÍCULO(S)</th>
                    <th>INICIO PRÉSTAMO</th>
                    <th>ÚLTIMO PAGO</th>
                    <th>DÍAS INACTIVO</th>
                    <th>MESES</th>
                    <th class="text-right">CAPITAL EN RIESGO</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prestamosRemate as $p)
                    @php
                        if (isset($p->dias_sin_pago)) {
                            $diasSinPago   = $p->dias_sin_pago;
                            $mesesSinPago  = $p->meses_sin_pago;
                            $fechaUltPago  = $p->fecha_ultimo_pago;
                        } else {
                            $ultimoPago    = $p->pagos->sortByDesc('fecha_pago')->first();
                            $fRef          = $ultimoPago ? \Carbon\Carbon::parse($ultimoPago->fecha_pago) : \Carbon\Carbon::parse($p->fecha_prestamo);
                            $diasSinPago   = (int) $fRef->diffInDays(now());
                            $mesesSinPago  = (int) $fRef->diffInMonths(now());
                            $fechaUltPago  = $ultimoPago ? $ultimoPago->fecha_pago : null;
                        }
                        $saldo = $p->saldo_a_fecha ?? $p->monto;
                        $totalRemate += $saldo;
                        $urgenciaColor = $diasSinPago >= 180 ? '#dc2626' : '#f97316';
                        $articulosStr = $p->articulos->map(function($a) { return $a->nombre_articulo . ($a->descripcion ? " ({$a->descripcion})" : ""); })->join(' • ');
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $p->codigo }}</strong></td>
                        <td>{{ $p->cliente->nombre ?? 'N/A' }}</td>
                        <td style="font-size: 8px; color: #555; max-width: 120px;">{{ $articulosStr ?: 'Sin artículo' }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->fecha_prestamo)->format('d/m/Y') }}</td>
                        <td>
                            @if($fechaUltPago) {{ \Carbon\Carbon::parse($fechaUltPago)->format('d/m/Y') }}
                            @else <span style="color: #dc2626; font-weight: bold;">NINGUNO</span> @endif
                        </td>
                        <td style="text-align: center; color: {{ $urgenciaColor }}; font-weight: bold;">{{ $diasSinPago }} días</td>
                        <td style="text-align: center; color: {{ $urgenciaColor }}; font-weight: bold;">{{ $mesesSinPago }}m</td>
                        <td class="text-right" style="font-weight: bold; color: #dc2626;">Bs {{ number_format($saldo, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px; color: #059669; font-weight: bold;">✓ ¡Excelente! No hay préstamos en situación de remate.</td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($prestamosRemate) > 0)
            <tfoot>
                <tr style="background: #fef2f2; border-top: 2px solid #dc2626;">
                    <td colspan="8" style="font-weight: bold; color: #dc2626; font-size: 10px;">TOTAL CAPITAL EN RIESGO ({{ count($prestamosRemate) }} préstamos)</td>
                    <td class="text-right" style="font-weight: bold; color: #dc2626; font-size: 11px;">Bs {{ number_format($totalRemate, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>

    <!-- 1.B REMATE MODERNO Y DETALLADO -->
    @elseif($tipo === 'remate')
        <div class="section-title" style="background: #dc2626;">DETALLE DE PRÉSTAMOS EN REMATE — REPORTE PARA TOMA DE DECISIONES</div>
        @php 
            $totalRemate = 0; 
            $totalMontoInicial = 0;
        @endphp
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-family: 'Helvetica', sans-serif;">
            <thead>
                <tr style="background: #f87171; color: white;">
                    <th style="padding: 6px 4px; text-align: center; border: 1px solid #ef4444; font-size: 9px; width: 3%;">#</th>
                    <th style="padding: 6px 4px; text-align: center; border: 1px solid #ef4444; font-size: 9px; width: 8%;">CÓDIGO</th>
                    <th style="padding: 6px 4px; text-align: left; border: 1px solid #ef4444; font-size: 9px; width: 18%;">CLIENTE Y CONTACTO</th>
                    <th style="padding: 6px 4px; text-align: left; border: 1px solid #ef4444; font-size: 9px; width: 25%;">ARTÍCULOS EN GARANTÍA</th>
                    <th style="padding: 6px 4px; text-align: center; border: 1px solid #ef4444; font-size: 9px; width: 8%;">F. PRÉSTAMO</th>
                    <th style="padding: 6px 4px; text-align: center; border: 1px solid #ef4444; font-size: 9px; width: 8%;">ÚLTIMO PAGO</th>
                    <th style="padding: 6px 4px; text-align: center; border: 1px solid #ef4444; font-size: 9px; width: 8%;">INACTIVO</th>
                    <th style="padding: 6px 4px; text-align: right; border: 1px solid #ef4444; font-size: 9px; width: 10%;">PRÉSTAMO INICIAL</th>
                    <th style="padding: 6px 4px; text-align: right; border: 1px solid #ef4444; font-size: 9px; width: 12%;">SALDO (EN RIESGO)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prestamosRemate as $index => $p)
                    @php
                        if (isset($p->dias_sin_pago)) {
                            $diasSinPago   = $p->dias_sin_pago;
                            $mesesSinPago  = $p->meses_sin_pago;
                            $fechaUltPago  = $p->fecha_ultimo_pago;
                        } else {
                            $ultimoPago    = $p->pagos->sortByDesc('fecha_pago')->first();
                            $fRef          = $ultimoPago ? \Carbon\Carbon::parse($ultimoPago->fecha_pago) : \Carbon\Carbon::parse($p->fecha_prestamo);
                            $diasSinPago   = (int) $fRef->diffInDays(now());
                            $mesesSinPago  = (int) $fRef->diffInMonths(now());
                            $fechaUltPago  = $ultimoPago ? $ultimoPago->fecha_pago : null;
                        }
                        $saldo = $p->saldo_a_fecha ?? $p->monto;
                        $monto = $p->monto ?? 0;
                        $totalRemate += $saldo;
                        $totalMontoInicial += $monto;
                        
                        $urgenciaColor = $diasSinPago >= 180 ? '#991b1b' : '#c2410c'; // Darker text for readability
                        $urgenciaBg = $diasSinPago >= 180 ? '#fee2e2' : '#ffedd5';

                        $articulosStr = $p->articulos->map(function($a) { return "• " . $a->nombre_articulo . ($a->descripcion ? " <span style='color: #666; font-size: 8px;'>({$a->descripcion})</span>" : ""); })->join('<br>');
                        $rowColor = $index % 2 === 0 ? '#ffffff' : '#fafafa';
                    @endphp
                    <tr style="background: {{ $rowColor }};">
                        <td style="padding: 6px 4px; text-align: center; border: 1px solid #e5e7eb; font-size: 9px;">{{ $loop->iteration }}</td>
                        <td style="padding: 6px 4px; text-align: center; border: 1px solid #e5e7eb; font-size: 10px; font-weight: bold; color: #1e3a8a;">
                            {{ $p->codigo }}
                        </td>
                        <td style="padding: 6px 4px; border: 1px solid #e5e7eb;">
                            <div style="font-size: 10px; font-weight: bold; color: #111827; margin-bottom: 2px;">{{ $p->cliente->nombre ?? 'N/A' }}</div>
                            <div style="font-size: 8px; color: #4b5563;">CI: {{ $p->cliente->ci ?? 'S/D' }}</div>
                            <div style="font-size: 8px; color: #047857; font-weight: bold;">Telf: {{ $p->cliente->telefono ?? 'S/D' }}</div>
                        </td>
                        <td style="padding: 6px 4px; border: 1px solid #e5e7eb; font-size: 9px; vertical-align: top; line-height: 1.2;">
                            {!! $articulosStr ?: '<span style="color:#999;">Sin artículo registrado</span>' !!}
                        </td>
                        <td style="padding: 6px 4px; text-align: center; border: 1px solid #e5e7eb; font-size: 9px; color: #4b5563;">
                            {{ \Carbon\Carbon::parse($p->fecha_prestamo)->format('d/m/Y') }}
                        </td>
                        <td style="padding: 6px 4px; text-align: center; border: 1px solid #e5e7eb; font-size: 9px;">
                            @if($fechaUltPago) 
                                <span style="color: #4b5563;">{{ \Carbon\Carbon::parse($fechaUltPago)->format('d/m/Y') }}</span>
                            @else 
                                <span style="color: #dc2626; font-weight: bold; font-size: 8px; background: #fee2e2; padding: 2px 4px; border-radius: 2px;">NINGUNO</span> 
                            @endif
                        </td>
                        <td style="padding: 6px 4px; text-align: center; border: 1px solid #e5e7eb; background: {{ $urgenciaBg }};">
                            <div style="color: {{ $urgenciaColor }}; font-weight: bold; font-size: 10px;">{{ $diasSinPago }} días</div>
                            <div style="color: {{ $urgenciaColor }}; font-size: 8px;">({{ $mesesSinPago }} meses)</div>
                        </td>
                        <td style="padding: 6px 4px; text-align: right; border: 1px solid #e5e7eb; font-size: 10px; color: #6b7280;">
                            Bs {{ number_format($monto, 2) }}
                        </td>
                        <td style="padding: 6px 4px; text-align: right; border: 1px solid #e5e7eb; font-size: 11px; font-weight: bold; color: #dc2626; background: #fff1f2;">
                            Bs {{ number_format($saldo, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 30px; color: #059669; font-weight: bold; font-size: 12px; border: 1px solid #e5e7eb;">
                            ✓ ¡Excelente! No hay préstamos en situación de remate al corte.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($prestamosRemate) > 0)
            <tfoot>
                <tr>
                    <td colspan="7" style="padding: 8px; text-align: right; font-weight: bold; color: #dc2626; font-size: 11px; border: 1px solid #e5e7eb; background: #fee2e2;">
                        TOTALES DE {{ count($prestamosRemate) }} PRÉSTAMOS EN REMATE:
                    </td>
                    <td style="padding: 8px; text-align: right; font-weight: bold; color: #6b7280; font-size: 11px; border: 1px solid #e5e7eb; background: #f3f4f6;">
                        Bs {{ number_format($totalMontoInicial, 2) }}
                    </td>
                    <td style="padding: 8px; text-align: right; font-weight: bold; color: #dc2626; font-size: 13px; border: 1px solid #e5e7eb; background: #fee2e2;">
                        Bs {{ number_format($totalRemate, 2) }}
                    </td>
                </tr>
            </tfoot>
            @endif
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
