---
name: soffiweb-ui-bootstrap
description: "Use for Laravel projects with Bootstrap 4 and Blade templates (no Livewire). ALWAYS combine with soffiweb-ui-core which contains the CSS tokens, component library, and shared JS. This skill only covers Bootstrap 4 integration specifics: layout pattern with container-fluid, how to override Bootstrap classes with sw-*, tables with .table, modals with data-toggle, forms with .form-group, validation with @error Blade directive, Laravel pagination with custom pagination view, and standard CRUD actions with Controller + FormRequest + Service patterns."
license: MIT
metadata:
  author: soffiweb
  version: "1.1"
  laravel: "5.8 — 8.x"
  css: "Bootstrap 4"
---

# Soffiweb UI — Bootstrap (Laravel 5.8–8 + Bootstrap 4)

> ⚠️ **IMPORTANTE**: Siempre combinar con **soffiweb-ui-core**. Este skill solo contiene lo específico de Bootstrap 4 + Blade.

---

## Stack

| Capa          | Tecnología                        |
|---------------|-----------------------------------|
| Framework     | Laravel 5.8 / 6.x / 7.x / 8.x    |
| CSS           | Bootstrap 4 + Soffiweb tokens     |
| Vistas        | Blade templates                   |
| Interactividad| jQuery (incluido con Bootstrap 4) |
| Iconos        | Font Awesome 6                    |
| Tipografía    | Inter (Google Fonts)              |
| Modales       | Bootstrap 4 (`data-toggle`)       |
| Auth          | `laravel/ui` o manual             |

---

## Contexto de layout

Antes de renderizar el layout autenticado, entregar desde backend:

- `$headerCompanyName`: empresa/tenant actual.
- `$headerPeriodLabel`: periodo activo.
- `$displayName`, `$roleLabel`, `$hasCustomAvatar`, `$avatarUrl`: usuario autenticado.

Resolverlos en controller, middleware o `View::composer`; nunca consultar DB desde Blade.

## Regla para modales Bootstrap

Los modales Bootstrap no deben quedar dentro de contenedores que creen stacking context persistente. Si el layout usa animaciones o transforms, renderizar los `.modal` al final del layout o moverlos a `body` desde JS compartido.

## Regla de densidad visual

No agregar métricas, totales o bloques KPI por defecto en vistas CRUD/listado. Solo se renderizan si el usuario lo solicita explícitamente.

## Regla de marca en topbar

En el bloque izquierdo del topbar usar logo de la marca/empresa si existe asset disponible. No dejar solo `config('app.name')` como texto plano salvo que el usuario lo pida.

## Regla de no redundancia de empresa

El nombre de la empresa o tenant actual debe mostrarse en el header/topbar y no repetirse dentro del contenido de la vista.

Reglas:

- No renderizar `razonsocial`, nombre comercial o label de empresa encima de tablas, cards, filtros o resúmenes.
- No duplicar el contexto tenant dentro de encabezados internos si ya está visible en el topbar.
- Solo mostrar datos de empresa dentro del contenido cuando sean parte funcional del documento, por ejemplo reportes imprimibles, PDFs, comprobantes o pantallas donde la identificación legal sea requisito.
- En CRUDs y listados normales, asumir que el contexto de empresa ya está resuelto por el header.

## Regla para tabs integradas a panel

Cuando una vista CRUD necesite alternar entre 2 o más secciones del mismo contexto, usar tabs compactas integradas al panel de contenido; no apilar bloques uno debajo del otro.

Reglas:

- Usar `Bootstrap 4` tabs (`nav` + `data-toggle="tab"` + `tab-content` + `tab-pane`).
- Las tabs deben ir alineadas a la izquierda y ocupar solo el ancho necesario.
- La pestaña activa usa `var(--p)` como fondo y `var(--btn-text)` como color de texto.
- Las pestañas inactivas usan `var(--surface)` con texto `var(--bc2)`.
- El panel de contenido debe verse unido a las tabs: tabs arriba, card abajo, sin separación vertical.
- Las tabs deben ser compactas: altura media, padding corto, sin ocupar todo el ancho del contenedor.
- Evitar tabs tipo ancho completo (`flex:1`) salvo requerimiento explícito.
- Mantener radios superiores en tabs y radios inferiores en el panel para que se perciba como un solo bloque.

Snippet base:

```html
<ul class="nav cuenta-tabs" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" data-toggle="tab" href="#panel-1" role="tab">Pestaña 1</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-toggle="tab" href="#panel-2" role="tab">Pestaña 2</a>
  </li>
</ul>

<div class="tab-content">
  <div class="tab-pane fade show active" id="panel-1" role="tabpanel">
    <section class="sw-card tab-panel-card">
      ...
    </section>
  </div>

  <div class="tab-pane fade" id="panel-2" role="tabpanel">
    <section class="sw-card tab-panel-card">
      ...
    </section>
  </div>
</div>
```

