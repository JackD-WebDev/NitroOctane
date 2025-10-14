# 🚀 NitroOctane

An opinionated starter template for building web applications with a Laravel 12 (Octane) backend and a Nuxt 4 (Nitro) frontend acting as a Backend-for-Frontend (BFF).

This repository combines a production-ready, Docker-first developer experience with batteries-included features like authentication, validation, i18n, and testing.

## ✨ Key features

- Production-ready, **[Docker](https://docs.docker.com/)**-first architecture
- **[Laravel Octane](https://laravel.com/docs/12.x/octane)** backend (high-performance PHP server)
- Authentication with **[Laravel Fortify](https://laravel.com/docs/12.x/fortify)** and API session/cookie handling via **[Laravel Sanctum](https://laravel.com/docs/12.x/sanctum)**
- **[Nuxt 4](https://nuxt.com/)** frontend using the **[Nitro](https://nitro.unjs.io/)** server as a BFF (server-side endpoints live in `client/server/api`)
- **[Zod](https://zod.dev/)** schemas on the Nitro server for request validation and type-safety
- Styling with **[Sass](https://sass-lang.com/)** and a simple CSS reset
- Form handling and validation helpers via **[FormKit](https://formkit.com/)** (see `client/formkit.config.mts`)
- A **[JSON:API](https://jsonapi.org/)** inspired response format used by the Laravel API
- Dockerized architecture with **[Nginx](https://docs.nginx.com/nginx/admin-guide/web-server/reverse-proxy/)** as a reverse proxy and per-service Dockerfiles in `dockerfiles/`.
- Internationalization support (i18n) with Nuxt + **[@nuxtjs/i18n](https://i18n.nuxtjs.org/)** and translation files in `client/locales/`
- Testing setup: **[Pest](https://pestphp.com/docs/introduction)** for backend tests and **[Vitest](https://vitest.dev/guide/)** for frontend tests

## Project layout (high level)

- `api/` — Laravel application

  - `app/` — Models, controllers, middleware, etc.
  - `routes/` — `api.php`, `web.php`
  - `tests/` — Pest tests

- `client/` — Nuxt 3 application (TypeScript)

  - `app/` — Vue pages and components
  - `server/api/` — Nitro server routes (BFF endpoints) — uses Zod for validation
  - `shared/` — shared types and utilities (Zod schemas in `client/shared/types`)
  - `locales/` — translation files for i18n

- `dockerfiles/` — Dockerfiles for PHP, Nginx, Bun, Composer, etc.
- `nginx/` — Nginx reverse-proxy configuration
- `env/` — Example environment files used for container orchestration

## API conventions

The Laravel API responses follow a custom JSON structure used across the project. Standard response members include:

- `success` (boolean)
- `message` (string, uppercase short description)
- `data` (resource object or array)
- `links` (object with relevant URLs)
- `meta` (object for extra info)
- `version` (application version string)

For errors:

- `success`: false
- `message`: uppercase short message
- `errors`: an object with `title` and `details` (array)

Refer to `api/app/Http/Controllers` for concrete examples of response helpers used by controllers.

### Authentication & session features

- Session management and explicit session-related tests and middleware (server-side sessions managed by Laravel + Sanctum).
- Two-factor authentication (2FA) support via Fortify flows and UI integrations.
- Forgot-password / password reset flows and validation rules for strong password policies.
- Email verification for new users and verification-check middleware for protected routes.
- Account page for authenticated users (account, password, and session management).
- Real-time events and broadcasting configuration integrated with Reverb for in-app events and notifications.

## Internationalization (i18n)

The frontend supports multiple locales via `@nuxtjs/i18n`. Language files live in `client/locales/`. The frontend middleware sets the `Accept-Language` header when proxying requests to the Laravel API so responses are localized.

Language selection priority (frontend):

1. Language switcher (route prefix)
2. localStorage
3. Browser preference
4. Logged-in user's preference (from the Pinia store)

New user registrations record the route language and send it to the backend to set `preferred_language`.

## Tests

- Backend (Pest): run from `api/`.
- Frontend (Vitest): run from `client/`.

## Files & places to look

- Frontend server APIs / BFF: `client/server/api/`
- Shared types and Zod schemas: `client/shared/types/`
- Nuxt config: `client/nuxt.config.ts`
- Laravel API routes: `api/routes/api.php`
- Styling: `client/assets/styles/`
- FormKit config: `client/formkit.config.mts`

## License

MIT — see `LICENSE`
