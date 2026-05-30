# Blogger

Aplicación de blog construida con Symfony 5.2. Permite a usuarios registrados crear y editar entradas de blog con imagen, ver entradas de otros usuarios y enviar mensajes de contacto.

## Requisitos

- PHP 7.2.5 o superior
- Composer
- MySQL 5.7 o superior (o MariaDB 10.3+)
- Node.js 14+ y npm (para assets)
- Symfony CLI (recomendado para desarrollo)

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/saplaza1410/blogger.git
cd blogger
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Configurar variables de entorno

```bash
cp .env.example .env.local
```

Editar `.env.local` y configurar:
- `DATABASE_URL` con tus credenciales de MySQL
- `APP_SECRET` con una cadena aleatoria segura (genera con `openssl rand -hex 32`)

### 4. Crear la base de datos y ejecutar migraciones

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Instalar dependencias JS y compilar assets

```bash
npm install
npm run dev
```

### 6. Iniciar el servidor de desarrollo

```bash
symfony serve
```

La aplicación estará disponible en `https://localhost:8000`.

## Comandos útiles

| Comando | Descripción |
|---------|-------------|
| `php bin/console doctrine:migrations:migrate` | Ejecutar migraciones pendientes |
| `php bin/console make:migration` | Generar migración desde cambios en entidades |
| `npm run dev` | Compilar assets (desarrollo) |
| `npm run build` | Compilar assets (producción) |
| `php bin/phpunit` | Ejecutar tests |
| `symfony serve` | Iniciar servidor de desarrollo |

## Estructura del proyecto

```
src/
  Controller/    Controladores HTTP (capa delgada)
  Entity/        Entidades Doctrine (Blogger, Contact, User)
  Form/          Formularios Symfony
  Repository/    Repositorios Doctrine con queries custom
  Security/      Autenticador de formulario de login
  Service/       Lógica de negocio (BloggerService, UserService, ContactService)
assets/
  app.js         Entry point de Webpack Encore
  styles/        CSS custom
migrations/      Migraciones de base de datos
templates/       Templates Twig
```
