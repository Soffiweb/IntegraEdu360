<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de docentes</title>
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
        .description { white-space: pre-line; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        @include('components.pdf.header-styles')
    </style>
</head>
<body>
    <x-pdf.header :institucion="$institucion ?? null" titulo="Lista de docentes" />
    <div class="footer"><table class="footer-table"><tr>
        <td class="footer-left"></td><td class="footer-center">INTEGRAEDU360 · SOFFIWEB</td><td class="footer-right"></td>
    </tr></table></div>
    <div class="filters">
        Estado: {{ $estados[$filtros['estado'] ?? ''] ?? 'Todos' }}
        @if (! empty($filtros['search'])) · Búsqueda: {{ $filtros['search'] }}@endif
    </div>
    <table class="report-table">
        <thead><tr>
            <th style="width: 5%">Ord</th>
            <th style="width: 16%">Identificación</th>
            <th style="width: {{ $institucion ? 34 : 22 }}%">Docente</th>
            <th style="width: 15%">Teléfono / celular</th>
            <th style="width: 20%">Correo</th>
            @if (! $institucion)<th style="width: 12%">Institución</th>@endif
            <th style="width: 10%">Estado</th>
        </tr></thead>
        <tbody>
            @forelse ($docentes as $docente)
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td>{{ $docente->persona?->numero_identificacion ?: 'Sin registro' }}</td>
                    <td>{{ trim(($docente->persona?->tratamiento ?? '').' '.$docente->nombre_completo) }}</td>
                    <td>{{ $docente->persona?->celular ?: ($docente->persona?->telefono ?: 'Sin registro') }}</td>
                    <td>{{ $docente->persona?->email ?: ($docente->email ?: 'Sin registro') }}</td>
                    @if (! $institucion)<td>{{ $docente->institucion?->nombre ?: 'Sin institución' }}</td>@endif
                    <td>{{ $estados[$docente->estado] ?? $docente->estado }}</td>
                </tr>
            @empty
                <tr><td colspan="{{ $institucion ? 6 : 7 }}">No hay docentes con los filtros actuales.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:12px;padding:10px;background:#edf1fb;page-break-inside:avoid;">
        <strong>Resumen estadístico</strong>
        <p>Total docentes: {{ $docentes->count() }} · Activos: {{ $docentes->where('estado', 'ACTIVO')->count() }} · En observación: {{ $docentes->where('estado', 'OBSERVACION')->count() }} · Inactivos: {{ $docentes->where('estado', 'INACTIVO')->count() }}</p>
    </div>
</body>
</html>
