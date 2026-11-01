<?php

namespace App\Http\Controllers\PadreTutor;

use App\Http\Controllers\Controller;
use App\Models\AlertaAsistencia;
use App\Services\Docente\BoletaAlertaAsistencia;
use Illuminate\Http\Request;

class AlertaAsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $alertas = AlertaAsistencia::where('representante_id', $request->session()->get('auth_user.id'))
            ->with(['matricula.estudiante.persona', 'matricula.paralelo.curso'])
            ->orderByRaw('CASE WHEN resuelta_at IS NULL THEN 0 ELSE 1 END')->orderByDesc('created_at')->orderByDesc('id')->paginate(20);

        return view('dashboard-role', compact('alertas') + [
            'roleKey' => 'padre-tutor', 'role' => integraEduRoles()['padre-tutor'],
            'dashboard' => integraEduDashboards()['padre-tutor'], 'dashboardSection' => 'alertas-asistencia',
        ]);
    }

    public function boleta(Request $request, BoletaAlertaAsistencia $boleta, int $alerta)
    {
        $representante = $request->session()->get('auth_user.id');
        $fila = AlertaAsistencia::where('representante_id', $representante)->whereNull('resuelta_at')
            ->whereHas('matricula.estudiante', fn ($q) => $q->where('representante_id', $representante))->findOrFail($alerta);

        return $boleta->pdf($fila->matricula);
    }
}
