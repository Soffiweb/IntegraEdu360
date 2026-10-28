<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de estudiantes por curso y paralelo</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #303237; margin: 0; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; border-top: 1px solid #cbd5e1; font-size: 7px; color: #6b7280; }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-left, .footer-right { width: 20%; }
        .footer-center { width: 60%; text-align: center; }
        .context { margin-bottom: 12px; font-size: 9px; }
        .context p { margin: 4px 0; }
        .report-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 12px; }
        thead { display: table-header-group; }
        .report-table th, .report-table td { border: 1px solid #94a3b8; padding: 5px; vertical-align: top; word-wrap: break-word; }
        .report-table th { background: #28449a; color: #fdfdff; font-size: 8px; }
        .report-table tbody tr { page-break-inside: avoid; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .statistics { padding: 10px; background: #edf1fb; page-break-inside: avoid; }
        .statistics h3 { margin: 0 0 6px; color: #28449a; }
        .statistics p { margin: 4px 0; }
        @include('components.pdf.header-styles')
    </style>
</head>
<body>
    <x-pdf.header :institucion="$institucion" titulo="Listado de estudiantes por curso y paralelo" />
    <div class="footer"><table class="footer-table"><tr>
        <td class="footer-left"></td><td class="footer-center">INTEGRAEDU360 · SOFFIWEB</td><td class="footer-right"></td>
    </tr></table></div>
    <div class="context">
        <p><strong>Período:</strong> {{ $periodo->nombre }}</p>
        <p><strong>Curso:</strong> {{ $carga->curso->nombre }} · <strong>Paralelo:</strong> {{ $carga->paralelo->letra }} · <strong>Especialidad:</strong> {{ $carga->curso->especialidad?->nombre ?? 'Educación General Básica' }}</p>
        <p><strong>Docente:</strong> {{ $docente->persona ? $docente->nombre_completo : $docente->username }}</p>
        <p><strong>Asignatura:</strong> {{ $carga->asignatura?->nombre ?? $carga->titulo }} · Matrículas activas</p>
    </div>
    <table class="report-table">
        <thead><tr>
            <th style="width: 5%">Ord</th><th style="width: 17%">Identificación</th>
            <th style="width: 34%">Apellidos y nombres</th>
            <th style="width: 6%">&nbsp;</th><th style="width: 6%">&nbsp;</th>
            <th style="width: 6%">&nbsp;</th><th style="width: 6%">&nbsp;</th>
            <th style="width: 20%">Observaciones</th>
        </tr></thead>
        <tbody>
            @forelse ($matriculas as $matricula)
                @php $persona = $matricula->estudiante->persona; @endphp
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td>{{ $persona?->numero_identificacion ?: 'Sin registro' }}</td>
                    <td>{{ $persona ? trim(implode(' ', array_filter([$persona->primer_apellido, $persona->segundo_apellido, $persona->primer_nombre, $persona->segundo_nombre]))) : $matricula->estudiante->username }}</td>
                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                </tr>
            @empty
                <tr><td colspan="8">No hay estudiantes con matrícula activa en este curso y paralelo para el período seleccionado.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="statistics">
        <h3>Resumen estadístico</h3>
        <p>Total de estudiantes: <strong>{{ $resumen['total'] }}</strong></p>
        <p>Masculino: <strong>{{ $resumen['masculino'] }}</strong> · Femenino: <strong>{{ $resumen['femenino'] }}</strong> · Otro o sin registro: <strong>{{ $resumen['otros'] }}</strong></p>
    </div>
</body>
</html>
