<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de calificaciones</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #303237; margin: 0; }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; border-top: 1px solid #cbd5e1; font-size: 7px; color: #6b7280; }
        .footer-left, .footer-right { width: 20%; }
        .footer-center { width: 60%; text-align: center; }
        .context { margin-bottom: 10px; font-size: 9px; }
        .context p { margin: 3px 0; }
        .report-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 12px; }
        thead { display: table-header-group; }
        .report-table th, .report-table td { border: 1px solid #94a3b8; padding: 3px; vertical-align: top; word-wrap: break-word; }
        .report-table th { background: #28449a; color: #fdfdff; font-size: 7px; }
        .report-table tbody tr { page-break-inside: avoid; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .strong { font-weight: bold; }
        .statistics { padding: 10px; background: #edf1fb; page-break-inside: avoid; }
        .statistics h3 { margin: 0 0 6px; color: #28449a; }
        .statistics p { margin: 4px 0; }
        .signatures { width: 40%; margin: 40px auto 0; text-align: center; border-top: 1px solid #303237; padding-top: 4px; page-break-inside: avoid; }
        @include('components.pdf.header-styles')
    </style>
</head>
<body>
    @php
        $anual = $trimestre === 'anual';
        $cuantitativa = $tipo === 'cuantitativa';
        $formato = fn ($valor) => \App\Services\Docente\RegistroCalificaciones::formato($valor);
        $nombreDocente = $docente->persona ? $docente->nombre_completo : $docente->username;
    @endphp
    <x-pdf.header :institucion="$institucion" titulo="Registro de calificaciones" />
    <div class="footer"><table class="footer-table"><tr>
        <td class="footer-left"></td><td class="footer-center">INTEGRAEDU360 · SOFFIWEB</td><td class="footer-right"></td>
    </tr></table></div>
    <div class="context">
        <p><strong>Período académico:</strong> {{ $periodo->nombre }} · <strong>{{ $anual ? 'Consolidado anual' : $trimestres[$trimestre] }}</strong></p>
        <p><strong>Asignatura:</strong> {{ $carga->asignatura?->nombre ?? $carga->titulo }} · <strong>Curso:</strong> {{ $carga->curso->nombre }} · <strong>Paralelo:</strong> {{ $carga->paralelo->letra }} · <strong>Especialidad:</strong> {{ $carga->curso->especialidad?->nombre ?? 'Educación General Básica' }}</p>
        <p><strong>Docente:</strong> {{ $nombreDocente }}</p>
    </div>
    <table class="report-table">
        <thead><tr>
            <th style="width: 3%">Ord</th><th style="width: 9%">Identificación</th><th style="width: 22%">Apellidos y nombres</th>
            @if($anual)
                @foreach($trimestres as $nombre)<th>{{ $nombre }}</th>@endforeach
                <th>Promedio final</th><th>Situación</th>
            @elseif($cuantitativa)
                @for($i = 1; $i <= $columnas; $i++)<th>A{{ $i }}</th>@endfor
                <th>Formativa 70 %</th><th>Sumativa</th>
                @if($mejoraDisponible)<th>Mejora</th>@endif
                <th>Sumativa 30 %</th><th>Nota</th><th>Alerta</th>
            @else
                <th>Valoración</th><th>Alerta</th>
            @endif
        </tr></thead>
        <tbody>
            @forelse($filas as $fila)
                <tr>
                    <td class="num">{{ $loop->iteration }}</td><td>{{ $fila['identificacion'] }}</td><td>{{ $fila['nombre'] }}</td>
                    @if($anual)
                        @foreach($fila['notas'] as $nota)<td class="num">{{ $nota }}</td>@endforeach
                        <td class="num strong">{{ $fila['promedio_texto'] }}</td><td>{{ $fila['situacion'] }}</td>
                    @elseif($cuantitativa)
                        @for($i = 0; $i < $columnas; $i++)<td class="num">{{ isset($fila['aportes'][$i]) ? number_format((float) $fila['aportes'][$i], 2, ',', '.') : '' }}</td>@endfor
                        <td class="num">{{ $formato($fila['formativa']) }}</td>
                        <td class="num">{{ $fila['registro']?->sumativa !== null ? number_format((float) $fila['registro']->sumativa, 2, ',', '.') : '' }}</td>
                        @if($mejoraDisponible)<td class="num">{{ $fila['registro']?->sumativa_mejora !== null ? number_format((float) $fila['registro']->sumativa_mejora, 2, ',', '.') : '' }}</td>@endif
                        <td class="num">{{ $formato($fila['sumativa_final']) }}</td>
                        <td class="num strong">{{ $fila['nota_texto'] }}</td><td>{{ $fila['alerta'] ? 'Sí' : '' }}</td>
                    @else
                        <td class="strong">{{ $fila['nota_texto'] }}</td><td>{{ $fila['alerta'] ? 'Sí' : '' }}</td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ $anual ? 8 : ($cuantitativa ? 8 + $columnas + ($mejoraDisponible ? 1 : 0) : 5) }}">No hay estudiantes con matrícula activa en este paralelo.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="statistics">
        <h3>Resumen estadístico</h3>
        <p>Estudiantes: <strong>{{ $resumen['estudiantes'] }}</strong> · Con {{ $anual ? 'promedio final' : 'nota' }}: <strong>{{ $resumen['con_nota'] }}</strong>@unless($anual) · Con alerta temprana: <strong>{{ $resumen['alertas'] }}</strong>@endunless</p>
        @if($cuantitativa)
            <p>Promedio del paralelo: <strong>{{ $resumen['promedio'] }}</strong> · 7,00 o más: <strong>{{ $resumen['sobre_minimo'] }}</strong> · Menos de 7,00: <strong>{{ $resumen['bajo_minimo'] }}</strong></p>
            <p>Acuerdo MINEDEC-MINEDEC-2026-00082-A: nota = formativa × 70 % + sumativa × 30 %; promedio final = promedio simple de los tres trimestres; dos decimales truncados.</p>
        @else
            <p>Destreza o aprendizaje alcanzado: <strong>{{ $resumen['alcanzado'] }}</strong> · En proceso de desarrollo: <strong>{{ $resumen['en_proceso'] }}</strong> · Iniciado: <strong>{{ $resumen['iniciado'] }}</strong></p>
            <p>Acuerdo MINEDEC-MINEDEC-2026-00082-A, arts. 7 y 35: escala cualitativa y promedio simple de los tres trimestres.</p>
        @endif
    </div>
    <div class="signatures">{{ $nombreDocente }}<br>Docente · Firma</div>
</body>
</html>
