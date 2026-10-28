<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de paralelos</title>
    <style>
        @page { margin: 92px 32px 54px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #303237; margin: 0; }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; border-top: 1px solid #cbd5e1; font-size: 7px; color: #6b7280; }
        .footer-left, .footer-right { width: 20%; }
        .footer-center { width: 60%; text-align: center; }
        .filters { margin-bottom: 10px; font-size: 8px; }
        .report-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead { display: table-header-group; }
        .report-table th, .report-table td { border: 1px solid #94a3b8; padding: 4px; vertical-align: top; word-wrap: break-word; }
        .report-table th { background: #28449a; color: #fdfdff; font-size: 8px; }
        .report-table tbody tr { page-break-inside: avoid; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .report-table { margin-bottom: 12px; }
        .report-table .group-title { background: #edf1fb; color: #28449a; text-align: left; font-size: 10px; }
        .subtotal td { background: #edf1fb; font-weight: bold; border-top: 2px solid #28449a; }
        .statistics { margin-top: 12px; padding: 10px; background: #edf1fb; page-break-inside: avoid; }
        .statistics h3 { margin: 0 0 6px; color: #28449a; }
        .statistics p { margin: 4px 0; }
        @include('components.pdf.header-styles')
    </style>
</head>
<body>
    <x-pdf.header :institucion="$institucion ?? null" titulo="Reporte de paralelos" />
    <div class="footer"><table class="footer-table"><tr>
        <td class="footer-left"></td><td class="footer-center">INTEGRAEDU360 · SOFFIWEB</td><td class="footer-right"></td>
    </tr></table></div>
    <div class="filters">
        Estado: {{ $estados[$filtros['estado'] ?? ''] ?? 'Todos' }}
        @if (! empty($filtros['search'])) · Búsqueda: {{ $filtros['search'] }}@endif
        @if ($cursoFiltrado) · Curso: {{ $cursoFiltrado->nombre }}@endif
    </div>
    @php $ordinal = 0; @endphp
    @forelse ($grupos as $grupo)
        <table class="report-table">
            <thead>
                <tr><th colspan="{{ $institucion ? 7 : 8 }}" class="group-title">{{ $grupo['nombre'] }}@if (! $institucion) — {{ $grupo['institucion'] }}@endif</th></tr>
                <tr>
                    <th style="width: 6%">Ord</th>
                    <th style="width: {{ $institucion ? 28 : 22 }}%">Curso</th>
                    <th style="width: 8%">Paralelo</th>
                    <th style="width: 10%">Capacidad</th>
                    <th style="width: 12%">Matriculados</th>
                    <th style="width: {{ $institucion ? 26 : 20 }}%">Tutor</th>
                    <th style="width: 10%">Estado</th>
                    @if (! $institucion)<th style="width: 12%">Institución</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach ($grupo['paralelos'] as $paralelo)
                    <tr>
                        <td class="num">{{ ++$ordinal }}</td>
                        <td>{{ $paralelo->curso?->nombre ?: 'Sin curso' }}</td>
                        <td>{{ $paralelo->letra }}</td>
                        <td class="num">{{ $paralelo->capacidad }}</td>
                        <td class="num">—</td>
                        <td>{{ $paralelo->tutor ? ($paralelo->tutor->persona ? $paralelo->tutor->nombre_completo : $paralelo->tutor->username) : 'Sin asignar' }}</td>
                        <td>{{ $estados[$paralelo->estado] ?? $paralelo->estado }}</td>
                        @if (! $institucion)<td>{{ $paralelo->curso?->institucion?->nombre ?: 'Sin institución' }}</td>@endif
                    </tr>
                @endforeach
                <tr class="subtotal">
                    <td colspan="3">Subtotal: {{ $grupo['total'] }} paralelos · {{ $grupo['cursos'] }} cursos</td>
                    <td class="num">{{ $grupo['capacidad'] }}</td>
                    <td class="num">—</td>
                    <td colspan="{{ $institucion ? 2 : 3 }}">Con tutor: {{ $grupo['con_tutor'] }}</td>
                </tr>
            </tbody>
        </table>
    @empty
        <p>No hay paralelos con los filtros actuales.</p>
    @endforelse
    <div class="statistics">
        <h3>Resumen estadístico</h3>
        <p>Paralelos: <strong>{{ $resumen['paralelos'] }}</strong> · Cursos: <strong>{{ $resumen['cursos'] }}</strong> · Especialidades: <strong>{{ $resumen['especialidades'] }}</strong></p>
        <p>Capacidad total: <strong>{{ $resumen['capacidad'] }} alumnos</strong> · Matriculados: <strong>—</strong></p>
        <p>Activos: <strong>{{ $resumen['activos'] }}</strong> · Inactivos: <strong>{{ $resumen['inactivos'] }}</strong></p>
        <p>Con tutor: <strong>{{ $resumen['con_tutor'] }}</strong> · Sin tutor: <strong>{{ $resumen['sin_tutor'] }}</strong></p>
    </div>
</body>
</html>
