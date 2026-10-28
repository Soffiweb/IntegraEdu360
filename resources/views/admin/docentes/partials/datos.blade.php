@foreach ($docentes as $docente)
    @php
        $persona = $docente->persona;
        $datos = [
            'Título o tratamiento' => $persona?->tratamiento,
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
            'Institución' => $docente->institucion?->nombre,
            'Usuario' => $docente->username,
            'Correo de acceso' => $docente->email,
            'Estado' => $estados[$docente->estado] ?? $docente->estado,
            'Último acceso' => $docente->ultimo_acceso?->format('Y-m-d H:i'),
        ];
    @endphp
    <dialog class="modal" id="datos-docente-{{ $docente->id }}" aria-labelledby="datos-docente-titulo-{{ $docente->id }}">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3 id="datos-docente-titulo-{{ $docente->id }}">Datos del docente</h3>
                    <span>Información personal y de acceso.</span>
                </div>
                <button class="btn" type="button" data-close-datos aria-label="Cerrar datos del docente">Cerrar</button>
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
