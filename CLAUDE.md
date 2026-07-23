# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

IntegraEdu360 is a Laravel 12 multi-institutional educational management SaaS platform. It supports multiple roles (superusuario, admin, docente, directivo, inspector, estudiante, padre-tutor, administrativo, asesor-academico, soporte-tecnico, coordinador-curso) across multiple educational institutions.

## Common Commands

```bash
# Full initial setup
composer setup

# Start development server (Laravel + queue + log tail + Vite HMR, all concurrently)
composer dev

# Run tests
composer test

# Run a single test file
php artisan test tests/Feature/AdminUsuarioCrudTest.php

# Frontend assets
npm run dev       # Vite dev server with HMR
npm run build     # Production build

# Database
php artisan migrate
php artisan db:seed --class=DocenteSeeder

# Code formatting (Laravel Pint)
./vendor/bin/pint
```

## Architecture

### Role-Based Access

Roles are defined as metadata in `routes/web.php` via `integraEduRoles()`, not in the database. Each role has: name, short code, category, description, and accent color. The `EnsureRoleSession` middleware (`app/Http/Middleware/`) enforces access control. Controllers are namespaced by role under `app/Http/Controllers/Admin/` and `app/Http/Controllers/Superusuario/`.

### Data Model

The app uses a **dual-user model**: Laravel's built-in `User` model is largely unused; the active model is `Usuario`, which links to:
- `Persona` — personal data (name, contact info)
- `Institucion` — the institution the user belongs to
- `Rol` — via `usuario_rol` pivot (many-to-many, also carries `institucion_id`)

`PeriodoAcademico` belongs to an `Institucion` and has a status + date range.

### Database

- **Production:** PostgreSQL (`IntegraEdu360` database, configured in `.env`)
- **Tests:** In-memory SQLite (configured in `phpunit.xml` — no external DB needed for tests)

### Frontend

Blade templates in `resources/views/` are organized by role (`admin/`, `superusuario/`) and share the `layouts/admin-shell.blade.php` layout. Tailwind CSS 4.x is configured via `@tailwindcss/vite` plugin directly in `vite.config.js` — there is no separate `tailwind.config.js`.

### Design System

The project follows the **Soffiweb** design system documented in `DESIGN.md`. Key tokens:
- **Colors:** Navy (primary), Rosa Soffi (accent), Steel (secondary)
- **Typography:** Plus Jakarta Sans (UI), DM Mono (numeric/data)
- **Spacing:** 4px base unit, CSS custom properties `--sp-xxs` through `--sp-section`
- Dark mode is first-class — always define both light and dark token variants
- Breakpoints: mobile < 640px, tablet 640–1024px, desktop > 1024px

When building UI, follow `DESIGN.md` component patterns for buttons, inputs, cards, tables, sidebar, topbar, and chips.
