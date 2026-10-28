<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista para firmas de docentes</title>
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
        .report-table tbody td { height: 18mm; padding: 0 4px; vertical-align: middle; }
        .teacher-name { height: 16mm; overflow: hidden; line-height: 1.3; }
        .teacher-name span { display: block; padding-top: 2mm; }
        .report-table .empty { height: auto; padding: 8px; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        @include('components.pdf.header-styles')
    </style>
</head>
<body>
    <x-pdf.header :institucion="$institucion ?? null" titulo="Lista para firmas de docentes" />
    <div class="footer"><table class="footer-table"><tr>
        <td class="footer-left"></td><td class="footer-center">INTEGRAEDU360 · SOFFIWEB</td><td class="footer-right"></td>
    </tr></table></div>
    <div class="filters">
        Estado: {{ $estados[$filtros['estado'] ?? ''] ?? 'Todos' }}
        @if (! empty($filtros['search'])) · Búsqueda: {{ $filtros['search'] }}@endif
    </div>
    <table class="report-table">
        <thead><tr>
            <th style="width: 6%">ORD</th>
            <th style="width: 20%">IDENTIFICACIÓN</th>
            <th style="width: 44%">NOMBRES</th>
            <th style="width: 30%">FIRMA</th>
        </tr></thead>
        <tbody>
            @forelse ($docentes as $docente)
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td>{{ $docente->persona?->numero_identificacion ?: 'Sin registro' }}</td>
                    <td><div class="teacher-name"><span>{{ $docente->nombre_completo }}</span></div></td>
                    <td></td>
                </tr>
            @empty
                <tr><td class="empty" colspan="4">No hay docentes con los filtros actuales.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:12px;padding:10px;background:#edf1fb;page-break-inside:avoid;">
        <strong>Resumen estadístico</strong>
        <p>Total docentes: {{ $docentes->count() }} · Activos: {{ $docentes->where('estado', 'ACTIVO')->count() }} · En observación: {{ $docentes->where('estado', 'OBSERVACION')->count() }} · Inactivos: {{ $docentes->where('estado', 'INACTIVO')->count() }}</p>
    </div>
</body>
</html>
