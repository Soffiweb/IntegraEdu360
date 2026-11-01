<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de notificación de asistencia</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #303237; margin: 0; }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; border-top: 1px solid #cbd5e1; font-size: 7px; color: #6b7280; }
        .footer-left, .footer-right { width: 20%; }
        .footer-center { width: 60%; text-align: center; }
        .context { margin-bottom: 12px; }
        .context p { margin: 4px 0; }
        .message { margin-bottom: 12px; line-height: 1.5; }
        .message p { margin: 0 0 6px; }
        .report-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 12px; }
        thead { display: table-header-group; }
        .report-table th, .report-table td { border: 1px solid #94a3b8; padding: 5px; vertical-align: top; word-wrap: break-word; }
        .report-table th { background: #28449a; color: #fdfdff; font-size: 8px; }
        .section-title { margin: 0 0 4px; color: #28449a; font-size: 10px; font-weight: bold; }
        .report-table tbody tr { page-break-inside: avoid; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .statistics { padding: 10px; background: #edf1fb; page-break-inside: avoid; }
        .statistics h3 { margin: 0 0 6px; color: #28449a; }
        .statistics p { margin: 4px 0; }
        .signatures { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 48px; page-break-inside: avoid; }
        .signatures td { width: 50%; padding: 0 24px; text-align: center; vertical-align: top; }
        .signature-line { border-top: 1px solid #303237; padding-top: 4px; }
        @include('components.pdf.header-styles')
    </style>
</head>
<body>
    @php
        $porCentaje = fn ($valor) => number_format($valor, 2, ',', '.').' %';
        $estudiante = $matricula->estudiante;
        $representante = $estudiante->representante;
        $curso = $matricula->paralelo->curso;
        $nombreEstudiante = $estudiante->persona ? $estudiante->nombre_completo : $estudiante->username;
        $nombreRepresentante = $representante ? ($representante->persona ? $representante->nombre_completo : $representante->username) : 'Sin representante registrado';
    @endphp
    <x-pdf.header :institucion="$institucion" titulo="Boleta de notificación de asistencia" />
    <div class="footer"><table class="footer-table"><tr>
        <td class="footer-left"></td><td class="footer-center">INTEGRAEDU360 · SOFFIWEB</td><td class="footer-right"></td>
    </tr></table></div>
    <div class="context">
        <p><strong>Representante:</strong> {{ $nombreRepresentante }}</p>
        <p><strong>Estudiante:</strong> {{ $nombreEstudiante }} · <strong>Identificación:</strong> {{ $estudiante->persona?->numero_identificacion ?: 'Sin registro' }}</p>
        <p><strong>Curso:</strong> {{ $curso->nombre }} · <strong>Paralelo:</strong> {{ $matricula->paralelo->letra }} · <strong>Especialidad:</strong> {{ $curso->especialidad?->nombre ?? 'Educación General Básica' }}</p>
        <p><strong>Período académico:</strong> {{ $periodo->nombre }}</p>
    </div>
    <div class="message">
        <p>Estimado/a representante:</p>
        <p>Por medio de la presente, se le notifica que la asistencia de <strong>{{ $nombreEstudiante }}</strong> en el período académico es de <strong>{{ $porCentaje($asistencia['porcentaje']) }}</strong>, por debajo del {{ number_format($umbral, 0) }} % esperado.</p>
        <p>Le pedimos revisar la situación del estudiante y comunicarse con la institución si necesita apoyo.</p>
    </div>
    <div class="section-title">Asistencia por asignatura</div>
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 7%">Ord</th><th>Asignatura</th>
                @foreach($estados as $etiqueta)<th style="width: 11%">{{ $etiqueta }}</th>@endforeach
                <th style="width: 13%">Asistencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($asignaturas as $asignatura)
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td>{{ $asignatura['nombre'] }}</td>
                    @foreach($estados as $codigo => $etiqueta)<td class="num">{{ $asignatura['estados'][$codigo] }}</td>@endforeach
                    <td class="num">{{ $porCentaje($asignatura['porcentaje']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="statistics">
        <h3>Resumen estadístico</h3>
        <p>Asignaturas: <strong>{{ $asignaturas->count() }}</strong> · Registros de asistencia: <strong>{{ $asistencia['registros'] }}</strong></p>
        <p>@foreach($estados as $codigo => $etiqueta){{ $etiqueta }}: <strong>{{ $asistencia['estados'][$codigo] }}</strong>@unless($loop->last) · @endunless @endforeach</p>
        <p>Asistencia del período: <strong>{{ $porCentaje($asistencia['porcentaje']) }}</strong></p>
        <p>La asistencia considera todas las clases del estudiante en el período, de todas las asignaturas. Presentes y atrasados cuentan como asistencia; faltas y justificadas, no.</p>
    </div>
    <table class="signatures"><tr>
        <td><div class="signature-line">Por la institución<br>Firma y sello</div></td>
        <td><div class="signature-line">{{ $nombreRepresentante }}<br>Representante · Firma de recibido</div></td>
    </tr></table>
</body>
</html>
