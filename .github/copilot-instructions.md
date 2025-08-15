# GitHub Copilot Instructions for NitroOctane

This document provides instructions for GitHub Copilot to better assist with development in the NitroOctane project.

## Project Overview

NitroOctane is a starter template for building web applications with a Laravel Octane backend and a Nuxt (with Nitro) frontend. The frontend acts as a Backend-for-Frontend (BFF), communicating with the Laravel API. The entire environment is containerized using Docker.

## Tech Stack

### Backend (API)

- **Framework**: Laravel with Laravel Octane
- **Language**: PHP
- **Authentication**: Laravel Fortify and Laravel Sanctum
- **Testing**: Pest
- **API Standard**: Custom JSON API-inspired responses.

### Frontend (Client)

- **Framework**: Nuxt 3 with Nitro server
- **Language**: TypeScript
- **Form & Type Validation**: Zod (on the Nitro server)
- **Styling**: Sass
- **Testing**: Vitest

### Infrastructure

- **Containerization**: Docker
- **Web Server / Reverse Proxy**: Nginx

## Project Structure

- `api/`: Contains the Laravel backend application. All backend-related work (models, controllers, routes, tests) happens here.
- `client/`: Contains the Nuxt frontend application. All frontend-related work (pages, components, stores, server routes) happens here.
- `dockerfiles/`: Custom Dockerfiles for services like PHP, Nginx, etc.
- `env/`: Environment variable files.
- `nginx/`: Nginx configuration.
- `php/`: PHP configuration files (e.g., `uploads.ini`, `supervisord.conf`).

## Key Files & Conventions

### Backend (`api/`)

- **API Routes**: `api/routes/api.php`
- **Models**: `api/app/Models/`
- **Controllers**: `api/app/Http/Controllers/`
- **Tests**: `api/tests/` (Pest tests)
- **Configuration**: `api/config/*.php`

### Frontend (`client/`)

- **Pages**: `client/app/pages/`
- **Components**: `client/app/components/`
- **Nitro Server Routes (BFF)**: `client/server/api/`
- **Stores (State Management)**: `client/app/stores/`
- **Tests**: `client/tests/` (Vitest tests)
- **Nuxt Configuration**: `client/nuxt.config.ts`

## How to Work with this Project

- **Environment Variables**: The project aims to use a master `.env` file at the root level, with values cascading down to the individual `.env` files in the `api/` and `client/` directories to maintain consistency.
- When working on the backend, focus on the `api/` directory. Remember that it's a Laravel project, so Artisan commands, Eloquent models, and Laravel conventions apply.
- When working on the frontend, focus on the `client/` directory. This is a Nuxt project. Pay attention to the `server/api` directory for BFF logic where Zod is used for validation.
- API responses from the Laravel backend should follow the established custom JSON API format.
- Remember that Nginx is used as a reverse proxy to route requests to either the Nuxt client or the Laravel API based on the URL. The configuration is in `nginx/nginx.conf`.
- Use the custom alias `dst && dup server` to restart the Docker services.
- Use the `art` alias for Artisan commands.
- Use the `comp` alias for Composer commands.
- Use the `pest` alias to run backend tests.
- Use the `ntest` alias to run frontend tests.

## API Response Structure

All API responses from the Laravel backend follow a standardized format.

### Success Response (`2xx`)

A successful response includes the following top-level members:

- `success`: `true`
- `message`: An uppercase string describing the successful outcome.
- `data`: The primary resource object or an array of resource objects.
- `links`: An object containing relevant URLs (e.g., `self`, `client`).
- `meta`: An object for non-standard meta-information (e.g., `documentation_url`).
- `version`: The application version string.

Each resource object within `data` is structured as follows:

- `data`: Contains the core resource information.
  - `type`: The resource type (e.g., "user").
  - `user_id` (or `{type}_id`): The resource's unique ID.
  - `attributes`: An object containing the resource's data.
- `links`: Resource-specific links.
- `meta`: Resource-specific meta-information.

### Error Response (`4xx` or `5xx`)

