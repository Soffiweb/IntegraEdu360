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
        .report-table { width: 100%; border-collapse: collapse; table-layout: auto; margin: 8px 0 12px; page-break-inside: avoid; }
        thead { display: table-header-group; }
        .report-table th, .report-table td { border: 1px solid #94a3b8; padding: 4px; vertical-align: top; word-wrap: break-word; }
        .report-table th { background: #28449a; color: #fdfdff; font-size: 8px; }
        .report-table tr { page-break-inside: avoid; }
        .report-table .teacher { background: #edf1fb; color: #28449a; text-align: left; font-size: 10px; }
        .report-table .metadata { background: #fdfdff; color: #303237; text-align: left; font-weight: normal; }
        .total td { background: #edf1fb; font-weight: bold; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .summary { margin-top: 12px; padding: 10px; background: #edf1fb; page-break-inside: avoid; }
        .note { color: #6b7280; font-size: 8px; }
        @include('components.pdf.header-styles')
    </style>
</head>
<body>
    <x-pdf.header :institucion="$institucion" :titulo="$titulo" />
    <div class="footer"><table class="footer-table"><tr>
        <td class="footer-left"></td><td class="footer-center">INTEGRAEDU360 · SOFFIWEB</td><td class="footer-right"></td>
    </tr></table></div>
    <div class="filters"><strong>Período académico:</strong> {{ $periodo->nombre }}
        @if($busqueda !== '') · Búsqueda: {{ $busqueda }}@endif
        <br>Períodos semanales de clase: máximo 25 por docente. EGB: 30 · Ciencias: 35 · Técnico: 40 por paralelo.
        <br>Las actividades y comisiones se presentan por separado y no se suman a la carga pedagógica.
    </div>
    @forelse($grupos as $grupo)
        <table class="report-table">
            <thead>
                <tr><th colspan="6" class="teacher">{{ $loop->iteration }}. {{ $grupo['docente']->persona ? $grupo['docente']->nombre_completo : $grupo['docente']->username }}</th></tr>
                <tr><th colspan="6" class="metadata">Identificación: {{ $grupo['docente']->persona?->numero_identificacion ?: 'Sin registro' }} · Estado: {{ $grupo['docente']->estado }} · Período: {{ $periodo->nombre }}</th></tr>
                <tr><th style="width:5%">Ord</th><th style="width:29%">Asignatura</th><th style="width:23%">Curso</th><th style="width:9%">Paralelo</th><th style="width:24%">Oferta / especialidad</th><th style="width:10%">Períodos / semana</th></tr>
            </thead>
            <tbody>
                @forelse($grupo['cargas'] as $carga)
                    <tr><td class="num">{{ $loop->iteration }}</td><td>{{ $carga->asignatura?->nombre ?? $carga->titulo }}</td><td>{{ $carga->curso?->nombre ?? 'Sin curso' }}</td><td>{{ $carga->paralelo?->letra ?? 'Sin paralelo' }}</td><td>{{ $carga->curso?->especialidad?->nombre ?? ($carga->curso ? 'Educación General Básica' : 'Sin oferta') }}</td><td class="num">{{ number_format((float) $carga->horas_semanales, 2, ',', '.') }}</td></tr>
                @empty
                    <tr><td colspan="6">Sin carga pedagógica asignada en este período.</td></tr>
                @endforelse
                <tr class="total"><td colspan="5">Total de períodos pedagógicos semanales</td><td class="num">{{ number_format($grupo['horas'], 2, ',', '.') }}</td></tr>
                <tr><td colspan="6"><strong>Comisiones institucionales ({{ $grupo['comisiones']->count() }} / máximo 2)</strong><br>
                    @forelse($grupo['comisiones'] as $comision)
                        {{ $loop->iteration }}. {{ $comision->titulo }}<br>
                    @empty Sin comisiones asignadas.<br>@endforelse
                    <span class="note">No computan períodos pedagógicos de clase.</span>
                </td></tr>
                @foreach($grupo['actividades'] as $actividad)
                    <tr><td class="num">{{ $loop->iteration }}</td><td colspan="5"><strong>Actividad:</strong> {{ $actividad->titulo }} · Dedicación registrada: {{ number_format((float) $actividad->horas_semanales, 2, ',', '.') }} h/semana. No incluida en el total pedagógico.</td></tr>
                @endforeach
                <tr><td colspan="6">Resumen del docente: {{ $grupo['asignaturas'] }} asignaturas · {{ $grupo['paralelos'] }} paralelos · {{ $grupo['cargas']->count() }} asignaciones de clase · {{ $grupo['comisiones']->count() }} comisiones · {{ $grupo['actividades']->count() }} actividades.</td></tr>
            </tbody>
        </table>
    @empty
        <p>No hay docentes con los filtros actuales.</p>
    @endforelse
    <div class="summary">
        <strong>Resumen estadístico{{ $individual ? ' individual' : ' general' }}</strong>
        <p>Docentes: {{ $resumen['docentes'] }} · Con carga: {{ $resumen['con_carga'] }} · Sin carga: {{ $resumen['docentes'] - $resumen['con_carga'] }}</p>
        <p>Total de períodos pedagógicos semanales: {{ number_format($resumen['horas'], 2, ',', '.') }} · Promedio por docente: {{ number_format($resumen['promedio'], 2, ',', '.') }} · Máximo asignado: {{ number_format($resumen['maximo'], 2, ',', '.') }}</p>
        <p>Asignaciones de clase: {{ $resumen['asignaciones'] }} · Cursos: {{ $resumen['cursos'] }} · Paralelos distintos: {{ $resumen['paralelos'] }} · Participaciones en comisiones: {{ $resumen['comisiones'] }} · Actividades: {{ $resumen['actividades'] }}</p>
        <span class="note">Resumen calculado únicamente sobre el período y los docentes incluidos en este reporte.</span>
    </div>
</body>
</html>
