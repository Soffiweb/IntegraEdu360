<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\Usuario;
use App\Services\Admin\ResumenInstitucional;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ResumenController extends Controller
{
    public function index(Request $request, ResumenInstitucional $service): View
    {
        $sesion = $request->session()->get('auth_user', []);
        $institucionId = ! empty($sesion['id'])
            ? Usuario::find($sesion['id'])?->institucion_id : ($sesion['institucion_id'] ?? null);
        $institucion = $institucionId ? Institucion::find($institucionId) : null;

        return view('admin.dashboard', [
            'institucion' => $institucion,
            'resumen' => $institucion ? $service->datos($institucion) : null,
        ]);
    }
}
