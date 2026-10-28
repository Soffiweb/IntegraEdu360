<section class="distributivo-personal datos-personales">
    <header class="page-header"><div class="page-header-copy">
        <h1>Actualizar datos</h1>
        <p>Revise y actualice su información personal. Los campos con * son obligatorios.</p>
    </div></header>
    @if(session('success'))
        <p class="sw-alert sw-alert-success" role="status">{{ session('success') }}</p>
    @endif
    @if($errors->any())
        <div class="sw-alert sw-alert-error" role="alert">Revise los campos indicados antes de guardar.</div>
    @endif
    <form method="POST" action="{{ route('docente.datos.update') }}" class="sw-card datos-formulario">
        @csrf
        @method('PUT')
        <div class="datos-campos">
            @foreach([
                'numero_identificacion' => ['Número de identificación', 'text', 30, true],
                'primer_nombre' => ['Primer nombre', 'text', 120, true],
                'segundo_nombre' => ['Segundo nombre', 'text', 120, false],
                'primer_apellido' => ['Primer apellido', 'text', 120, true],
                'segundo_apellido' => ['Segundo apellido', 'text', 120, false],
                'fecha_nacimiento' => ['Fecha de nacimiento', 'date', null, false],
                'email' => ['Correo electrónico de contacto', 'email', 255, false],
                'telefono' => ['Teléfono', 'tel', 50, false],
                'celular' => ['Celular', 'tel', 50, false],
                'provincia' => ['Provincia', 'text', 120, false],
                'canton' => ['Cantón', 'text', 120, false],
                'parroquia' => ['Parroquia', 'text', 120, false],
                'direccion' => ['Dirección', 'text', 255, false],
            ] as $campo => [$etiqueta, $tipo, $maximo, $obligatorio])
                <div class="sw-field form-group">
                    <label class="sw-label" for="dato-{{ $campo }}">{{ $etiqueta }}{{ $obligatorio ? ' *' : '' }}</label>
                    <input class="sw-input" id="dato-{{ $campo }}" name="{{ $campo }}" type="{{ $tipo }}"
                           value="{{ old($campo, $persona?->{$campo}) }}" @required($obligatorio)
                           @if($maximo) maxlength="{{ $maximo }}" @endif
                           @error($campo) aria-invalid="true" aria-describedby="error-{{ $campo }}" @enderror>
                    @error($campo)<span class="sw-hint-error" id="error-{{ $campo }}">{{ $message }}</span>@enderror
                </div>
            @endforeach
            <div class="sw-field form-group">
                <label class="sw-label" for="dato-sexo">Sexo</label>
                <select class="sw-select" name="sexo" id="dato-sexo" @error('sexo') aria-invalid="true" aria-describedby="error-sexo" @enderror>
                    <option value="">Sin especificar</option>
                    <option value="M" @selected(old('sexo', $persona?->sexo) === 'M')>Masculino</option>
                    <option value="F" @selected(old('sexo', $persona?->sexo) === 'F')>Femenino</option>
                </select>
                @error('sexo')<span class="sw-hint-error" id="error-sexo">{{ $message }}</span>@enderror
            </div>
        </div>
        <button type="submit" class="sw-btn sw-btn-primary"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Guardar cambios</button>
    </form>
</section>
