<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de asignaturas</title>
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
        .statistics h3 { margin: 0 0 6px; color: #28449a; font-size: 0.585em; }
        .statistics p { margin: 4px 0; }
        @include('components.pdf.header-styles')
    </style>
</head>
<body>
    <x-pdf.header :institucion="$institucion ?? null" titulo="Reporte de asignaturas" />
    <div class="footer"><table class="footer-table"><tr>
        <td class="footer-left"></td><td class="footer-center">INTEGRAEDU360 · SOFFIWEB</td><td class="footer-right"></td>
    </tr></table></div>
    <div class="filters">Año lectivo: {{ $anioLectivo }} · Total de asignaturas: {{ $totalAsignaturas }}</div>
    @php $ordinal = 0; @endphp
    @forelse ($asignaturasAgrupadas as $especialidadNombre => $porCurso)
        @foreach ($porCurso as $cursoGroup)
            <table class="report-table">
                <thead>
                    <tr><th colspan="6" class="group-title">{{ $especialidadNombre }} — {{ $cursoGroup['curso']?->nombre ?? 'Sin curso asignado' }}</th></tr>
                    <tr>
                        <th style="width: 6%">Ord</th>
                        <th style="width: 36%">Asignatura</th>
                        <th style="width: 22%">Área</th>
                        <th style="width: 16%">Código</th>
                        <th style="width: 10%">Hrs/sem.</th>
                        <th style="width: 10%">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cursoGroup['items'] as $asignatura)
                        <tr>
                            <td class="num">{{ ++$ordinal }}</td>
                            <td>{{ $asignatura->nombre }}</td>
                            <td>{{ $areas[$asignatura->area] ?? $asignatura->area }}</td>
                            <td>{{ $asignatura->codigo ?: '—' }}</td>
                            <td class="num">{{ $asignatura->horas_semana ?? '—' }}</td>
                            <td>{{ $estados[$asignatura->estado] ?? $asignatura->estado }}</td>
                        </tr>
                    @endforeach
                    <tr class="subtotal">
                        <td colspan="6">Subtotal: {{ count($cursoGroup['items']) }} asignaturas</td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @empty
        <p>No hay asignaturas para mostrar.</p>
    @endforelse
    <div class="statistics">
        <h3>Resumen estadístico</h3>
        <p>Asignaturas: <strong>{{ $totalAsignaturas }}</strong> · Grupos de especialidad: <strong>{{ count($asignaturasAgrupadas) }}</strong></p>
    </div>
</body>
</html>
