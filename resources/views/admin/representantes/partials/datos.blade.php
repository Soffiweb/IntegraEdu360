@foreach ($representantes as $representante)
    @php
        $persona = $representante->persona;
        $datos = [
            'Número de identificación' => $persona?->numero_identificacion,
            'Nombres' => trim(implode(' ', array_filter([$persona?->primer_nombre, $persona?->segundo_nombre]))),
            'Apellidos' => trim(implode(' ', array_filter([$persona?->primer_apellido, $persona?->segundo_apellido]))),
            'Fecha de nacimiento' => $persona?->fecha_nacimiento,
            'Sexo' => $sexos[$persona?->sexo] ?? null,
            'Teléfono' => $persona?->telefono,
            'Celular' => $persona?->celular,
            'Correo personal' => $persona?->email,
            'Provincia' => $persona?->provincia,
            'Cantón' => $persona?->canton,
            'Parroquia' => $persona?->parroquia,
            'Dirección' => $persona?->direccion,
            'Institución' => $representante->institucion?->nombre,
            'Usuario' => $representante->username,
            'Correo de acceso' => $representante->email,
            'Estado' => $estados[$representante->estado] ?? $representante->estado,
            'Alumnos representados' => $representante->representados->map(fn ($alumno) => $alumno->nombre_completo.' ('.$alumno->persona?->numero_identificacion.')')->implode('; '),
            'Último acceso' => $representante->ultimo_acceso?->format('Y-m-d H:i'),
        ];
    @endphp
    <dialog class="modal" id="datos-representante-{{ $representante->id }}" aria-labelledby="datos-representante-titulo-{{ $representante->id }}">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3 id="datos-representante-titulo-{{ $representante->id }}">Datos del representante</h3>
                    <span>Información personal y de acceso.</span>
                </div>
                <button class="btn" type="button" data-close-datos aria-label="Cerrar datos del representante">Cerrar</button>
            </div>
            <dl class="form-grid">
                @foreach ($datos as $etiqueta => $valor)
                    <div class="field-group">
                        <dt>{{ $etiqueta }}</dt>
                        <dd style="margin: 0; overflow-wrap: anywhere;">{{ filled($valor) ? $valor : 'Sin registro' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </dialog>
@endforeach
