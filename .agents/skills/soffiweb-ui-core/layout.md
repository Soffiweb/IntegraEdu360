# Soffiweb UI — Base Layout

## Estructura HTML

```html
<!DOCTYPE html>
<html lang="es" id="root" data-theme="light">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name') }} — @yield('title')</title>

  {{-- Fuentes e iconos --}}
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>

  {{-- Tokens y componentes CSS (de soffiweb-ui-core) --}}
  <link href="{{ asset('css/soffiweb-core.css') }}" rel="stylesheet">
  {{-- O pegar el contenido de tokens.md + components.md directamente en <style> --}}
</head>
<body>

  <div class="sw-app">

    {{-- ── TOPBAR ── --}}
    {{-- Lado izquierdo: hamburguesa (móvil) + logo + empresa + período --}}
    {{-- Lado derecho: toggle tema + menú usuario con avatar, nombre, rol --}}
    <header class="sw-topbar">
      <div class="sw-topbar-brand">

        {{-- Botón hamburguesa — solo visible en móvil --}}
        <button type="button" class="sw-btn sw-btn-ghost sw-btn-sm sw-sidebar-toggle"
                aria-controls="sw-sidebar" aria-expanded="false" aria-label="Abrir menú"
                onclick="swToggleSidebar(this)">
          <i class="fa-solid fa-bars"></i>
        </button>

        {{-- Logo de la aplicación/marca --}}
        <span class="sw-topbar-app">
          <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}" class="sw-topbar-logo">
        </span>

        {{-- Empresa y período activo --}}
        <div class="sw-topbar-tenant">
          <span class="sw-topbar-company" title="{{ $headerCompanyName }}">
            {{ $headerCompanyName }}
          </span>
          <span class="sw-topbar-period" title="{{ $headerPeriodLabel }}">
            <i class="fa-regular fa-calendar"></i> {{ $headerPeriodLabel }}
          </span>
        </div>

      </div>

      <div class="sw-topbar-actions">

        {{-- Toggle de tema --}}
        <button type="button" class="sw-btn sw-btn-ghost sw-btn-sm"
                aria-label="Cambiar tema" onclick="swToggleTheme()">
          <i class="fa-solid fa-circle-half-stroke"></i>
        </button>

        {{-- Menú de usuario --}}
        <div class="sw-user-menu">
          <button type="button" class="sw-user-trigger"
                  aria-haspopup="true" aria-expanded="false"
                  aria-controls="sw-user-dropdown"
                  onclick="swToggleUserMenu(this)">

            {{-- Avatar con fallback --}}
            @if($hasCustomAvatar)
              <span class="sw-user-avatar sw-user-avatar-photo" aria-hidden="true">
                <img src="{{ $avatarUrl }}" class="sw-user-avatar-img" alt="">
                <i class="fa-solid fa-user sw-user-avatar-fallback"></i>
              </span>
            @else
              <span class="sw-user-avatar" aria-hidden="true">
                <i class="fa-solid fa-user"></i>
              </span>
            @endif

            <span class="sw-user-name">{{ $displayName }}</span>
            <i class="fa-solid fa-angle-down sw-user-arrow"></i>
          </button>

          {{-- Dropdown usuario --}}
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

      {{-- ── SIDEBAR ── --}}
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

        {{-- Ejemplo de submenú --}}
        <div class="sw-sb-group">Módulos</div>
        <button type="button"
                class="sw-sb-link sw-sb-toggle {{ request()->routeIs('reportes.*') ? 'open' : '' }}"
                aria-expanded="{{ request()->routeIs('reportes.*') ? 'true' : 'false' }}"
                aria-controls="sw-menu-reportes"
                onclick="swToggleSub(this)">
          <i class="fa-solid fa-chart-bar"></i> Reportes
          <i class="fa-solid fa-chevron-right sw-arrow"></i>
        </button>
        <div id="sw-menu-reportes"
             class="sw-sb-sub {{ request()->routeIs('reportes.*') ? 'open' : '' }}">
          <a href="{{ route('reportes.index') }}"
             class="sw-sb-sub-link {{ request()->routeIs('reportes.index') ? 'active' : '' }}">
            <i class="fa-solid fa-file-lines"></i> Resumen
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

      {{-- Backdrop para cerrar el sidebar en móvil --}}
      <button type="button" class="sw-sidebar-backdrop"
              aria-label="Cerrar menú" onclick="swCloseSidebar()"></button>

      {{-- ── MAIN ── --}}
      <main class="sw-main sw-fade-in">
        @yield('content')
      </main>

    </div>
  </div>

  <script src="{{ asset('js/soffiweb-core.js') }}"></script>
  {{-- O pegar el contenido de js.md directamente --}}
  @stack('scripts')
</body>
</html>
```

---

## Datos del header — vienen del backend, nunca desde Blade

| Variable             | Descripción                                     |
| -------------------- | ----------------------------------------------- |
| `$headerCompanyName` | Empresa o tenant activo                         |
| `$headerPeriodLabel` | Período vigente (ej. `2025-01-01 – 2025-12-31`) |
| `$displayName`       | Nombre del usuario autenticado                  |
| `$roleLabel`         | Rol o cargo del usuario                         |
| `$hasCustomAvatar`   | `bool` — si tiene foto de perfil                |
| `$avatarUrl`         | URL de la foto de perfil                        |

Resolverlos en controller, `View::composer` o middleware. **Nunca consultar DB en Blade.**

---

## Responsabilidades por zona

| Zona    | Qué contiene                                                    |
| ------- | --------------------------------------------------------------- |
| Topbar  | Logo + empresa + período (izquierda) · tema + usuario (derecha) |
| Sidebar | Navegación agrupada con submenús · sección Cuenta al final      |
| Main    | `@yield('content')` — el contenido de cada vista                |

---

## Cómo agregar secciones al sidebar

### Enlace simple

```html
<a
    href="{{ route('x.index') }}"
    class="sw-sb-link {{ request()->routeIs('x.*') ? 'active' : '' }}"
>
    <i class="fa-solid fa-icon-name"></i> Nombre
</a>
```

### Submenú expandible

```html
<button
    type="button"
    class="sw-sb-link sw-sb-toggle {{ request()->routeIs('x.*') ? 'open' : '' }}"
    aria-expanded="{{ request()->routeIs('x.*') ? 'true' : 'false' }}"
    aria-controls="sw-menu-x"
    onclick="swToggleSub(this)"
>
    <i class="fa-solid fa-icon"></i> Nombre del módulo
    <i class="fa-solid fa-chevron-right sw-arrow"></i>
</button>
<div
    id="sw-menu-x"
    class="sw-sb-sub {{ request()->routeIs('x.*') ? 'open' : '' }}"
>
    <a
        href="{{ route('x.sub1') }}"
        class="sw-sb-sub-link {{ request()->routeIs('x.sub1') ? 'active' : '' }}"
    >
        <i class="fa-solid fa-file-lines"></i> Subopción 1
    </a>
    <a
        href="{{ route('x.sub2') }}"
        class="sw-sb-sub-link {{ request()->routeIs('x.sub2') ? 'active' : '' }}"
    >
        <i class="fa-solid fa-list"></i> Subopción 2
    </a>
</div>
```
