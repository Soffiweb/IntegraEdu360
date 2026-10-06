<?php

namespace App\Services\Admin;

use App\Models\Institucion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ActualizarDatosInstitucion
{
    public function guardar(Institucion $institucion, array $datos): void
    {
        $nuevos = [];
        $anteriores = [];
        try {
            foreach (['logo1', 'logo2'] as $campo) {
                $archivo = $datos[$campo] ?? null;
                unset($datos[$campo]);
                if (! $archivo) {
                    continue;
                }
                $ruta = $archivo->store('instituciones/'.$institucion->id.'/logos', 'public');
                if (! $ruta) {
                    throw new RuntimeException('No se pudo guardar el logotipo.');
                }
                $nuevos[] = $ruta;
                $anteriores[] = $institucion->{$campo};
                $datos[$campo] = $ruta;
            }
            DB::transaction(fn () => $institucion->update($datos));
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($nuevos);
            throw $exception;
        }
        foreach (array_filter($anteriores) as $ruta) {
            if (str_starts_with($ruta, 'instituciones/'.$institucion->id.'/logos/')) {
                Storage::disk('public')->delete($ruta);
            }
        }
    }
}
