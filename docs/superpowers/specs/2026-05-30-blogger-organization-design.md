# Blogger — Diseño de Organización del Repositorio

**Fecha:** 2026-05-30  
**Repositorio:** https://github.com/saplaza1410/blogger.git  
**Stack:** PHP 7.2+, Symfony 5.2, Doctrine ORM, Twig, MySQL, Bootstrap 4

---

## Contexto

El proyecto es una aplicación de blogging construida con Symfony 5.2. Permite a usuarios registrados crear y editar entradas de blog, ver entradas de otros usuarios y contactar al administrador. El repositorio presenta problemas de higiene git (archivos sensibles commiteados, directorio accidental) y de organización del código (lógica de negocio en controllers, nomenclatura mixta, sin tests ni migraciones).

---

## Problemas identificados

### Git / Seguridad
| Problema | Impacto |
|----------|---------|
| `.env` commiteado con `APP_SECRET` y `DATABASE_URL` | Exposición de secretos en historial público |
| Directorio `~/` commiteado (settings de VS Code) | Datos personales del desarrollador en el repo |
| `public/uploads/bloggerfotos/` con imágenes reales | Archivos binarios innecesarios en el repo |
| `.gitignore` obsoleto (Symfony 2/3) | Archivos sensibles no cubiertos |
| `README.md` vacío | Sin instrucciones de instalación |

### Código PHP
| Problema | Impacto |
|----------|---------|
| Lógica de negocio en Controllers | Difícil de testear, viola SRP |
| Nomenclatura mixta español/inglés | Inconsistencia, dificulta mantenimiento |
| `migrations/` vacío | Esquema de BD no versionado |
| Bootstrap/jQuery como archivos vendor en `/public/` | Sin gestión de dependencias front |
| Sin tests | Sin cobertura de regresiones |

---

## Enfoque elegido: Secuencial (A)

Separar la limpieza git del refactor de código. Cada fase es autónoma y puede verificarse antes de continuar.

---

## Fases de implementación

### Fase 1 — Limpieza de Git

**1a. Purgar secretos del historial**
- Usar BFG Repo Cleaner para eliminar `.env` del historial completo
- Crear `.env.example` con variables en blanco y comentarios explicativos:
  ```
  APP_ENV=dev
  APP_SECRET=generate-with-php-bin-console-secret
  DATABASE_URL="mysql://user:password@127.0.0.1:3306/myblogger?serverVersion=5.7"
  MAILER_DSN=smtp://localhost
  ```
- Ejecutar `git push --force` al remote tras la purga

**1b. Eliminar archivos accidentales**
- Eliminar el directorio `~/` del tracking (`git rm -r --cached "~/"`)
- Eliminar `public/uploads/` del tracking y del historial
- Las imágenes ya existentes locales no se borran

**1c. Actualizar .gitignore**
```gitignore
# Symfony
/.env
/.env.local
/.env.*.local
/var/
/vendor/
/public/uploads/
/public/bundles/

# Node
/node_modules/
/public/build/

# IDE
.idea/
.vscode/
~

# PHPUnit
/phpunit.xml

# Composer
/composer.phar
```

**1d. README.md**
Secciones: descripción del proyecto, requisitos, instalación paso a paso, comandos útiles.

---

### Fase 2 — Capa de Services

Crear `src/Service/` con tres services:

**`BloggerService`**
- `createPost(Blogger $blog, UploadedFile $picture, User $user): void`
- `updatePost(Blogger $blog, ?UploadedFile $picture): void`
- `getPaginatedPosts(int $page): PaginationInterface`
- `getUserPosts(int $userId, int $page): PaginationInterface`
- Extraer la lógica de upload de imagen de `BloggerController` a este service

**`UserService`**
- `register(User $user, string $plainPassword): void`
- Extraer hashing de contraseña y persistencia de `RegisterController`

**`ContactService`**
- `save(Contact $contact): void`
- Extraer la lógica de guardado de `ContactsController`

Los controllers quedan como capa delgada: reciben `Request`, llaman al service, devuelven `Response`.

---

### Fase 3 — Mejoras de código

