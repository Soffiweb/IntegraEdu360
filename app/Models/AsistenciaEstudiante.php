<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaEstudiante extends Model
{
    public const ESTADOS = ['P' => 'Presente', 'F' => 'Falta', 'A' => 'Atraso', 'J' => 'Justificada'];

    protected $table = 'asistencias_estudiantes';

    protected $fillable = ['sesion_asistencia_id', 'matricula_id', 'estado'];
}
