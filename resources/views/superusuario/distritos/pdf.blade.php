<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Distritos educativos — {{ $zona->nombre }}</title>
    <style>
        @page { margin: 92px 32px 54px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #303237; margin: 0; }
        .footer-table, .report-table { width: 100%; border-collapse: collapse; }
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
        @include('components.pdf.header-styles')
    </style>
</head>
<body>
    <x-pdf.header :institucion="$institucion ?? null" titulo="Distritos educativos" :nombre-institucion="'IntegraEdu360 — '.$zona->nombre" />
    <div class="footer">
        <table class="footer-table"><tr>
            <td class="footer-left"></td>
            <td class="footer-center">SOFFICAJA &nbsp;·&nbsp; SOFFIWEB</td>
            <td class="footer-right"></td>
        </tr></table>
    </div>
    <table class="report-table">
        <thead><tr>
            <th style="width: 6%">Ord</th>
            <th style="width: 12%">Código</th>
            <th style="width: 23%">Provincia</th>
            <th style="width: 59%">Nombre del distrito</th>
        </tr></thead>
        <tbody>
            @forelse ($distritos as $distrito)
                <tr><td class="num">{{ $loop->iteration }}</td><td>{{ $distrito->codigo }}</td><td>{{ $distrito->provincia }}</td><td>{{ $distrito->nombre }}</td></tr>
            @empty
                <tr><td colspan="4">No hay distritos registrados para esta zona educativa.</td></tr>
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