**3a. Nomenclatura**
- Renombrar entidad `Contacts` → `Contact` (singular, convención Doctrine)
- Renombrar `BloggerRepository::ListBlogger()` → `findAllOrderedByDate()`
- Renombrar `BloggerRepository::MyBlogs()` → `findByUserId()`
- Las rutas de URL visibles al usuario (`/blogs`, `/entradas`, `/blog/{title}`) se mantienen tal como están en español

**3b. Repository: añadir findBySlug**
Añadir `findBySlug(string $slug): ?Blogger` en `BloggerRepository` para buscar posts por slug en lugar de por `title` exacto (evita problemas con caracteres especiales y URLs).

La entidad `Blogger` necesita un campo `slug` (generado automáticamente al crear el post usando `SluggerInterface`).

**3c. Migración inicial**
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```
Commitear el archivo de migración generado en `migrations/`.

---

### Fase 4 — Assets con Webpack Encore

**4a. Instalar Webpack Encore**
```bash
composer require symfony/webpack-encore-bundle
npm install
npm install --save-dev @symfony/webpack-encore bootstrap jquery
```

**4b. Configurar webpack.config.js**
- Entry point: `assets/app.js` que importa Bootstrap y CSS custom
- Output: `public/build/`

**4c. Eliminar archivos vendor de /public/**
- Eliminar `public/css/bootstrap/` (todos los archivos)
- Eliminar `public/js/bootstrap*.js` y `public/js/jquery.min.js`
- Actualizar `templates/base.html.twig` para usar `{{ encore_entry_link_tags('app') }}` y `{{ encore_entry_script_tags('app') }}`

---

### Fase 5 — Tests

Añadir tests funcionales en `tests/` usando `symfony/browser-kit`:

| Test | Qué verifica |
|------|-------------|
| `BloggerControllerTest::testListBlogs` | GET /blogs devuelve 200 |
| `BloggerControllerTest::testCreatePostRequiresAuth` | GET /blogger sin auth devuelve redirect a login |
| `ContactsControllerTest::testContactForm` | POST /contacts con datos válidos guarda y redirige |
| `SecurityControllerTest::testLogin` | GET /login devuelve 200, POST con credenciales correctas autentica |

Configurar `phpunit.xml.dist` para usar la BD de test (`.env.test` ya existe en el repo).

---

## Estructura de carpetas resultante

```
src/
  Controller/
    BloggerController.php     (delgado, solo HTTP)
    ContactsController.php
    HomeController.php
    RegisterController.php
    SecurityController.php
  Entity/
    Blogger.php               (+ campo slug)
    Contact.php               (renombrado de Contacts)
    User.php
  Form/
    BloggerType.php
    ContactType.php           (renombrado)
    UserType.php
  Repository/
    BloggerRepository.php     (métodos renombrados + findBySlug)
    ContactRepository.php
    UserRepository.php
  Security/
    LoginFormAuthenticator.php
  Service/
    BloggerService.php        (NUEVO)
    UserService.php           (NUEVO)
    ContactService.php        (NUEVO)
assets/
  app.js                      (NUEVO - entry point Webpack)
  styles/
    app.css                   (movido desde public/css/styles.css)
migrations/
  Version20260530XXXXXX.php   (NUEVO - migración inicial)
tests/
  BloggerControllerTest.php   (NUEVO)
  ContactsControllerTest.php  (NUEVO)
  SecurityControllerTest.php  (NUEVO)
```

---

## Criterios de éxito

- [ ] `git log --all -- .env` no devuelve ningún commit
- [ ] El directorio `~/` no existe en el historial
- [ ] `.gitignore` cubre `/public/uploads/`, `/.env`, `~/`, `/var/`, `/vendor/`
- [ ] `README.md` tiene instrucciones de instalación completas
- [ ] Cada Controller tiene < 50 líneas (lógica en Services)
- [ ] `BloggerRepository` tiene `findBySlug()` y métodos con nombres en inglés
- [ ] `migrations/` tiene al menos una migración commiteada
- [ ] `npm run build` genera `public/build/` correctamente
- [ ] `php bin/phpunit` ejecuta los 4 tests y todos pasan
