@foreach ($estudiantes as $estudiante)
    @php
        $persona = $estudiante->persona;
        $datos = [
            'Número de identificación' => $persona?->numero_identificacion,
            'Representante' => $estudiante->representante?->nombre_completo,
            'Parentesco del representante' => $parentescos[$estudiante->parentesco_representante] ?? null,
            'Contacto del representante' => $estudiante->representante?->persona?->celular ?: $estudiante->representante?->persona?->telefono,
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
            'Institución' => $estudiante->institucion?->nombre,
            'Usuario' => $estudiante->username,
            'Correo de acceso' => $estudiante->email,
            'Estado' => $estados[$estudiante->estado] ?? $estudiante->estado,
            'Último acceso' => $estudiante->ultimo_acceso?->format('Y-m-d H:i'),
        ];
    @endphp
    <dialog class="modal" id="datos-alumno-{{ $estudiante->id }}" aria-labelledby="datos-alumno-titulo-{{ $estudiante->id }}">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3 id="datos-alumno-titulo-{{ $estudiante->id }}">Datos del alumno</h3>
                    <span>Información personal y de acceso.</span>
                </div>
                <button class="btn" type="button" data-close-datos aria-label="Cerrar datos del alumno">Cerrar</button>
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
