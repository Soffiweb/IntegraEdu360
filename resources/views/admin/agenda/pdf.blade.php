<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agenda institucional</title>
    <style>
        @page { margin: 105px 28px 42px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #303237; }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; border-top: 1px solid #cbd5e1; font-size: 7px; color: #6b7280; }
        .footer-left, .footer-right { width: 20%; }
        .footer-center { width: 60%; text-align: center; }
        .filters { margin-bottom: 10px; font-size: 8px; }
        .report-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead { display: table-header-group; }
        th, td { border: 1px solid #94a3b8; padding: 4px; vertical-align: top; word-wrap: break-word; }
        th { background: #28449a; color: #fff; font-size: 8px; }
        .description { white-space: pre-line; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        @include('components.pdf.header-styles')
    </style>
</head>
<body>
    <x-pdf.header :institucion="$institucion ?? null" titulo="Agenda institucional" />
    <div class="footer"><table class="footer-table"><tr>
        <td class="footer-left"></td><td class="footer-center">SOFFICAJA · SOFFIWEB</td><td class="footer-right"></td>
    </tr></table></div>
    <div class="filters">
        Desde: {{ $filtros['fecha_desde'] ?? '' ?: 'Sin límite' }} · Hasta: {{ $filtros['fecha_hasta'] ?? '' ?: 'Sin límite' }}
        <br>Cumplimiento: {{ $cumplimientos[$filtros['cumplimiento'] ?? ''] ?? 'Todos' }}
        @if (! empty($filtros['search']))<br>Búsqueda: {{ $filtros['search'] }}@endif
    </div>
    <table class="report-table">
        <thead><tr>
            <th style="width: 5%">Ord</th>
            <th style="width: 12%">Fecha</th>
            <th style="width: 14%">Horario</th>
            <th style="width: {{ $institucion ? 35 : 20 }}%">Actividad</th>
            <th style="width: 22%">Responsable</th>
            @if (! $institucion)<th style="width: 15%">Institución</th>@endif
            <th style="width: 12%">Cumplimiento</th>
        </tr></thead>
        <tbody>
            @forelse ($agendas as $agenda)
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td>{{ $agenda->fecha->format('d/m/Y') }}</td>
                    <td>{{ substr($agenda->hora_desde, 0, 5) }} - {{ substr($agenda->hora_hasta, 0, 5) }}</td>
                    <td class="description">{{ $agenda->descripcion }}</td>
                    <td>{{ $agenda->responsable }}</td>
                    @if (! $institucion)<td>{{ $agenda->institucion?->nombre }}</td>@endif
                    <td class="num">{{ $agenda->porcentaje_cumplimiento }}%</td>
                </tr>
            @empty
                <tr><td colspan="{{ $institucion ? 6 : 7 }}">No hay actividades agendadas con los filtros actuales.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:12px;padding:10px;background:#edf1fb;page-break-inside:avoid;">
        <strong>Resumen estadístico</strong>
        <p>Actividades: {{ $agendas->count() }} · Cumplidas: {{ $agendas->where('porcentaje_cumplimiento', 100)->count() }} · Pendientes: {{ $agendas->where('porcentaje_cumplimiento', 0)->count() }}</p>
        <p>Cumplimiento promedio: {{ number_format($agendas->avg('porcentaje_cumplimiento') ?? 0, 1) }}%</p>
    </div>
</body>
</html>
