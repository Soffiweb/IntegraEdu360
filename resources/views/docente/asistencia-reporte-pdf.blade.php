<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #303237; margin: 0; }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; border-top: 1px solid #cbd5e1; font-size: 7px; color: #6b7280; }
        .footer-left, .footer-right { width: 20%; }
        .footer-center { width: 60%; text-align: center; }
        .filters { margin-bottom: 10px; font-size: 8px; }
        .report-table { width: 100%; border-collapse: collapse; table-layout: auto; margin: 8px 0 12px; }
        thead { display: table-header-group; }
        .report-table th, .report-table td { border: 1px solid #94a3b8; padding: 4px; vertical-align: top; word-wrap: break-word; }
        .report-table th { background: #28449a; color: #fdfdff; font-size: 8px; }
        .report-table tr { page-break-inside: avoid; }
        .report-table .group { background: #edf1fb; color: #28449a; text-align: left; font-size: 10px; }
        .total td { background: #edf1fb; font-weight: bold; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .summary { margin-top: 12px; padding: 10px; background: #edf1fb; page-break-inside: avoid; }
        @include('components.pdf.header-styles')
    </style>
</head>
<body>
    @php
        $porCentaje = fn ($valor) => $valor === null ? '—' : number_format($valor, 2, ',', '.').' %';
        $conteoEstados = fn ($totales) => collect($estados)->map(fn ($etiqueta, $codigo) => $etiqueta.': '.$totales[$codigo])->implode(' · ');
    @endphp
    <x-pdf.header :institucion="$institucion" :titulo="$titulo" />
    <div class="footer"><table class="footer-table"><tr>
        <td class="footer-left"></td><td class="footer-center">INTEGRAEDU360 · SOFFIWEB</td><td class="footer-right"></td>
    </tr></table></div>
    <div class="filters">
        <strong>Docente:</strong> {{ $docente->persona ? $docente->nombre_completo : $docente->username }}<br>
        <strong>Período académico:</strong> {{ $periodo->nombre }}
        @if($carga)<br><strong>Clase:</strong> {{ $carga->asignatura?->nombre ?? $carga->titulo }} · {{ $carga->curso->nombre }} {{ $carga->paralelo->letra }} · {{ $carga->curso->especialidad?->nombre ?? 'EGB' }}@endif
        @if($desde || $hasta)<br><strong>Fechas:</strong> @if($desde)desde {{ \Carbon\CarbonImmutable::parse($desde)->format('d/m/Y') }} @endif @if($hasta)hasta {{ \Carbon\CarbonImmutable::parse($hasta)->format('d/m/Y') }}@endif @endif
    </div>
    @forelse($grupos as $grupo)
        <table class="report-table">
            <thead>
                <tr><th colspan="{{ 4 + count($estados) }}" class="group">{{ $grupo['titulo'] }}</th></tr>
                <tr>
                    <th style="width:15%">Fecha</th>
                    <th style="width:{{ $agrupacion === 'cursos' ? 28 : 34 }}%">{{ $agrupacion === 'cursos' ? 'Asignatura' : 'Asignatura · curso · paralelo' }}</th>
                    <th>Tema</th>
                    <th style="width:6%">Horas</th>
                    @foreach($estados as $codigo => $etiqueta)<th style="width:5%">{{ $codigo }}</th>@endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($grupo['filas'] as $sesion)
                    <tr>
                        <td>{{ ucfirst($sesion->fecha->locale('es')->isoFormat('ddd D MMM YYYY')) }}</td>
                        <td>{{ $sesion->carga->asignatura?->nombre ?? $sesion->carga->titulo }}@if($agrupacion === 'semanas') · {{ $sesion->carga->curso->nombre }} {{ $sesion->carga->paralelo->letra }}@endif</td>
                        <td>{{ $sesion->tema ?: 'Sin tema' }}</td>
                        <td class="num">{{ $sesion->horas }}</td>
                        @foreach($estados as $codigo => $etiqueta)<td class="num">{{ $sesion->{'total_'.strtolower($codigo)} }}</td>@endforeach
                    </tr>
                @endforeach
                <tr class="total"><td colspan="{{ 4 + count($estados) }}">
                    Subtotal: {{ $grupo['clases'] }} clases · {{ $grupo['horas'] }} horas @if($agrupacion === 'semanas')de {{ number_format($cargaSemanal, 0, ',', '.') }} períodos semanales @endif· {{ $conteoEstados($grupo['estados']) }} · Asistencia: {{ $porCentaje($grupo['asistencia']) }}
                </td></tr>
            </tbody>
        </table>
    @empty
        <p>No hay clases registradas con los filtros actuales.</p>
    @endforelse
    <div class="summary">
        <strong>Resumen</strong>
        <p>{{ $agrupacion === 'cursos' ? 'Cursos y paralelos' : 'Semanas' }}: {{ $grupos->count() }} · Clases: {{ $resumen['clases'] }} · Horas: {{ $resumen['horas'] }}</p>
        <p>{{ $conteoEstados($resumen['estados']) }} · Asistencia: {{ $porCentaje($resumen['asistencia']) }}</p>
        <p>La asistencia considera como asistentes a los estudiantes presentes y atrasados. Las semanas con feriados pueden tener menos horas que la carga semanal.</p>
    </div>
</body>
</html>