CSS guía:

```css
.cuenta-tabs {
  gap: .35rem;
  margin-bottom: 0;
}

.cuenta-tabs .nav-item {
  flex: 0 0 auto;
}

.cuenta-tabs .nav-link {
  background: var(--surface);
  border: 1px solid rgba(129, 162, 207, .35);
  border-bottom: 0;
  border-radius: .75rem .75rem 0 0;
  color: var(--bc2);
  font-size: 13px;
  font-weight: 600;
  min-height: 44px;
  padding: .7rem 1rem;
}

.cuenta-tabs .nav-link.active {
  background: var(--p);
  border-color: var(--p);
  color: var(--btn-text);
}

.tab-panel-card {
  border-top-left-radius: 0;
}
```

## Setup en `layouts/app.blade.php`

```html
<!DOCTYPE html>
<html lang="es" id="root" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name') }} — @yield('title')</title>

  {{-- Bootstrap 4 (compilado con Laravel Mix) --}}
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">

  {{-- Soffiweb: Inter + Font Awesome --}}
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>

  {{-- Tokens y componentes Soffiweb (del core) --}}
  <link href="{{ asset('css/soffiweb.css') }}" rel="stylesheet">
  {{-- O pegar el bloque <style> del core directamente --}}

  <style>
    body { font-family: 'Inter', sans-serif !important; }
    /* Neutralizar clases Bootstrap que chocan con Soffiweb */
    .btn-primary, .btn-secondary, .btn-success, .btn-danger, .btn-warning { display: none !important; }
    .badge, .badge-primary, .badge-success { display: none !important; }
  </style>
</head>
<body>

  <header class="sw-topbar">
    <div class="sw-topbar-brand">
      <button type="button" class="sw-btn sw-btn-ghost sw-btn-sm sw-sidebar-toggle"
              aria-controls="sw-sidebar" aria-expanded="false" aria-label="Abrir menú"
              onclick="swToggleSidebar(this)">
        <i class="fa-solid fa-bars"></i>
      </button>
      <span class="sw-topbar-app">
        <img src="{{ asset('img/LogoSoffiweb.png') }}" alt="{{ config('app.name') }}" class="sw-topbar-logo">
      </span>
      <div class="sw-topbar-tenant">
        <span class="sw-topbar-company" title="{{ $headerCompanyName }}">{{ $headerCompanyName }}</span>
        <span class="sw-topbar-period" title="{{ $headerPeriodLabel }}">
          <i class="fa-regular fa-calendar"></i> {{ $headerPeriodLabel }}
        </span>
      </div>
    </div>

    <div class="sw-topbar-actions">
      <button type="button" class="sw-btn sw-btn-ghost sw-btn-sm" aria-label="Cambiar tema"
              onclick="swToggleTheme()">
        <i class="fa-solid fa-circle-half-stroke"></i>
      </button>

      <div class="sw-user-menu">
        <button type="button" class="sw-user-trigger" aria-haspopup="true" aria-expanded="false"
                aria-controls="sw-user-dropdown" onclick="swToggleUserMenu(this)">
          <span class="sw-user-avatar" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
          <span class="sw-user-name">{{ $displayName }}</span>
          <i class="fa-solid fa-angle-down sw-user-arrow"></i>
        </button>
        <div class="sw-user-dropdown" id="sw-user-dropdown">
          <div class="sw-user-role">
            <span class="sw-user-role-label">Rol</span>
            <span class="sw-user-role-value">{{ $roleLabel }}</span>
          </div>
          <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="sw-user-action">
              <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
            </button>
          </form>
        </div>
      </div>
    </div>
  </header>

  <div class="sw-body">
    {{-- Sidebar --}}
    @include('layouts.sidebar')
    <button type="button" class="sw-sidebar-backdrop" aria-label="Cerrar menú"
            onclick="swCloseSidebar()"></button>

    {{-- Main --}}
    <main class="sw-main sw-fade-in">
      @yield('content')
    </main>
  </div>

  {{-- Bootstrap 4 JS + jQuery --}}
  <script src="{{ asset('js/app.js') }}"></script>

  {{-- JS del core (swToggleTheme, swToggleSub, swToggleSS, etc.) --}}
  <script src="{{ asset('js/soffiweb.js') }}"></script>
  {{-- O pegar el bloque JS del core directamente --}}

  @stack('scripts')
</body>
</html>
```

---

## Sidebar Blade (`layouts/sidebar.blade.php`)

