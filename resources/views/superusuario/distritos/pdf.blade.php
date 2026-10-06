<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Distritos educativos — {{ $zona->nombre }}</title>
    <style>
        @page { margin: 92px 32px 54px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #303237; margin: 0; }
        .header { position: fixed; top: -70px; left: 0; right: 0; height: 66px; border-bottom: 1px solid #28449a; padding: 0 4px 10px; }
        .header-table, .footer-table, .report-table { width: 100%; border-collapse: collapse; }
        .header-logo { width: 20%; vertical-align: middle; padding-right: 10px; }
        .header-logo img { max-width: 104px; max-height: 50px; }
        .header-center { width: 56%; text-align: center; vertical-align: middle; }
        .pdf-title { font-size: 13px; font-weight: bold; color: #28449a; margin-bottom: 3px; }
        .pdf-empresa { font-size: 10px; font-weight: bold; }
        .pdf-direccion { font-size: 8px; color: #6b7280; }
        .header-meta { width: 24%; text-align: right; vertical-align: middle; font-size: 8px; }
        .footer { position: fixed; bottom: -34px; left: 0; right: 0; border-top: 1px solid #edf1fb; padding-top: 8px; font-size: 8px; color: #6b7280; }
        .footer-left, .footer-right { width: 25%; }
        .footer-center { width: 50%; text-align: center; }
        thead { display: table-header-group; }
        th, td { padding: 7px 6px; word-wrap: break-word; vertical-align: top; border-bottom: 1px solid #edf1fb; }
        th { background: #28449a; color: #fdfdff; text-align: left; }
        tr { page-break-inside: avoid; }
        .report-table { table-layout: fixed; }
        .summary { margin-top: 14px; padding: 10px; background: #edf1fb; page-break-inside: avoid; }
        .summary p { margin: 4px 0; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table"><tr>
            <td class="header-logo">
                @if ($logoPath)
                    <img src="{{ $logoPath }}" alt="IntegraEdu360">
                @endif
            </td>
            <td class="header-center">
                <div class="pdf-title">Distritos educativos</div>
                <div class="pdf-empresa">IntegraEdu360 — {{ $zona->nombre }}</div>
                <div class="pdf-direccion">{{ $zona->cobertura }}</div>
            </td>
            <td class="header-meta">
                <div><strong>Fecha:</strong> {{ $generadoEn->format('d/m/Y') }}</div>
                <div><strong>Hora:</strong> {{ $generadoEn->format('H:i') }}</div>
                <div>Hora de Ecuador</div>
            </td>
        </tr></table>
    </div>
    <div class="footer">
        <table class="footer-table"><tr>
            <td class="footer-left"></td>
            <td class="footer-center">SOFFICAJA &nbsp;·&nbsp; SOFFIWEB</td>
            <td class="footer-right"></td>
        </tr></table>
    </div>
    <table class="report-table">
        <thead><tr>
            <th style="width: 12%">Código</th>
            <th style="width: 23%">Provincia</th>
            <th style="width: 65%">Nombre del distrito</th>
        </tr></thead>
        <tbody>
            @forelse ($distritos as $distrito)
                <tr><td>{{ $distrito->codigo }}</td><td>{{ $distrito->provincia }}</td><td>{{ $distrito->nombre }}</td></tr>
            @empty
                <tr><td colspan="3">No hay distritos registrados para esta zona educativa.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="summary">
        <strong>Resumen estadístico</strong>
        <p>Total: {{ $distritos->count() }} distritos · {{ $porProvincia->count() }} provincias.</p>
        @foreach ($porProvincia as $provincia => $total)
            <p>{{ $provincia }}: {{ $total }} {{ $total == 1 ? 'distrito' : 'distritos' }}.</p>
        @endforeach
    </div>
</body>
</html>