An error response includes:

- `success`: `false`
- `message`: An uppercase string describing the error.
- `errors`: An object containing:
  - `title`: An uppercase, short error identifier.
  - `details`: An array of more specific error messages or objects.

## Coding Style & Preferences

- **PHP DocBlocks**: Important PHP files or classes should include a descriptive comment block at the top, like so:
  ```php
  /*
  |----------------------------------------------------------------------
  | Agent Helper
  |----------------------------------------------------------------------
  |
  | The AgentHelper class provides methods to parse user agent strings
  | and return details about the browser and platform.
  |
  | It can be used to identify the user's browser and platform
  | from the user agent string, which is useful for analytics,
  | debugging, and providing tailored user experiences.
  |
  */
  ```
- Try not to leave notes in frontend files (.ts / .vue) unless absolutely crucial, instead name functions well enough to understand their purpose.
- Nuxt uses auto imports. Avoid using imports in .ts and .vue files unless there is an error.
- Don't hide unused functions or methods in PHP or TypeScript files. Instead, remove them if they are not needed.
- PHP functions should include DocBlocks that describe their purpose, parameters, and return values.
- Strive for 100% Type Safety in PHP and TypeScript code.
- Try to use ternary operators for simple conditional assignments in PHP and TypeScript.
- Sort PHP 'use' statements by length, then by alphabetical order.
- Try to avoid leaving commented-out code in files. If a piece of code is no longer needed, it should be removed entirely.
- Try not to leave notes in frontend files (.ts / .vue) unless absolutely crucial, instead name functions well enough to understand their purpose.
- **TypeScript**: Use TypeScript for all frontend code. Ensure type safety and use zod schemas and z.infer in client/shared/types whenever possible.
- **Zod**: Use Zod for schema validation in the Nitro server routes. Define schemas in `client/shared/types/` and use `z.infer` to create TypeScript types from them.
- **Styling**: Prefer global styles in `client/app/assets/styles` using Sass. Use scoped styles in Vue components only when necessary for component-specific overrides or when a style is not reusable.

## Localization

The application supports multiple languages.

- **Backend (Laravel)**:

  - Translation files are located in `api/lang/`.
  - The `__()` helper function is used in PHP files to retrieve translated strings.
  - The locale is set automatically for API responses by the `api/app/Http/Middleware/LocalizationResponse.php` middleware, which reads the `Accept-Language` header sent by the Nuxt/Nitro frontend.

- **Frontend (Nuxt)**:
  - **Module**: Localization is managed by `@nuxtjs/i18n`. Core configuration is in `nuxt.config.ts`, with detailed `vue-i18n` options potentially in a separate `i18n.config.ts`.
  - **Translation Files**: Language strings are stored in JSON files within `client/locales/`.
  - **Backend Sync**: The `client/server/middleware/apiRequest.ts` middleware intercepts requests to the Laravel backend. It reads the language from the current route's prefix (e.g., `/es_US/`), defaulting to `en_US`, and sets it as the `Accept-Language` header. This ensures the backend API returns responses in the correct language. This middleware also forwards authentication cookies and CSRF tokens.
  - **Routing**: The core routing and translation logic is handled by the global middleware at `client/app/middleware/01.i18n.global.ts`.
  - **Language Persistence & Priority**:
    1.  **Language Switcher**: A dedicated component allows users to switch languages, which immediately updates the route prefix and persists the choice to `localStorage`.
    2.  **Initial Load**: On page load, the language is determined by checking in order: browser preference, `localStorage`, a logged-in user's preference from the Pinia store, and finally the user's selection.
    3.  **User Preference**: For logged-in users, their `preferred_language` is stored in a Pinia store. This preference is updated on the backend only when the user updates their profile on a language-prefixed route (e.g., `/es_US/account`).
    4.  **Registration**: During user registration, the language from the route prefix is added to the request payload as `lang` to set the new user's default language preference.
  - **Usage**: In Vue components, use the `t()` function to display translated strings from the appropriate locale file.
