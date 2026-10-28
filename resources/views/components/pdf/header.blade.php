<div class="pdf-header">
    <table><tr>
        <td class="pdf-logo-left">@if ($logo1Path)<img src="{{ $logo1Path }}" alt="Logotipo 1">@endif</td>
        <td class="pdf-heading">
            <div class="pdf-institution">{{ $institucion?->nombre ?? $nombreInstitucion ?? 'IntegraEdu360' }}</div>
            <div class="pdf-slogan">{{ $institucion?->slogan }}</div>
            <div class="pdf-report-title">{{ $titulo }}</div>
            <div class="pdf-date">{{ $fecha }}</div>
        </td>
        <td class="pdf-logo-right">@if ($logo2Path)<img src="{{ $logo2Path }}" alt="Logotipo 2">@endif</td>
    </tr></table>
</div>
