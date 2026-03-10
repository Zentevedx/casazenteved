<table>
    <thead>
        <tr>
            <th colspan="4">{{ $titulo }}</th>
        </tr>
        <tr>
            <th colspan="4">Periodo: {{ $periodo }}</th>
        </tr>
        <tr>
            <th colspan="4">Generado: {{ $fecha_generacion }}</th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @if($tipo === 'resumen')
            <tr>
                <td style="font-weight: bold;">Métricas Principales</td>
                <td></td>
            </tr>
            <tr>
                <td>Préstamos Otorgados ({{ $cantidad_prestamos }})</td>
                <td>{{ number_format($prestamos, 2) }}</td>
            </tr>
            <tr>
                <td>Intereses Cobrados</td>
                <td>{{ number_format($intereses, 2) }}</td>
            </tr>
            <tr>
                <td>Gastos Operativos</td>
                <td>{{ number_format($gastos, 2) }}</td>
            </tr>
            <tr>
                <td>Capital Recuperado</td>
                <td>{{ number_format($capital_recuperado, 2) }}</td>
            </tr>
            <tr>
                <td>Cartera Total Estimada</td>
                <td>{{ number_format($cartera_total, 2) }}</td>
            </tr>
            <tr>
                <td>Cartera en Riesgo (Remate)</td>
                <td>{{ number_format($cartera_remate, 2) }}</td>
            </tr>
            <tr>
                <td>Cartera Vigente Saneada</td>
                <td>{{ number_format($cartera_vigente, 2) }}</td>
            </tr>
        @elseif($tipo === 'prestamos' && isset($listas['prestamos']))
            <tr>
                <td style="font-weight: bold;">CÓDIGO</td>
                <td style="font-weight: bold;">CLIENTE</td>
                <td style="font-weight: bold;">FECHA</td>
                <td style="font-weight: bold;">MONTO</td>
            </tr>
            @foreach($listas['prestamos'] as $p)
                <tr>
                    <td>{{ $p->codigo }}</td>
                    <td>{{ $p->cliente->nombre }} {{ $p->cliente->apellidos }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->fecha_prestamo)->format('Y-m-d') }}</td>
                    <td>{{ number_format($p->monto, 2) }}</td>
                </tr>
            @endforeach
        @elseif($tipo === 'intereses' && isset($listas['intereses']))
            <tr>
                <td style="font-weight: bold;">CÓDIGO PRÉSTAMO</td>
                <td style="font-weight: bold;">CLIENTE</td>
                <td style="font-weight: bold;">FECHA PAGO</td>
                <td style="font-weight: bold;">MONTO INTERÉS</td>
            </tr>
            @foreach($listas['intereses'] as $p)
                <tr>
                    <td>{{ $p->prestamo->codigo }}</td>
                    <td>{{ $p->prestamo->cliente->nombre }} {{ $p->prestamo->cliente->apellidos }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->fecha_pago)->format('Y-m-d') }}</td>
                    <td>{{ number_format($p->monto_pagado, 2) }}</td>
                </tr>
            @endforeach
        @elseif($tipo === 'capital' && isset($listas['capital']))
            <tr>
                <td style="font-weight: bold;">CÓDIGO PRÉSTAMO</td>
                <td style="font-weight: bold;">CLIENTE</td>
                <td style="font-weight: bold;">FECHA PAGO</td>
                <td style="font-weight: bold;">MONTO CAPITAL</td>
            </tr>
            @foreach($listas['capital'] as $p)
                <tr>
                    <td>{{ $p->prestamo->codigo }}</td>
                    <td>{{ $p->prestamo->cliente->nombre }} {{ $p->prestamo->cliente->apellidos }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->fecha_pago)->format('Y-m-d') }}</td>
                    <td>{{ number_format($p->monto_pagado, 2) }}</td>
                </tr>
            @endforeach
        @elseif($tipo === 'gastos' && isset($listas['gastos']))
            <tr>
                <td style="font-weight: bold;">FECHA</td>
                <td style="font-weight: bold;">DESCRIPCIÓN</td>
                <td style="font-weight: bold;">MONTO GASTO</td>
            </tr>
            @foreach($listas['gastos'] as $g)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($g->fecha)->format('Y-m-d') }}</td>
                    <td>{{ $g->descripcion }}</td>
                    <td>{{ number_format($g->monto, 2) }}</td>
                </tr>
            @endforeach
        @elseif($tipo === 'remate')
            {{-- Encabezados --}}
            <tr>
                <td style="font-weight: bold;">#</td>
                <td style="font-weight: bold;">CÓDIGO</td>
                <td style="font-weight: bold;">CLIENTE</td>
                <td style="font-weight: bold;">ARTÍCULO(S)</td>
                <td style="font-weight: bold;">FECHA INICIO</td>
                <td style="font-weight: bold;">ÚLTIMO PAGO</td>
                <td style="font-weight: bold;">DÍAS INACTIVO</td>
                <td style="font-weight: bold;">MESES</td>
                <td style="font-weight: bold;">CAPITAL EN RIESGO (Bs)</td>
            </tr>
            @php $totalRemate = 0; @endphp
            @forelse($prestamosRemate as $p)
                @php
                    if (isset($p->dias_sin_pago)) {
                        $diasSinPago  = $p->dias_sin_pago;
                        $mesesSinPago = $p->meses_sin_pago;
                        $fechaUlt     = $p->fecha_ultimo_pago;
                    } else {
                        $ultP         = $p->pagos->sortByDesc('fecha_pago')->first();
                        $fRef         = $ultP ? \Carbon\Carbon::parse($ultP->fecha_pago) : \Carbon\Carbon::parse($p->fecha_prestamo);
                        $diasSinPago  = (int) $fRef->diffInDays(now());
                        $mesesSinPago = (int) $fRef->diffInMonths(now());
                        $fechaUlt     = $ultP ? $ultP->fecha_pago : null;
                    }
                    $saldo = $p->saldo_a_fecha ?? $p->monto;
                    $totalRemate += $saldo;
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $p->codigo }}</td>
                    <td>{{ $p->cliente->nombre }}</td>
                    <td>{{ $p->articulos->pluck('nombre')->join(', ') ?: 'Sin artículo' }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->fecha_prestamo)->format('Y-m-d') }}</td>
                    <td>{{ $fechaUlt ? \Carbon\Carbon::parse($fechaUlt)->format('Y-m-d') : 'NINGUNO' }}</td>
                    <td>{{ $diasSinPago }}</td>
                    <td>{{ $mesesSinPago }}</td>
                    <td>{{ number_format($saldo, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="9">Sin préstamos en remate</td></tr>
            @endforelse
            {{-- Fila de total --}}
            <tr>
                <td style="font-weight: bold;" colspan="8">TOTAL CAPITAL EN RIESGO</td>
                <td style="font-weight: bold;">{{ number_format($totalRemate, 2) }}</td>
            </tr>
        @endif
    </tbody>
</table>

