@extends('layouts.admin-shell')

@section('title', 'Datos institucionales')
@section('admin-nav', 'institucion')
@section('head')
    <link href="{{ asset('css/institucion-identidad.css') }}" rel="stylesheet">
@endsection
@section('sidebar-panel-copy', 'Revise y actualice la informacion oficial de la institucion asociada a su sesion administrativa sin salir del marco principal del dashboard.')

@section('sidebar-panel-items')
    <div>
        <strong>Solo edicion</strong>
        <p>El administrador actualiza la institucion asignada, pero no crea ni elimina registros institucionales.</p>
    </div>
    <div>
        <strong>Consistencia operativa</strong>
        <p>Los cambios aqui alimentan nombre, estado y datos de contacto usados por otros modulos del sistema.</p>
    </div>
@endsection

@section('crumbs')
    <a href="{{ route('auth.form') }}">Menu principal</a>
    <span>/</span>
    <a href="{{ route('roles.dashboard', 'admin') }}">Dashboard admin</a>
    <span>/</span>
    <span>Datos institucionales</span>
@endsection

@section('topbar-action')
    <a class="btn institucion-save-icon" href="{{ route('admin.resumen.index') }}" title="Resumen Ejecutivo" aria-label="Resumen Ejecutivo"><i class="fa-solid fa-chart-pie" aria-hidden="true"></i></a>
@endsection

