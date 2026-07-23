<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            margin: 0;
            padding: 1.5cm;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.4;
        }

        /* ── Cabecera repetida en cada página (a partir de la 2ª) ── */
        .running-header {
            position: fixed;
            top: 0.5cm;
            left: 1.5cm;
            right: 1.5cm;
            height: 14mm;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3mm;
            display: table;
            width: auto;
        }
        .running-header .rh-left {
            display: table-cell;
            vertical-align: bottom;
            font-size: 7.5px;
            font-weight: bold;
            color: #152b74;
        }
        .running-header .rh-right {
            display: table-cell;
            vertical-align: bottom;
            text-align: right;
            font-size: 7.5px;
            color: #94a3b8;
        }

        /* ── Pie de página repetido en cada página ── */
        .page-footer {
            position: fixed;
            bottom: 0.5cm;
            left: 1.5cm;
            right: 1.5cm;
            height: 16mm;
            border-top: 1px solid #e2e8f0;
            padding-top: 3mm;
            display: table;
            width: 100%;
        }
        .page-footer .pf-left {
            display: table-cell;
            vertical-align: top;
            width: 33.33%;
            text-align: left;
            font-size: 7.5px;
            color: #94a3b8;
        }
        .page-footer .pf-center {
            display: none;
        }
        .page-footer .pf-right {
            display: none;
        }

        /* Contadores de página DomPDF */

        /* ── Encabezado del documento (sólo primera página) ── */
        .page-header {
            width: 100%;
            display: table;
            border-bottom: 2px solid #152b74;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .page-header .brand-block,
        .page-header .info-block {
            display: table-cell;
            vertical-align: middle;
        }
        .page-header .brand-block {
            width: 30%;
            padding-right: 12px;
        }
        .page-header .info-block {
            width: 70%;
        }
        .page-header .logo-slot {
            min-height: 58px;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            background: #f8fafc;
            color: #94a3b8;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: center;
            line-height: 58px;
        }
        .page-header .info-block {
            text-align: left;
        }
        .page-header .title {
            font-size: 16px;
            font-weight: bold;
            color: #152b74;
            letter-spacing: 0.03em;
        }
        .page-header .subtitle {
            font-size: 10px;
            color: #475569;
            margin-top: 2px;
        }
        .meta {
            margin-top: 4px;
            font-size: 8px;
            color: #64748b;
        }

        .esp-header {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 5px 8px;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-top: 14px;
            margin-bottom: 0;
        }

        .curso-header {
            background-color: #e2e8f0;
            color: #1e293b;
            padding: 4px 8px 4px 16px;
            font-size: 8.5px;
            font-weight: bold;
            border-bottom: 1px solid #cbd5e1;
        }
        .curso-header .grado {
            font-weight: normal;
            color: #64748b;
            margin-left: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead tr th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 4px 6px;
            text-align: left;
            border-bottom: 1px solid #cbd5e1;
        }
        tbody tr td {
            padding: 4px 6px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 8.5px;
            vertical-align: top;
        }
        tbody tr:last-child td { border-bottom: 1px solid #e2e8f0; }

        .name  { font-weight: bold; color: #0f172a; }
        .mono  { font-family: DejaVu Sans Mono, monospace; font-size: 8px; }
        .muted { color: #94a3b8; }

        .badge          { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 7.5px; font-weight: bold; }
        .badge-activo   { background: #dcfce7; color: #166534; }
        .badge-inactivo { background: #fee2e2; color: #991b1b; }

        .summary-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 14px;
            font-size: 8.5px;
            color: #475569;
        }
        .summary-bar strong { color: #152b74; }
        .document-content {
            padding-top: 12mm;
            padding-bottom: 14mm;
        }
    </style>
</head>
<body>

    {{-- Cabecera repetida en páginas 2+ --}}
    <div class="running-header">
        <span class="rh-left">Catálogo de Asignaturas{{ $institucion ? ' — ' . $institucion->nombre : '' }}</span>
        <span class="rh-right">Año lectivo: {{ $anioLectivo }}</span>
    </div>

    {{-- Pie de página con numeración --}}
    <div class="page-footer">
        <span class="pf-left">IntegraEdu360</span>
        <span class="pf-center">Página <span class="page-num"></span> de <span class="page-total"></span></span>
        <span class="pf-right">PÃ¡gina <span class="page-num"></span> de <span class="page-total"></span></span>
    </div>

    <div class="document-content">
    <div class="page-header">
        <div class="brand-block">
            <div class="logo-slot">Logotipo instituciÃ³n</div>
        </div>
        <div class="info-block">
        <div class="title">Catálogo de Asignaturas</div>
        @if ($institucion)
            <div class="subtitle">{{ $institucion->nombre }}</div>
        @endif
        <div class="meta">Año lectivo: <strong>{{ $anioLectivo }}</strong></div>
        </div>
    </div>

    <div class="summary-bar">
        Total de asignaturas: <strong>{{ $totalAsignaturas }}</strong>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Especialidades: <strong>{{ count($asignaturasAgrupadas) }}</strong>
    </div>

    @foreach ($asignaturasAgrupadas as $especialidadNombre => $porCurso)
        <div class="esp-header">{{ $especialidadNombre }}</div>

        @foreach ($porCurso as $cursoGroup)
            <div class="curso-header">
                @if ($cursoGroup['curso'])
                    {{ $cursoGroup['curso']->nombre }}
                    <span class="grado">— Grado {{ $cursoGroup['curso']->grado }}°</span>
                @else
                    <span class="muted">Sin curso asignado</span>
                @endif
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width:32%;">Asignatura</th>
                        <th style="width:22%;">Área</th>
                        <th style="width:10%;">Código</th>
                        <th style="width:10%;">Hrs/sem.</th>
                        <th style="width:14%;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cursoGroup['items'] as $asignatura)
                        <tr>
                            <td>
                                <span class="name">{{ $asignatura->nombre }}</span>
                            </td>
                            <td>{{ $areas[$asignatura->area] ?? $asignatura->area }}</td>
                            <td>
                                @if ($asignatura->codigo)
                                    <span class="mono">{{ $asignatura->codigo }}</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($asignatura->horas_semana)
                                    <span class="mono">{{ $asignatura->horas_semana }}</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ strtolower($asignatura->estado) }}">
                                    {{ $estados[$asignatura->estado] ?? $asignatura->estado }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endforeach
    </div>

</body>
</html>
