@extends('layouts.admin-shell')

@section('title', 'Datos institucionales')
@section('admin-nav', 'institucion')
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

@section('content')
    <section class="hero">
        <div>
            <h2>Datos institucionales</h2>
            <!-- <p>Esta vista usa la misma base del dashboard administrativo para mantener continuidad en cada opcion del menu de administracion.</p> -->
        </div>
    </section>

    <section class="stats" style="--stats-columns: 3;">
        <article class="card">
            <strong class="metric">{{ $institucion->codigo_amie }}</strong>
            <span>Codigo AMIE</span>
        </article>
        <article class="card">
            <strong class="metric">{{ $estados[$institucion->estado] ?? $institucion->estado }}</strong>
            <span>Estado actual</span>
        </article>
        <article class="card">
            <strong class="metric">{{ $institucion->provincia ?: 'N/D' }}</strong>
            <span>Provincia registrada</span>
        </article>
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Editar datos de la institucion</h2>
            <span>{{ $institucion->nombre }}</span>
        </div>

        @if (session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.institucion.update', $institucion) }}">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="field-group">
                    <label for="codigo_amie">Codigo AMIE</label>
                    <input id="codigo_amie" class="input" type="text" name="codigo_amie" value="{{ old('codigo_amie', $institucion->codigo_amie) }}" required>
                </div>
                <div class="field-group full">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" class="input" type="text" name="nombre" value="{{ old('nombre', $institucion->nombre) }}" required>
                </div>
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
                    <label for="regimen_id">Regimen escolar</label>
                    <select id="regimen_id" class="select" name="regimen_id" required>
                        <option value="">Seleccione</option>
                        @foreach ($regimenes as $value => $label)
                            <option value="{{ $value }}" @selected((string) old('regimen_id', $institucion->regimen_id) === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field-group">
                    <label for="provincia">Provincia</label>
                    <input id="provincia" class="input" type="text" name="provincia" value="{{ old('provincia', $institucion->provincia) }}" required>
                </div>
                <div class="field-group">
                    <label for="canton">Canton</label>
                    <input id="canton" class="input" type="text" name="canton" value="{{ old('canton', $institucion->canton) }}" required>
                </div>
                <div class="field-group">
                    <label for="parroquia">Parroquia</label>
                    <input id="parroquia" class="input" type="text" name="parroquia" value="{{ old('parroquia', $institucion->parroquia) }}">
                </div>
                <div class="field-group full">
                    <label for="direccion">Direccion</label>
                    <input id="direccion" class="input" type="text" name="direccion" value="{{ old('direccion', $institucion->direccion) }}">
                </div>
                <div class="field-group">
                    <label for="telefono">Telefono</label>
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
                <button class="btn btn-primary" type="submit">Guardar cambios</button>
            </div>
        </form>
    </section>
@endsection
