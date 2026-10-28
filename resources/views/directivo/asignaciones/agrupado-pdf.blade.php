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
        .keep-group { page-break-inside: avoid; }
        thead { display: table-header-group; }
        .report-table th, .report-table td { border: 1px solid #94a3b8; padding: 4px; vertical-align: top; word-wrap: break-word; }
        .report-table th { background: #28449a; color: #fdfdff; font-size: 8px; }
        .report-table tr { page-break-inside: avoid; }
        .report-table .group { background: #edf1fb; color: #28449a; text-align: left; font-size: 10px; }
        .total td { background: #edf1fb; font-weight: bold; }
        .detail { white-space: pre-line; font-size: 8px; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .summary { margin-top: 12px; padding: 10px; background: #edf1fb; page-break-inside: avoid; }
        @include('components.pdf.header-styles')
    </style>
</head>
<body>
    <x-pdf.header :institucion="$institucion" :titulo="$titulo" />
    <div class="footer"><table class="footer-table"><tr>
        <td class="footer-left"></td><td class="footer-center">INTEGRAEDU360 · SOFFIWEB</td><td class="footer-right"></td>
    </tr></table></div>
    <div class="filters">
        <strong>Período académico:</strong> {{ $periodo->nombre }}
        @if(trim($busqueda) !== '') · Búsqueda de docente: {{ $busqueda }}@endif
        @if($tipo === 'comisiones')<br>Las comisiones no computan períodos pedagógicos de clase.@endif
        @if($tipo === 'tutores')<br>Tutorías asignadas al período lectivo seleccionado, de los cursos y paralelos activos.@endif
        @if(in_array($tipo, ['asignaturas', 'areas']))<br>Las horas corresponden únicamente a las asignaciones de clase del período. Cada docente aparece una vez por grupo.@endif
    </div>
    @forelse($grupos as $grupo)
        <table class="report-table {{ in_array($tipo, ['comisiones', 'tutores']) ? 'keep-group' : '' }}">
            <thead>
                <tr><th colspan="{{ in_array($tipo, ['asignaturas', 'areas']) ? 5 : ($tipo === 'tutores' ? 4 : 3) }}" class="group">{{ $grupo['titulo'] }}</th></tr>
                <tr>
                    <th style="width:5%">Ord</th>
                    @if($tipo === 'tutores')<th style="width:12%">Paralelo</th>@endif
                    <th style="width:{{ $tipo === 'comisiones' ? 65 : 28 }}%">{{ $tipo === 'tutores' ? 'Docente tutor' : 'Docente' }}</th>
                    <th style="width:20%">Identificación</th>
                    @if(in_array($tipo, ['asignaturas', 'areas']))<th style="width:37%">{{ $tipo === 'areas' ? 'Asignaturas, cursos y paralelos' : 'Cursos y paralelos' }}</th><th style="width:10%">Períodos / semana</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($grupo['filas'] as $fila)
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        @if($tipo === 'tutores')<td>{{ $fila['detalle'] }}</td>@endif
                        <td>{{ $fila['nombre'] }}</td><td>{{ $fila['identificacion'] }}</td>
                        @if(in_array($tipo, ['asignaturas', 'areas']))<td class="detail">{{ $fila['detalle'] }}</td><td class="num">{{ number_format((float) $fila['horas'], 2, ',', '.') }}</td>@endif
                    </tr>
                @endforeach
                <tr class="total"><td colspan="{{ in_array($tipo, ['asignaturas', 'areas']) ? 5 : ($tipo === 'tutores' ? 4 : 3) }}">
                    Subtotal: {{ $grupo['docentes'] }} docentes distintos
                    @if($tipo === 'tutores') · {{ $grupo['paralelos'] }} paralelos
                    @endif
                    @if(in_array($tipo, ['asignaturas', 'areas'])) · {{ number_format((float) $grupo['horas'], 2, ',', '.') }} períodos semanales
                    @endif
                </td></tr>
            </tbody>
        </table>
    @empty
        <p>No hay registros con los filtros actuales.</p>
    @endforelse
    <div class="summary">
        <strong>Resumen estadístico</strong>
        <p>Grupos: {{ $resumen['grupos'] }} · Docentes distintos: {{ $resumen['docentes'] }}</p>
        @if($tipo === 'comisiones')<p>Participaciones en comisiones: {{ $resumen['participaciones'] }}. No se suman a la carga pedagógica.</p>@endif
        @if($tipo === 'tutores')<p>Paralelos: {{ $resumen['paralelos'] }} · Con tutor válido: {{ $resumen['paralelos'] - $resumen['sin_tutor'] }} · Sin tutor válido: {{ $resumen['sin_tutor'] }}</p>@endif
        @if(in_array($tipo, ['asignaturas', 'areas']))<p>Participaciones docente/grupo: {{ $resumen['participaciones'] }} · Paralelos distintos: {{ $resumen['paralelos'] }} · Total de períodos pedagógicos semanales: {{ number_format((float) $resumen['horas'], 2, ',', '.') }}</p>@endif
        <p>Los docentes se cuentan una sola vez en el total general, aunque participen en varios grupos. Resumen calculado sobre los registros incluidos en este reporte.</p>
    </div>
</body>
</html>