```html
<nav class="sw-sidebar" id="sw-sidebar" aria-label="Navegación principal">
  <div class="sw-sb-brand">
    <div class="sw-sb-brand-name">{{ config('app.name') }}</div>
    <div class="sw-sb-brand-sub">Sistema de gestión</div>
  </div>

  <div class="sw-sb-group">Principal</div>
  <a href="{{ route('dashboard') }}"
     class="sw-sb-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <i class="fa-solid fa-house"></i> Dashboard
  </a>

  <div class="sw-sb-group">Módulos</div>

  <a href="{{ route('activos.index') }}"
     class="sw-sb-link {{ request()->routeIs('activos.*') ? 'active' : '' }}">
    <i class="fa-solid fa-boxes-stacked"></i> Activos
  </a>

  <button type="button" class="sw-sb-link sw-sb-toggle {{ request()->routeIs('reportes.*') ? 'open' : '' }}"
          aria-expanded="{{ request()->routeIs('reportes.*') ? 'true' : 'false' }}"
          aria-controls="sw-menu-reportes" onclick="swToggleSub(this)">
    <i class="fa-solid fa-chart-bar"></i> Reportes
    <i class="fa-solid fa-chevron-right sw-arrow"></i>
  </button>
  <div id="sw-menu-reportes" class="sw-sb-sub {{ request()->routeIs('reportes.*') ? 'open' : '' }}">
    <a href="{{ route('reportes.responsable') }}"
       class="sw-sb-sub-link {{ request()->routeIs('reportes.responsable') ? 'active' : '' }}">
      <i class="fa-solid fa-user"></i> Por responsable
    </a>
    <a href="{{ route('reportes.departamento') }}"
       class="sw-sb-sub-link {{ request()->routeIs('reportes.departamento') ? 'active' : '' }}">
      <i class="fa-solid fa-building"></i> Por departamento
    </a>
  </div>

  <div class="sw-sb-group">Cuenta</div>
  <a href="{{ route('profile') }}"
     class="sw-sb-link {{ request()->routeIs('profile') ? 'active' : '' }}">
    <i class="fa-solid fa-circle-user"></i> Mi perfil
  </a>
  <form action="{{ route('logout') }}" method="POST" class="sw-sb-form">
    @csrf
    <button type="submit" class="sw-sb-link sw-sb-action">
      <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
    </button>
  </form>
</nav>
```

---

## Tabla con acciones CRUD

```html
@extends('layouts.app')
@section('title', 'Activos')
@section('content')

{{-- Métricas --}}
<div class="sw-metrics-grid">
  <div class="sw-metric">
    <div class="sw-metric-label">
      <i class="fa-solid fa-boxes-stacked" style="color:var(--p);font-size:10px"></i>
      Total
    </div>
    <div class="sw-metric-value" style="color:var(--p)">{{ $total }}</div>
    <div class="sw-metric-sub">registros activos</div>
  </div>
</div>

{{-- Alertas Blade --}}
@if(session('success'))
  <div class="sw-alert sw-alert-success mb-3">
    <i class="fa-solid fa-circle-check"></i>
    <div>{{ session('success') }}</div>
  </div>
@endif

{{-- Botones --}}
<div class="d-flex flex-wrap mb-3" style="gap:8px">
  <a href="{{ route('activos.create') }}" class="sw-btn sw-btn-primary">
    <i class="fa-solid fa-plus"></i> Agregar
  </a>
  <a href="{{ route('activos.pdf') }}" class="sw-btn sw-btn-ghost" target="_blank">
    <i class="fa-solid fa-file-pdf"></i> PDF
  </a>
</div>

{{-- Tabla --}}
<div class="sw-table-wrap">
  <table class="sw-table">
    <thead>
      <tr>
        <th><i class="fa-solid fa-hashtag" style="margin-right:4px"></i>Código</th>
        <th>Descripción</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($activos as $activo)
      <tr>
        <td><code style="color:var(--s);font-size:11px">{{ $activo->codigo }}</code></td>
        <td>{{ Str::limit($activo->descripcion, 55) }}</td>
        <td>
          @if($activo->estado === 'activo')
            <span class="sw-badge sw-badge-success">
              <i class="fa-solid fa-circle" style="font-size:6px"></i> Activo
            </span>
          @endif
        </td>
        <td>
          <div class="d-flex" style="gap:4px">
            <a href="{{ route('activos.edit', $activo) }}" class="sw-bico sw-bico-edit">
              <i class="fa-solid fa-pen"></i>
            </a>
            <button class="sw-bico sw-bico-delete" data-toggle="modal"
                    data-target="#modalEliminar" data-id="{{ $activo->id }}">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="4" class="text-center py-4" style="color:var(--bc2)">
          <i class="fa-solid fa-inbox" style="font-size:24px;display:block;margin-bottom:8px"></i>
          Sin datos
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- Modal eliminar (Bootstrap 4) --}}
<div class="modal fade" id="modalEliminar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="sw-modal w-100">
      <div class="sw-modal-title">
        <i class="fa-solid fa-trash" style="color:var(--er);margin-right:6px"></i>
        ¿Eliminar?
      </div>
      <div class="sw-modal-body">Esta acción no se puede deshacer.</div>
      <div class="sw-modal-actions">
        <button class="sw-btn sw-btn-ghost sw-btn-sm" data-dismiss="modal">
          <i class="fa-solid fa-xmark"></i> Cancelar
        </button>
        <form id="formEliminar" method="POST" style="display:inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="sw-btn sw-btn-error sw-btn-sm">
            <i class="fa-solid fa-trash"></i> Eliminar
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
$('#modalEliminar').on('show.bs.modal', function(e) {
  const btn = $(e.relatedTarget);
  const id = btn.data('id');
  $('#formEliminar').attr('action', '/activos/' + id);
});
</script>
@endpush
```

