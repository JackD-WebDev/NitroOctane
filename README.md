# 🚀 **NitroOctane**

_A starter template for projects using a **Laravel Octane** backend and a **Nuxt Nitro BFF** frontend._

> This template is designed to be a starting point for my own personal projects, but it can be used by anyone who wants to build a web application with these technologies.

---

## ✨ Features

- **Basic login page** and authentication with [_Laravel Fortify_](https://laravel.com/docs/12.x/fortify) and [_Sanctum_](https://laravel.com/docs/12.x/sanctum)
- **Zod** for type and form validation on the Nitro Server
- **Custom [JSON API](https://jsonapi.org/) inspired responses** for consistent API output
- **[Nuxt Nitro](https://nitro.unjs.io/)** as a lightweight backend-for-frontend (BFF)
- **Dockerized architecture** with **Nginx reverse proxy** for routing
- **Comprehensive testing**:
  - **[Pest](https://pestphp.com/docs/introduction)** for backend (Laravel)
  - **[Vitest](https://vitest.dev/guide/)** for frontend (Nuxt)

---

## 🚀 Getting Started

> **Note:** This section is planned for later and will include step-by-step instructions for setting up and running the project.

## 📖 API Documentation

- **Planned:** OpenAPI/Swagger docs for public API (coming soon)
- **Current:** See `api/routes/api.php` for available endpoints

---

## 🗂️ Project Structure

- `api/` — Laravel backend (Octane, Fortify, Sanctum, Pest)
- `client/` — Nuxt frontend (Nitro, Zod, Vitest)
- `dockerfiles/` — Custom Dockerfiles for each service
- `env/` — Environment variable files for DB, phpMyAdmin, etc.
- `nginx/`, `php/` — Service configs

---

## 📄 License

MIT — see [LICENSE](LICENSE)

---

## 🛠️ Planned Features

- 📊 Dashboard
- 👥 Multi-login management
- ✅ Formkit validation
- 🛠️ Install script
- 🌐 Public API with [OpenAPI](https://www.openapis.org/) (Swagger) and interactive docs
