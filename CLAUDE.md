# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Development server
```bash
php -S 127.0.0.1:8000 -t public/ public/index.php
```
The Symfony CLI (`symfony serve`) is not always available; use the PHP built-in server as fallback.

### Assets
```bash
npm run dev          # compile for development (watch: npm run watch)
npm run build        # compile for production (versioned, minified)
```
Assets entry point: `assets/app.js` → output to `public/build/`. Do NOT commit `public/build/` (gitignored).

### Tests
```bash
# Run all tests
SYMFONY_DEPRECATIONS_HELPER=disabled vendor/bin/.phpunit/phpunit-8.5-0/phpunit tests/

# Run a single test class
SYMFONY_DEPRECATIONS_HELPER=disabled vendor/bin/.phpunit/phpunit-8.5-0/phpunit tests/Controller/BloggerControllerTest.php

# Run a single test method
SYMFONY_DEPRECATIONS_HELPER=disabled vendor/bin/.phpunit/phpunit-8.5-0/phpunit --filter testBlogsListReturns200 tests/Controller/
```
Tests use **SQLite** (configured in `.env.test`). The test DB schema is created with:
```bash
php bin/console doctrine:schema:create --env=test
```
`SYMFONY_DEPRECATIONS_HELPER=disabled` is required to suppress a PHPUnit bridge crash (PHP 7.4 + curl ARM incompatibility with the deprecation reporter). Without it, one test errors.

### Database
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console make:migration          # generate migration after entity changes
php bin/console cache:clear             # required after config/service changes
```

### Environment setup
Copy `.env.example` to `.env.local` and set real credentials — never edit `.env` directly (it has no secrets and is not gitignored by accident; `.env.local` is).

## Architecture

### Request flow
`Controller → Service → Repository/EntityManager → DB`

Controllers are thin HTTP adapters. All business logic lives in `src/Service/`:
- `BloggerService` — creates/updates posts, generates slugs, handles image upload, paginates listings
- `UserService` — registers users (password hashing + persist)
- `ContactService` — persists contact messages

### Service wiring quirk
`BloggerService` takes a `string $uploadDirectory` constructor argument that cannot be autowired. It is explicitly wired in `config/services.yaml`:
```yaml
App\Service\BloggerService:
    arguments:
        $uploadDirectory: '%bloggerfotos_directory%'
```
The parameter resolves to `%kernel.project_dir%/public/uploads/bloggerfotos` (defined at the top of `services.yaml`). Upload files are gitignored.

### Authentication quirk
`LoginFormAuthenticator` authenticates users by **email** (`findOneBy(['email' => ...])`), but the Symfony security provider is configured with `property: username` (for session reload). This is intentional — login form collects email, but the session identity is the username. Do not change either side without updating both.

Access control is defined in `config/packages/security.yaml`. Protected routes: `/blogger` (create post), `/edit-blogger/{id}` (edit), `/entradas` (my posts).

### Routing
All routes are defined via Doctrine Annotations on controller methods (no YAML routes file). Route names in use: `home`, `mis-blog`, `mis-entradas`, `ver-blog`, `blogger`, `editblogger`, `contacts`, `register`, `app_login`, `app_logout`.

### Slug
`BloggerService::createPost()` generates the slug from the title using `SluggerInterface::slug()->lower()`. The `slug` column on `blogger` has a `UNIQUE INDEX`. **Duplicate titles will fail** with a DB constraint error — there is no collision-handling logic.

### Paginator
`BloggerRepository::findAllOrderedByDate()` and `findByUserId()` return a `\Doctrine\ORM\Query` object (not results). This is intentional — `KnpPaginatorInterface::paginate()` requires the raw query to apply `LIMIT`/`OFFSET`. Never call `->getResult()` before passing to the paginator.

### Frontend
Bootstrap 4 and jQuery are loaded via Webpack Encore (`assets/app.js`). The old vendor files (`public/css/bootstrap/`, `public/js/bootstrap*.js`) have been removed. Templates use `{{ encore_entry_link_tags('app') }}` and `{{ encore_entry_script_tags('app') }}`.

### Test isolation
`config/services_test.yaml` overrides the HTTP client service to use `NativeHttpClient` instead of `CurlHttpClient`. This prevents a PHP 7.4 + curl 7.68.0 (ARM) destructor crash when multiple test classes run in sequence. Do not remove this file.