@section('content')
    <div class="institucion-datos-content">
    <header class="hero">
        <div>
            <div class="eyebrow">Administración institucional</div>
            <h1>Datos institucionales</h1>
            <p>Actualice la identidad, representación y datos oficiales del colegio.</p>
        </div>
        <div class="hero-side">
            <strong>AMIE {{ $institucion->codigo_amie }}</strong>
            <p>{{ $estados[$institucion->estado] ?? $institucion->estado }}</p>
        </div>
    </header>

    <section class="panel">
        <div class="section-title">
            <h2>Editar datos de la institución</h2>
            <span>Información oficial</span>
        </div>

        @if (session('status'))
            <div class="alert" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.institucion.update', $institucion) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="institucion-form-heading"><h3>Identificación institucional</h3></div>
                <div class="field-group">
                    <label for="codigo_amie">Código AMIE</label>
                    <input id="codigo_amie" class="input" type="text" name="codigo_amie" value="{{ old('codigo_amie', $institucion->codigo_amie) }}" required>
                </div>
                <div class="field-group full">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" class="input" type="text" name="nombre" value="{{ old('nombre', $institucion->nombre) }}" required>
                </div>
                <div class="field-group full">
                    <label for="slogan">Slogan</label>
                    <input id="slogan" class="input" type="text" name="slogan" maxlength="255" value="{{ old('slogan', $institucion->slogan) }}" @error('slogan') aria-invalid="true" @enderror>
                    @error('slogan') <span role="alert">{{ $message }}</span> @enderror
                </div>
                <div class="institucion-form-heading"><h3>Identidad visual</h3></div>
                @foreach (['logo1' => 'Logotipo del colegio 1', 'logo2' => 'Logotipo 2'] as $campoLogo => $labelLogo)
                    <div class="field-group institucion-logo-field">
                        <label for="{{ $campoLogo }}">{{ $labelLogo }}</label>
                        @if ($institucion->{$campoLogo})
                            <img class="institucion-logo-preview" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($institucion->{$campoLogo}) }}" alt="{{ $labelLogo }} actual">
                        @else
                            <div class="institucion-logo-empty" aria-hidden="true"><i class="fa-regular fa-image"></i></div>
                        @endif
                        <input id="{{ $campoLogo }}" class="input" type="file" name="{{ $campoLogo }}" accept="image/png,image/jpeg,image/webp" aria-describedby="{{ $campoLogo }}-ayuda" @error($campoLogo) aria-invalid="true" @enderror>
                        <span id="{{ $campoLogo }}-ayuda">PNG, JPG o WebP. Máximo 2 MB. Si no selecciona otro archivo, se conserva el logotipo actual.</span>
                        @error($campoLogo) <span role="alert">{{ $message }}</span> @enderror
                    </div>
                @endforeach
                <div class="institucion-form-heading"><h3>Representante institucional</h3></div>
                <div class="field-group">
                    <label for="representante_nombre">Nombre del Representante de la Institución</label>
                    <input id="representante_nombre" class="input" type="text" name="representante_nombre" maxlength="255" value="{{ old('representante_nombre', $institucion->representante_nombre) }}" @error('representante_nombre') aria-invalid="true" @enderror>
                    @error('representante_nombre') <span role="alert">{{ $message }}</span> @enderror
                </div>
                <div class="field-group">
                    <label for="representante_cargo">Cargo</label>
                    <input id="representante_cargo" class="input" type="text" name="representante_cargo" maxlength="120" value="{{ old('representante_cargo', $institucion->representante_cargo) }}" @error('representante_cargo') aria-invalid="true" @enderror>
                    @error('representante_cargo') <span role="alert">{{ $message }}</span> @enderror
                </div>
                <div class="institucion-form-heading"><h3>Organización institucional</h3></div>
                <div class="field-group">
                    <label for="sostenimiento_id">Sostenimiento</label>
                    <select id="sostenimiento_id" class="select" name="sostenimiento_id" required>
                        <option value="">Seleccione</option>
                        @foreach ($sostenimientos as $value => $label)
                            <option value="{{ $value }}" @selected((string) old('sostenimiento_id', $institucion->sostenimiento_id) === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field-group">
                    <label for="regimen_id">Régimen escolar</label>
                    <select id="regimen_id" class="select" name="regimen_id" required>
                        <option value="">Seleccione</option>
                        @foreach ($regimenes as $value => $label)
                            <option value="{{ $value }}" @selected((string) old('regimen_id', $institucion->regimen_id) === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="institucion-form-heading"><h3>Ubicación y contacto</h3></div>
                <div class="field-group">
                    <label for="provincia">Provincia</label>
                    <input id="provincia" class="input" type="text" name="provincia" value="{{ old('provincia', $institucion->provincia) }}" required>
                </div>
                <div class="field-group">
                    <label for="canton">Cantón</label>
                    <input id="canton" class="input" type="text" name="canton" value="{{ old('canton', $institucion->canton) }}" required>
                </div>
                <div class="field-group">
                    <label for="parroquia">Parroquia</label>
                    <input id="parroquia" class="input" type="text" name="parroquia" value="{{ old('parroquia', $institucion->parroquia) }}">
                </div>
                <div class="field-group full">
                    <label for="direccion">Dirección</label>
                    <input id="direccion" class="input" type="text" name="direccion" value="{{ old('direccion', $institucion->direccion) }}">
                </div>
                <div class="field-group">
                    <label for="telefono">Teléfono</label>
                    <input id="telefono" class="input" type="text" name="telefono" value="{{ old('telefono', $institucion->telefono) }}">
                </div>
                <div class="field-group">
                    <label for="email">Correo institucional</label>
                    <input id="email" class="input" type="email" name="email" value="{{ old('email', $institucion->email) }}">
                </div>
                <div class="field-group">
                    <label for="estado">Estado</label>
                    <select id="estado" class="select" name="estado" required>
                        @foreach ($estados as $value => $label)
                            <option value="{{ $value }}" @selected(old('estado', $institucion->estado) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn institucion-save-icon" href="{{ route('admin.institucion.index') }}" aria-label="Cancelar cambios" title="Cancelar cambios"><i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
                <button class="btn btn-primary institucion-save-icon" type="submit" aria-label="Guardar cambios" title="Guardar cambios"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i></button>
            </div>
        </form>
    </section>
    </div>
@endsection
