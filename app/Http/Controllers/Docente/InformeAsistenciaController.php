<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Matricula;
use App\Models\Usuario;
use App\Services\Docente\BoletaAlertaAsistencia;
use App\Services\Docente\InformesAsistencia;
use App\Services\Docente\RegistroAsistencia;
use Illuminate\Http\Request;

class InformeAsistenciaController extends Controller
{
    public function index(Request $request, InformesAsistencia $informes)
    {
        $request->validate(RegistroAsistencia::REGLAS_FILTROS);

        return view('dashboard-role', $informes->datos($request) + [
            'roleKey' => 'docente', 'role' => integraEduRoles()['docente'],
            'dashboard' => integraEduDashboards()['docente'], 'dashboardSection' => 'informes-asistencia',
        ]);
    }

    public function boleta(Request $request, BoletaAlertaAsistencia $boleta, int $matricula)
    {
        $docente = Usuario::findOrFail($request->session()->get('auth_user.id'));
        abort_unless($docente->institucion_id, 403);
        $fila = Matricula::where('institucion_id', $docente->institucion_id)->findOrFail($matricula);
        abort_unless(AsignacionDocente::where('docente_id', $docente->id)->where('institucion_id', $docente->institucion_id)
            ->where('periodo_id', $fila->periodo_id)->where('paralelo_id', $fila->paralelo_id)
            ->where('tipo', 'carga')->where('horas_semanales', '>', 0)->exists(), 404);

        return $boleta->pdf($fila);
    }
}
