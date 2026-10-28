<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HorarioPersonalController extends Controller
{
    public function index(Request $request)
    {
        $docente = Usuario::findOrFail($request->session()->get('auth_user.id'));
        abort_unless($docente->institucion_id, 403, 'La cuenta debe tener una institución asignada.');
        $bloques = DB::table('horario_docente as h')
            ->join('asignaturas as a', 'a.id', '=', 'h.asignatura_id')
            ->join('cursos as c', 'c.id', '=', 'h.curso_id')
            ->where('h.usuario_id', $docente->id)
            ->where('a.institucion_id', $docente->institucion_id)
            ->where('c.institucion_id', $docente->institucion_id)
            ->orderBy('h.hora_inicio')->orderBy('h.hora_fin')->orderBy('h.id')
            ->get(['h.dia_semana', 'h.hora_inicio', 'h.hora_fin', 'h.aula', 'a.nombre as asignatura', 'c.nombre as curso']);
        $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes'];
        foreach ([6 => 'Sábado', 7 => 'Domingo'] as $numero => $nombre) {
            if ($bloques->contains('dia_semana', $numero)) {
                $dias[$numero] = $nombre;
            }
        }
        $franjas = $bloques->groupBy(fn ($bloque) => $bloque->hora_inicio.'–'.$bloque->hora_fin)
            ->map(fn ($grupo) => [
                'inicio' => substr($grupo->first()->hora_inicio, 0, 5),
                'fin' => substr($grupo->first()->hora_fin, 0, 5),
                'dias' => $grupo->groupBy('dia_semana'),
            ]);

        return view('dashboard-role', compact('dias', 'franjas') + [
            'roleKey' => 'docente',
            'role' => integraEduRoles()['docente'],
            'dashboard' => integraEduDashboards()['docente'],
            'dashboardSection' => 'horario-personal',
        ]);
    }
}