---

## Formulario create/edit

```html
@extends('layouts.app')
@section('title', isset($activo) ? 'Editar' : 'Nuevo')
@section('content')

<div class="sw-card">
  <div class="sw-card-title">
    <i class="fa-solid fa-{{ isset($activo) ? 'pen' : 'plus' }}" style="margin-right:6px"></i>
    {{ isset($activo) ? 'Editar' : 'Nuevo' }}
  </div>

  <form action="{{ isset($activo) ? route('activos.update', $activo) : route('activos.store') }}"
        method="POST" class="mt-3">
    @csrf
    @if(isset($activo)) @method('PUT') @endif

    <div class="row">
      <div class="col-md-8">
        <div class="sw-field mb-3">
          <label class="sw-label">
            <i class="fa-regular fa-file-lines" style="font-size:10px"></i> Descripción *
          </label>
          <input type="text" name="descripcion"
                 class="sw-input @error('descripcion') sw-error @enderror"
                 value="{{ old('descripcion', $activo->descripcion ?? '') }}"/>
          @error('descripcion')
            <span class="sw-hint-error">{{ $message }}</span>
          @enderror
        </div>
      </div>
      <div class="col-md-4">
        <div class="sw-field mb-3">
          <label class="sw-label">Estado</label>
          <select name="estado" class="sw-select">
            <option value="activo" {{ old('estado', $activo->estado ?? '') == 'activo' ? 'selected' : '' }}>
              Activo
            </option>
            <option value="inactivo" {{ old('estado', $activo->estado ?? '') == 'inactivo' ? 'selected' : '' }}>
              Inactivo
            </option>
          </select>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-end" style="gap:8px">
      <a href="{{ route('activos.index') }}" class="sw-btn sw-btn-ghost">
        <i class="fa-solid fa-xmark"></i> Cancelar
      </a>
      <button type="submit" class="sw-btn sw-btn-primary">
        <i class="fa-solid fa-floppy-disk"></i>
        {{ isset($activo) ? 'Actualizar' : 'Guardar' }}
      </button>
    </div>
  </form>
</div>

@endsection
```

---

## Bootstrap 4 Compatibility Notes

- `d-flex`, `flex-wrap` disponibles; `gap-*` no existe en BS4 → usar `style="gap:Xpx"` inline
- `data-toggle="modal"` y `data-dismiss="modal"` son sintaxis BS4 (BS5 usa `data-bs-toggle`)
- No necesitas `@stack` si el proyecto es muy legacy
- `Str::limit()`, `$collection->find()` disponibles en Eloquent 5.5+
- En Laravel 5.8 preferir `Auth::user()` sobre `auth()->user()` en casos específicos

---

## Prohibido en proyectos Bootstrap 4

| ❌ Nunca                            | ✅ Siempre                            |
|-------------------------------------|---------------------------------------|
| Clases `btn-*`, `badge-*` de BS4    | Clases `sw-btn sw-btn-*`              |
| Clases `table-striped`, `table` BS4 | Clases `sw-table-wrap` + `sw-table`   |
| Clases `badge-*` de BS4             | Clases `sw-badge sw-badge-*`          |
| Clases `alert alert-*` de BS4       | Clases `sw-alert sw-alert-*`          |
| Clases `card` de BS4                | Clase `sw-card`                       |
| jQuery para lógica de negocio       | jQuery solo para modales BS4          |
| `data-bs-toggle` (BS5)              | `data-toggle` (BS4)                   |
