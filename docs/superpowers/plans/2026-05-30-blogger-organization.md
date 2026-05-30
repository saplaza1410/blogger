# Blogger Organization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Limpiar el repositorio git de secretos y archivos accidentales, extraer lógica de negocio a Services, añadir slug, Webpack Encore y tests funcionales.

**Architecture:** Enfoque secuencial: Fase 1 limpia git sin tocar código; Fase 2-3 extraen lógica de Controllers a una capa Service; Fase 4 moderniza assets con Webpack Encore; Fase 5 añade tests funcionales. Cada fase termina con un commit verificable.

**Tech Stack:** PHP 7.2+, Symfony 5.2, Doctrine ORM, Twig, MySQL 5.7, KnpPaginator, Webpack Encore, PHPUnit 8.5

---

## Mapa de archivos

### Fase 1 — Git
| Acción | Archivo |
|--------|---------|
| Modificar | `.gitignore` |
| Crear | `.env.example` |
| Modificar | `README.md` |
| Eliminar del tracking | `~/` (directorio literal) |
| Eliminar del tracking | `public/uploads/` |
| Purgar del historial | `.env` |

### Fase 2 — Services
| Acción | Archivo |
|--------|---------|
| Crear | `src/Service/BloggerService.php` |
| Modificar | `src/Controller/BloggerController.php` |
| Crear | `src/Service/UserService.php` |
| Modificar | `src/Controller/RegisterController.php` |
| Crear | `src/Service/ContactService.php` |
| Modificar | `src/Controller/ContactsController.php` |

### Fase 3 — Mejoras de código
| Acción | Archivo |
|--------|---------|
| Renombrar | `src/Entity/Contacts.php` → `src/Entity/Contact.php` |
| Renombrar | `src/Repository/ContactsRepository.php` → `src/Repository/ContactRepository.php` |
| Renombrar | `src/Form/ContactsType.php` → `src/Form/ContactType.php` |
| Modificar | `src/Controller/ContactsController.php` |
| Modificar | `src/Service/ContactService.php` |
| Modificar | `src/Repository/BloggerRepository.php` |
| Modificar | `src/Service/BloggerService.php` |
| Modificar | `src/Controller/BloggerController.php` |
| Modificar | `src/Entity/Blogger.php` |
| Modificar | `templates/blogger/blogs.html.twig` |
| Crear | `migrations/VersionXXXX.php` (generado) |

### Fase 4 — Webpack Encore
| Acción | Archivo |
|--------|---------|
| Crear | `webpack.config.js` |
| Crear | `assets/app.js` |
| Crear | `assets/styles/app.css` |
| Modificar | `templates/base.html.twig` |
| Eliminar | `public/css/bootstrap/` (directorio completo) |
| Eliminar | `public/js/bootstrap*.js`, `public/js/jquery.min.js` |

### Fase 5 — Tests
| Acción | Archivo |
|--------|---------|
| Modificar | `.env.test` |
| Crear | `tests/Controller/BloggerControllerTest.php` |
| Crear | `tests/Controller/ContactsControllerTest.php` |
| Crear | `tests/Controller/SecurityControllerTest.php` |

---

## FASE 1 — Limpieza de Git

### Task 1: Purgar .env del historial completo

**Prerequisito:** Java instalado (`java -version`). Si no está instalado: `brew install openjdk`.

**Files:**
- Purgar del historial: `.env`
- Crear: `.env.example`

- [ ] **Step 1: Verificar que Java está disponible**

```bash
java -version
```
Expected: versión de Java (cualquiera). Si falla, instalar con `brew install openjdk`.

- [ ] **Step 2: Descargar BFG Repo Cleaner**

```bash
curl -L https://repo1.maven.org/maven2/com/madgag/bfg/1.14.0/bfg-1.14.0.jar -o /tmp/bfg.jar
```

- [ ] **Step 3: Crear un clon bare del remoto para que BFG pueda reescribir el historial**

```bash
cd /tmp
git clone --mirror https://github.com/saplaza1410/blogger.git blogger-mirror.git
```

- [ ] **Step 4: Ejecutar BFG para eliminar .env del historial completo**

```bash
java -jar /tmp/bfg.jar --delete-files .env /tmp/blogger-mirror.git
```

Expected output: líneas que dicen `Deleted` para cada commit donde existía `.env`.

- [ ] **Step 5: Limpiar el historial reescrito**

```bash
cd /tmp/blogger-mirror.git
git reflog expire --expire=now --all
git gc --prune=now --aggressive
```

- [ ] **Step 6: Hacer push --force al remoto**

> ⚠️ Esta operación reescribe el historial público del repositorio. Todos los colaboradores deberán hacer `git fetch --all` y `git reset --hard origin/main` después.

```bash
git push --force
```

- [ ] **Step 7: Actualizar el clon local para que refleje el historial limpio**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
git fetch --all
git reset --hard origin/main
```

- [ ] **Step 8: Crear .env.example**

Crear el archivo `/Users/avantiam/Desktop/proyectos personales/blogger/.env.example` con este contenido exacto:

```dotenv
###> symfony/framework-bundle ###
APP_ENV=dev
# Generate with: php bin/console secret:generate-tokens
APP_SECRET=change-me-generate-a-secure-random-string
###< symfony/framework-bundle ###

###> symfony/mailer ###
# MAILER_DSN=smtp://localhost
###< symfony/mailer ###

###> doctrine/doctrine-bundle ###
# MySQL:
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/myblogger?serverVersion=5.7"
# SQLite (desarrollo rápido sin MySQL):
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
###< doctrine/doctrine-bundle ###
```

- [ ] **Step 9: Commit**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
git add .env.example
git commit -m "security: add .env.example, .env purged from history"
```

---

### Task 2: Eliminar directorio ~/ y public/uploads del tracking

**Files:**
- Eliminar del tracking: `~/` (directorio VS Code accidental)
- Eliminar del tracking: `public/uploads/`

- [ ] **Step 1: Eliminar el directorio ~ del tracking de git**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
git rm -r --cached "~"
```

Expected: líneas `rm '~/.config/Code/User/settings.json'`

- [ ] **Step 2: Verificar que el directorio local no se eliminó (solo del tracking)**

```bash
ls "~/.config/Code/User/settings.json"
```

Expected: el archivo existe localmente. Si no existía, no importa.

- [ ] **Step 3: Eliminar public/uploads/ del tracking**

```bash
git rm -r --cached public/uploads/
```

Expected: líneas `rm 'public/uploads/bloggerfotos/...'` para cada imagen.

- [ ] **Step 4: Commit**

```bash
git commit -m "chore: stop tracking VS Code settings and uploaded images"
```

---

### Task 3: Actualizar .gitignore

**Files:**
- Modificar: `.gitignore`

- [ ] **Step 1: Reemplazar .gitignore con versión para Symfony 5**

Reemplazar el contenido completo de `/Users/avantiam/Desktop/proyectos personales/blogger/.gitignore`:

```gitignore
###> symfony/framework-bundle ###
/.env
/.env.local
/.env.*.local
/config/secrets/prod/prod.decrypt.private.php
/public/bundles/
/var/
/vendor/
###< symfony/framework-bundle ###

###> uploads y assets generados ###
/public/uploads/
/public/build/
###< uploads y assets generados ###

###> node ###
/node_modules/
package-lock.json
###< node ###

###> PHP ###
/composer.phar
###< PHP ###

###> PHPUnit ###
/phpunit.xml
###< PHPUnit ###

###> IDE ###
.idea/
.vscode/
~
/.DS_Store
###< IDE ###
```

- [ ] **Step 2: Commit**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
git add .gitignore
git commit -m "chore: update .gitignore to Symfony 5 patterns"
```

---

### Task 4: Escribir README.md

**Files:**
- Modificar: `README.md`

- [ ] **Step 1: Escribir README.md con instrucciones completas**

Reemplazar el contenido de `/Users/avantiam/Desktop/proyectos personales/blogger/README.md`:

```markdown
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
```

- [ ] **Step 2: Commit**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
git add README.md
git commit -m "docs: write installation and project structure README"
```

---

## FASE 2 — Capa de Services

### Task 5: Crear BloggerService

**Files:**
- Crear: `src/Service/BloggerService.php`

- [ ] **Step 1: Crear el archivo BloggerService.php**

Crear `/Users/avantiam/Desktop/proyectos personales/blogger/src/Service/BloggerService.php`:

```php
<?php

namespace App\Service;

use App\Entity\Blogger;
use App\Entity\User;
use App\Repository\BloggerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class BloggerService
{
    private EntityManagerInterface $em;
    private SluggerInterface $slugger;
    private PaginatorInterface $paginator;
    private BloggerRepository $bloggerRepository;
    private string $uploadDirectory;

    public function __construct(
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        PaginatorInterface $paginator,
        BloggerRepository $bloggerRepository,
        string $uploadDirectory
    ) {
        $this->em = $em;
        $this->slugger = $slugger;
        $this->paginator = $paginator;
        $this->bloggerRepository = $bloggerRepository;
        $this->uploadDirectory = $uploadDirectory;
    }

    public function createPost(Blogger $blog, ?UploadedFile $picture, User $user): void
    {
        if ($picture) {
            $blog->setPicture($this->uploadPicture($picture));
        }

        $blog->setAuthor($user->getUsername());
        $blog->setUser($user);

        $this->em->persist($blog);
        $this->em->flush();
    }

    public function updatePost(Blogger $blog, ?UploadedFile $picture): void
    {
        if ($picture) {
            $blog->setPicture($this->uploadPicture($picture));
        }

        $this->em->persist($blog);
        $this->em->flush();
    }

    public function getPaginatedPosts(int $page): object
    {
        $query = $this->bloggerRepository->ListBlogger();

        return $this->paginator->paginate($query, $page, 10);
    }

    public function getUserPosts(int $userId, int $page): object
    {
        $query = $this->bloggerRepository->MyBlogs($userId);

        return $this->paginator->paginate($query, $page, 10);
    }

    private function uploadPicture(UploadedFile $picture): string
    {
        $originalFilename = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $picture->guessExtension();

        try {
            $picture->move($this->uploadDirectory, $newFilename);
        } catch (FileException $e) {
            throw new \RuntimeException('Error al subir la imagen: ' . $e->getMessage());
        }

        return $newFilename;
    }
}
```

- [ ] **Step 2: Registrar el parámetro uploadDirectory en services.yaml**

Abrir `/Users/avantiam/Desktop/proyectos personales/blogger/config/services.yaml` y añadir la definición explícita del servicio justo antes de la última línea del bloque `services:`:

```yaml
    App\Service\BloggerService:
        arguments:
            $uploadDirectory: '%bloggerfotos_directory%'
```

El bloque `services:` del archivo quedará así al final:

```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\:
        resource: '../src/'
        exclude:
            - '../src/DependencyInjection/'
            - '../src/Entity/'
            - '../src/Kernel.php'
            - '../src/Tests/'

    App\Controller\:
        resource: '../src/Controller/'
        tags: ['controller.service_arguments']

    App\Service\BloggerService:
        arguments:
            $uploadDirectory: '%bloggerfotos_directory%'
```

- [ ] **Step 3: Verificar que Symfony puede cargar el contenedor de servicios**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
php bin/console debug:container App\\Service\\BloggerService
```

Expected: muestra la definición del servicio con sus argumentos.

- [ ] **Step 4: Commit**

```bash
git add src/Service/BloggerService.php config/services.yaml
git commit -m "feat: add BloggerService with upload and pagination logic"
```

---

### Task 6: Refactorizar BloggerController para usar BloggerService

**Files:**
- Modificar: `src/Controller/BloggerController.php`

- [ ] **Step 1: Reemplazar BloggerController.php**

Reemplazar el contenido completo de `/Users/avantiam/Desktop/proyectos personales/blogger/src/Controller/BloggerController.php`:

```php
<?php

namespace App\Controller;

use App\Entity\Blogger;
use App\Form\BloggerType;
use App\Service\BloggerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BloggerController extends AbstractController
{
    private BloggerService $bloggerService;

    public function __construct(BloggerService $bloggerService)
    {
        $this->bloggerService = $bloggerService;
    }

    /**
     * @Route("/blogger", name="blogger")
     */
    public function add(Request $request): Response
    {
        $blog = new Blogger();
        $form = $this->createForm(BloggerType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form->get('picture')->getData();
            $this->bloggerService->createPost($blog, $picture, $this->getUser());

            return $this->redirectToRoute('mis-blog');
        }

        return $this->render('blogger/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit-blogger/{id}", name="editblogger")
     */
    public function edit(int $id, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $blog = $em->getRepository(Blogger::class)->find($id);

        if ($blog->getUser() !== $this->getUser()) {
            $this->addFlash('error_permisos', Blogger::ERROR_PERMISOS);
            return $this->redirectToRoute('mis-blog');
        }

        $form = $this->createForm(BloggerType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form->get('picture')->getData();
            $this->bloggerService->updatePost($blog, $picture);

            return $this->redirectToRoute('mis-blog');
        }

        return $this->render('blogger/edit.html.twig', [
            'form' => $form->createView(),
            'blog' => $blog,
        ]);
    }

    /**
     * @Route("/blog/{title}", name="ver-blog")
     */
    public function blog(string $title): Response
    {
        $em = $this->getDoctrine()->getManager();
        $blog = $em->getRepository(Blogger::class)->findOneBy(['title' => $title]);

        return $this->render('blogger/blog.html.twig', [
            'blog' => $blog,
        ]);
    }

    /**
     * @Route("/blogs", name="mis-blog")
     */
    public function blogs(Request $request): Response
    {
        $pagination = $this->bloggerService->getPaginatedPosts(
            $request->query->getInt('page', 1)
        );

        return $this->render('blogger/blogs.html.twig', [
            'blog' => $pagination,
        ]);
    }

    /**
     * @Route("/entradas", name="mis-entradas")
     */
    public function entradas(Request $request): Response
    {
        $pagination = $this->bloggerService->getUserPosts(
            $this->getUser()->getId(),
            $request->query->getInt('page', 1)
        );

        return $this->render('blogger/blogs.html.twig', [
            'blog' => $pagination,
        ]);
    }
}
```

- [ ] **Step 2: Verificar que no hay errores de sintaxis**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
php -l src/Controller/BloggerController.php
```

Expected: `No syntax errors detected`

- [ ] **Step 3: Verificar que el contenedor carga correctamente**

```bash
php bin/console cache:clear
```

Expected: `Cache for the "dev" environment (debug=true) was successfully cleared.`

- [ ] **Step 4: Commit**

```bash
git add src/Controller/BloggerController.php
git commit -m "refactor: BloggerController delegates logic to BloggerService"
```

---

### Task 7: Crear UserService

**Files:**
- Crear: `src/Service/UserService.php`

- [ ] **Step 1: Crear UserService.php**

Crear `/Users/avantiam/Desktop/proyectos personales/blogger/src/Service/UserService.php`:

```php
<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    private EntityManagerInterface $em;
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function register(User $user, string $plainPassword): void
    {
        $user->setPassword(
            $this->passwordEncoder->encodePassword($user, $plainPassword)
        );
        $this->em->persist($user);
        $this->em->flush();
    }
}
```

- [ ] **Step 2: Verificar sintaxis**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
php -l src/Service/UserService.php
```

Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add src/Service/UserService.php
git commit -m "feat: add UserService with register logic"
```

---

### Task 8: Refactorizar RegisterController para usar UserService

**Files:**
- Modificar: `src/Controller/RegisterController.php`

- [ ] **Step 1: Reemplazar RegisterController.php**

Reemplazar el contenido de `/Users/avantiam/Desktop/proyectos personales/blogger/src/Controller/RegisterController.php`:

```php
<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @Route("/register", name="register")
     */
    public function index(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->register($user, $form['password']->getData());
            $this->addFlash('exito', User::REGISTRO_EXITOSO);

            return $this->redirectToRoute('register');
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
```

- [ ] **Step 2: Verificar y limpiar caché**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
php -l src/Controller/RegisterController.php
php bin/console cache:clear
```

Expected: sin errores.

- [ ] **Step 3: Commit**

```bash
git add src/Controller/RegisterController.php
git commit -m "refactor: RegisterController delegates to UserService"
```

---

### Task 9: Crear ContactService y refactorizar ContactsController

**Files:**
- Crear: `src/Service/ContactService.php`
- Modificar: `src/Controller/ContactsController.php`

- [ ] **Step 1: Crear ContactService.php**

Crear `/Users/avantiam/Desktop/proyectos personales/blogger/src/Service/ContactService.php`:

```php
<?php

namespace App\Service;

use App\Entity\Contacts;
use Doctrine\ORM\EntityManagerInterface;

class ContactService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function save(Contacts $contact): void
    {
        $this->em->persist($contact);
        $this->em->flush();
    }
}
```

- [ ] **Step 2: Reemplazar ContactsController.php**

Reemplazar el contenido de `/Users/avantiam/Desktop/proyectos personales/blogger/src/Controller/ContactsController.php`:

```php
<?php

namespace App\Controller;

use App\Entity\Contacts;
use App\Form\ContactsType;
use App\Service\ContactService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactsController extends AbstractController
{
    private ContactService $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    /**
     * @Route("/contacts", name="contacts")
     */
    public function index(Request $request): Response
    {
        $contact = new Contacts();
        $form = $this->createForm(ContactsType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->contactService->save($contact);

            return $this->redirectToRoute('home');
        }

        return $this->render('contacts/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
```

- [ ] **Step 3: Verificar y limpiar caché**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
php -l src/Service/ContactService.php
php -l src/Controller/ContactsController.php
php bin/console cache:clear
```

- [ ] **Step 4: Commit**

```bash
git add src/Service/ContactService.php src/Controller/ContactsController.php
git commit -m "feat: add ContactService, ContactsController delegates to it"
```

---

## FASE 3 — Mejoras de código

### Task 10: Renombrar Contacts → Contact

Esta tarea renombra la entidad, repositorio y formulario de plural a singular (convención Doctrine). Se hacen todos los cambios en un solo commit para mantener consistencia.

**Files:**
- Renombrar + modificar: `Contacts.php` → `Contact.php`
- Renombrar + modificar: `ContactsRepository.php` → `ContactRepository.php`
- Renombrar + modificar: `ContactsType.php` → `ContactType.php`
- Modificar: `ContactsController.php`
- Modificar: `ContactService.php`

- [ ] **Step 1: Crear src/Entity/Contact.php (nuevo)**

Crear `/Users/avantiam/Desktop/proyectos personales/blogger/src/Entity/Contact.php`:

```php
<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ContactRepository::class)
 */
class Contact
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }
}
```

- [ ] **Step 2: Crear src/Repository/ContactRepository.php (nuevo)**

Crear `/Users/avantiam/Desktop/proyectos personales/blogger/src/Repository/ContactRepository.php`:

```php
<?php

namespace App\Repository;

use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Contact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contact[]    findAll()
 * @method Contact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }
}
```

- [ ] **Step 3: Crear src/Form/ContactType.php (nuevo)**

Crear `/Users/avantiam/Desktop/proyectos personales/blogger/src/Form/ContactType.php`:

```php
<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nombre'])
            ->add('email', EmailType::class, ['label' => 'Correo'])
            ->add('message', TextareaType::class, ['label' => 'Mensaje'])
            ->add('Registrar', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
```

- [ ] **Step 4: Actualizar ContactService.php para usar Contact**

Reemplazar el contenido de `/Users/avantiam/Desktop/proyectos personales/blogger/src/Service/ContactService.php`:

```php
<?php

namespace App\Service;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;

class ContactService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function save(Contact $contact): void
    {
        $this->em->persist($contact);
        $this->em->flush();
    }
}
```

- [ ] **Step 5: Actualizar ContactsController.php para usar Contact y ContactType**

Reemplazar el contenido de `/Users/avantiam/Desktop/proyectos personales/blogger/src/Controller/ContactsController.php`:

```php
<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Service\ContactService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactsController extends AbstractController
{
    private ContactService $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    /**
     * @Route("/contacts", name="contacts")
     */
    public function index(Request $request): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->contactService->save($contact);

            return $this->redirectToRoute('home');
        }

        return $this->render('contacts/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
```

- [ ] **Step 6: Eliminar archivos viejos de Contacts (plural)**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
git rm src/Entity/Contacts.php
git rm src/Repository/ContactsRepository.php
git rm src/Form/ContactsType.php
```

- [ ] **Step 7: Verificar que el contenedor carga correctamente**

```bash
php bin/console cache:clear
```

Expected: sin errores.

- [ ] **Step 8: Commit**

```bash
git add src/Entity/Contact.php src/Repository/ContactRepository.php src/Form/ContactType.php \
        src/Service/ContactService.php src/Controller/ContactsController.php
git commit -m "refactor: rename Contacts to Contact (singular), ContactsType to ContactType"
```

---

### Task 11: Renombrar métodos de BloggerRepository

**Files:**
- Modificar: `src/Repository/BloggerRepository.php`
- Modificar: `src/Service/BloggerService.php`

- [ ] **Step 1: Actualizar BloggerRepository.php**

Reemplazar el contenido de `/Users/avantiam/Desktop/proyectos personales/blogger/src/Repository/BloggerRepository.php`:

```php
<?php

namespace App\Repository;

use App\Entity\Blogger;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Blogger|null find($id, $lockMode = null, $lockVersion = null)
 * @method Blogger|null findOneBy(array $criteria, array $orderBy = null)
 * @method Blogger[]    findAll()
 * @method Blogger[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BloggerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blogger::class);
    }

    public function findAllOrderedByDate(): \Doctrine\ORM\Query
    {
        return $this->getEntityManager()->createQuery('
            SELECT blog.id, blog.title, blog.author, blog.picture, blog.text, blog.date, user.username
            FROM App:Blogger blog
            JOIN blog.user user
            ORDER BY blog.id DESC
        ');
    }

    public function findByUserId(int $userId): \Doctrine\ORM\Query
    {
        return $this->getEntityManager()->createQuery('
            SELECT blog.id, blog.title, blog.author, blog.picture, blog.text, blog.date, user.username
            FROM App:Blogger blog
            JOIN blog.user user
            WHERE user.id = :userId
            ORDER BY blog.id DESC
        ')->setParameter('userId', $userId);
    }

    public function findBySlug(string $slug): ?Blogger
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
```

- [ ] **Step 2: Actualizar BloggerService.php para usar los nuevos nombres de método**

Reemplazar en `/Users/avantiam/Desktop/proyectos personales/blogger/src/Service/BloggerService.php` los dos métodos de paginación:

```php
    public function getPaginatedPosts(int $page): object
    {
        $query = $this->bloggerRepository->findAllOrderedByDate();

        return $this->paginator->paginate($query, $page, 10);
    }

    public function getUserPosts(int $userId, int $page): object
    {
        $query = $this->bloggerRepository->findByUserId($userId);

        return $this->paginator->paginate($query, $page, 10);
    }
```

El archivo completo de `src/Service/BloggerService.php` debe quedar:

```php
<?php

namespace App\Service;

use App\Entity\Blogger;
use App\Entity\User;
use App\Repository\BloggerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class BloggerService
{
    private EntityManagerInterface $em;
    private SluggerInterface $slugger;
    private PaginatorInterface $paginator;
    private BloggerRepository $bloggerRepository;
    private string $uploadDirectory;

    public function __construct(
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        PaginatorInterface $paginator,
        BloggerRepository $bloggerRepository,
        string $uploadDirectory
    ) {
        $this->em = $em;
        $this->slugger = $slugger;
        $this->paginator = $paginator;
        $this->bloggerRepository = $bloggerRepository;
        $this->uploadDirectory = $uploadDirectory;
    }

    public function createPost(Blogger $blog, ?UploadedFile $picture, User $user): void
    {
        if ($picture) {
            $blog->setPicture($this->uploadPicture($picture));
        }

        $blog->setAuthor($user->getUsername());
        $blog->setUser($user);

        $this->em->persist($blog);
        $this->em->flush();
    }

    public function updatePost(Blogger $blog, ?UploadedFile $picture): void
    {
        if ($picture) {
            $blog->setPicture($this->uploadPicture($picture));
        }

        $this->em->persist($blog);
        $this->em->flush();
    }

    public function getPaginatedPosts(int $page): object
    {
        $query = $this->bloggerRepository->findAllOrderedByDate();

        return $this->paginator->paginate($query, $page, 10);
    }

    public function getUserPosts(int $userId, int $page): object
    {
        $query = $this->bloggerRepository->findByUserId($userId);

        return $this->paginator->paginate($query, $page, 10);
    }

    private function uploadPicture(UploadedFile $picture): string
    {
        $originalFilename = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $picture->guessExtension();

        try {
            $picture->move($this->uploadDirectory, $newFilename);
        } catch (FileException $e) {
            throw new \RuntimeException('Error al subir la imagen: ' . $e->getMessage());
        }

        return $newFilename;
    }
}
```

- [ ] **Step 3: Verificar sintaxis**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
php -l src/Repository/BloggerRepository.php
php -l src/Service/BloggerService.php
php bin/console cache:clear
```

Expected: sin errores.

- [ ] **Step 4: Commit**

```bash
git add src/Repository/BloggerRepository.php src/Service/BloggerService.php
git commit -m "refactor: rename ListBlogger/MyBlogs to English, add findBySlug"
```

---

### Task 12: Añadir campo slug a Blogger y actualizar rutas

**Files:**
- Modificar: `src/Entity/Blogger.php`
- Modificar: `src/Service/BloggerService.php`
- Modificar: `src/Controller/BloggerController.php`
- Modificar: `templates/blogger/blogs.html.twig`

- [ ] **Step 1: Añadir campo slug a src/Entity/Blogger.php**

Reemplazar el contenido completo de `/Users/avantiam/Desktop/proyectos personales/blogger/src/Entity/Blogger.php`:

```php
<?php

namespace App\Entity;

use App\Repository\BloggerRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BloggerRepository::class)
 */
class Blogger
{
    const ERROR_PERMISOS = "Este Blog no le pertenece";

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $picture;

    /**
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="blogger")
     */
    private $user;

    public function __construct()
    {
        $this->date = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture): self
    {
        $this->picture = $picture;
        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
```

- [ ] **Step 2: Actualizar BloggerService para generar el slug al crear un post**

En `src/Service/BloggerService.php`, actualizar el método `createPost` para generar el slug:

```php
    public function createPost(Blogger $blog, ?UploadedFile $picture, User $user): void
    {
        if ($picture) {
            $blog->setPicture($this->uploadPicture($picture));
        }

        $slug = (string) $this->slugger->slug($blog->getTitle())->lower();
        $blog->setSlug($slug);
        $blog->setAuthor($user->getUsername());
        $blog->setUser($user);

        $this->em->persist($blog);
        $this->em->flush();
    }
```

- [ ] **Step 3: Actualizar la ruta ver-blog en BloggerController para usar slug**

En `src/Controller/BloggerController.php`, reemplazar el método `blog`:

```php
    /**
     * @Route("/blog/{slug}", name="ver-blog")
     */
    public function blog(string $slug): Response
    {
        $blog = $this->getDoctrine()
            ->getRepository(Blogger::class)
            ->findBySlug($slug);

        if (!$blog) {
            throw $this->createNotFoundException('Entrada no encontrada.');
        }

        return $this->render('blogger/blog.html.twig', [
            'blog' => $blog,
        ]);
    }
```

- [ ] **Step 4: Actualizar BloggerRepository::findAllOrderedByDate para incluir slug**

En `src/Repository/BloggerRepository.php`, actualizar la query en `findAllOrderedByDate`:

```php
    public function findAllOrderedByDate(): \Doctrine\ORM\Query
    {
        return $this->getEntityManager()->createQuery('
            SELECT blog.id, blog.title, blog.slug, blog.author, blog.picture, blog.text, blog.date, user.username
            FROM App:Blogger blog
            JOIN blog.user user
            ORDER BY blog.id DESC
        ');
    }

    public function findByUserId(int $userId): \Doctrine\ORM\Query
    {
        return $this->getEntityManager()->createQuery('
            SELECT blog.id, blog.title, blog.slug, blog.author, blog.picture, blog.text, blog.date, user.username
            FROM App:Blogger blog
            JOIN blog.user user
            WHERE user.id = :userId
            ORDER BY blog.id DESC
        ')->setParameter('userId', $userId);
    }
```

- [ ] **Step 5: Actualizar blogs.html.twig para usar slug en el link**

En `/Users/avantiam/Desktop/proyectos personales/blogger/templates/blogger/blogs.html.twig`, reemplazar la línea del link "Leer más":

Antes:
```twig
<a class="btn btn-light pull-right marginBottom10" href="{{ path('ver-blog', {title: blog.title}) }}">Leer más</a>
```

Después:
```twig
<a class="btn btn-light pull-right marginBottom10" href="{{ path('ver-blog', {slug: blog.slug}) }}">Leer más</a>
```

- [ ] **Step 6: Verificar sintaxis de todos los archivos modificados**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
php -l src/Entity/Blogger.php
php -l src/Service/BloggerService.php
php -l src/Controller/BloggerController.php
php -l src/Repository/BloggerRepository.php
php bin/console cache:clear
```

Expected: sin errores.

- [ ] **Step 7: Commit**

```bash
git add src/Entity/Blogger.php src/Service/BloggerService.php \
        src/Controller/BloggerController.php src/Repository/BloggerRepository.php \
        templates/blogger/blogs.html.twig
git commit -m "feat: add slug field to Blogger, use slug in ver-blog route"
```

---

### Task 13: Generar migración inicial

**Prerequisito:** MySQL corriendo con la BD `myblogger` ya creada. Si no existe: `php bin/console doctrine:database:create`.

**Files:**
- Crear: `migrations/VersionXXXXXXXXXXXXXX.php` (generado automáticamente)

- [ ] **Step 1: Verificar la conexión a la BD**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
php bin/console doctrine:database:create --if-not-exists
```

Expected: `Created database "myblogger"` o `Database "myblogger" for connection named default already exists.`

- [ ] **Step 2: Generar la migración**

```bash
php bin/console make:migration
```

Expected: `[OK] Next: Review the new migration then run it with php bin/console doctrine:migrations:migrate`

El comando crea un archivo en `migrations/` con un nombre tipo `Version20260530XXXXXX.php`.

- [ ] **Step 3: Revisar el archivo de migración generado**

Abrir el archivo generado en `migrations/` y verificar que contiene `CREATE TABLE blogger` con la columna `slug`, y `CREATE TABLE contact` (nueva tabla singular). Si ves `CREATE TABLE contacts` (plural), hay un problema — la entidad `Contact` no está siendo detectada. En ese caso, ejecutar `php bin/console cache:clear` y regenerar.

- [ ] **Step 4: Ejecutar la migración**

```bash
php bin/console doctrine:migrations:migrate
```

Expected: `[notice] Migrating up to Version20260530XXXXXX`

- [ ] **Step 5: Commit del archivo de migración**

```bash
git add migrations/
git commit -m "feat: add initial database migration with slug field"
```

---

## FASE 4 — Webpack Encore

### Task 14: Instalar y configurar Webpack Encore

**Files:**
- Crear: `webpack.config.js`
- Crear: `assets/app.js`
- Crear: `assets/styles/app.css`
- Modificar: `templates/base.html.twig`

- [ ] **Step 1: Instalar el bundle de Webpack Encore**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
composer require symfony/webpack-encore-bundle
```

Expected: `Webpack Encore Bundle` instalado. Crea `webpack.config.js` y `assets/` si no existen.

- [ ] **Step 2: Instalar dependencias npm**

```bash
npm install
npm install --save-dev @symfony/webpack-encore bootstrap@4 @popperjs/core jquery
```

Expected: `node_modules/` creado con las dependencias.

- [ ] **Step 3: Reemplazar webpack.config.js**

Reemplazar el contenido de `/Users/avantiam/Desktop/proyectos personales/blogger/webpack.config.js`:

```javascript
const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
```

- [ ] **Step 4: Copiar el CSS existente a assets/styles/app.css**

Leer el contenido de `public/css/styles.css` y crear `/Users/avantiam/Desktop/proyectos personales/blogger/assets/styles/app.css` con ese mismo contenido.

- [ ] **Step 5: Crear assets/app.js**

Crear `/Users/avantiam/Desktop/proyectos personales/blogger/assets/app.js`:

```javascript
import './styles/app.css';

import $ from 'jquery';
import 'bootstrap';

global.$ = global.jQuery = $;
```

- [ ] **Step 6: Actualizar templates/base.html.twig para usar Encore**

Reemplazar el contenido de `/Users/avantiam/Desktop/proyectos personales/blogger/templates/base.html.twig`:

```twig
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>
            {% block title %}
                Blogger
            {% endblock %}
        </title>
        {% block stylesheets %}
            {{ encore_entry_link_tags('app') }}
        {% endblock %}

        {% block javascripts %}
            {{ encore_entry_script_tags('app') }}
        {% endblock %}
    </head>
    <body>
        <div class="container">
            {% block body %}
                <nav class="navbar navbar-dark bg-dark justify-content-between">
                    <a class="navbar-brand" href="{{ path('home') }}">Blogger</a>
                    <ul class="nav">
                    {% if is_granted("IS_AUTHENTICATED_FULLY") %}
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                            <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.226 5.468 2.37A7 7 0 0 0 8 1z"/>
                            </svg>
                            <strong>{{ app.user.username }}</strong>
                            <span class="glyphicon glyphicon-chevron-down"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ path('mis-entradas') }}">Mis Entradas</a>
                            </li>
                            <li>
                                <div class="navbar-login">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <p class="text-left">{{ app.user.email }}</p>
                                            <p class="text-left">
                                                <a href="{{ path('app_logout') }}" class="btn btn-primary btn-block">Logout</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </li>
                    {% else %}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ path('app_login') }}">Login</a>
                    </li>
                    {% endif %}
                    </ul>
                </nav>
                <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="nav navbar-nav navbar-logo mx-auto">
                            <li class="nav-item center">
                                <a class="nav-link" href="{{ path('home') }}">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ path('mis-blog') }}">Blog</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ path('contacts') }}">Contacto</a>
                            </li>
                        </ul>
                    </div>
                </nav>
            {% endblock %}
        </div>
    </body>
</html>
```

- [ ] **Step 7: Compilar los assets**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
npm run dev
```

Expected: `webpack compiled successfully` sin errores. Crea `public/build/` con `app.js`, `app.css` y manifests.

- [ ] **Step 8: Commit**

```bash
git add webpack.config.js assets/ templates/base.html.twig package.json
git commit -m "feat: add Webpack Encore, migrate Bootstrap/jQuery to npm"
```

---

### Task 15: Eliminar Bootstrap y jQuery de /public/

**Files:**
- Eliminar: `public/css/bootstrap/` (directorio completo)
- Eliminar: `public/js/bootstrap*.js`, `public/js/jquery.min.js`
- Eliminar: `public/css/styles.css` (movido a assets/)

- [ ] **Step 1: Eliminar los archivos vendor de /public/ del tracking git**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
git rm -r public/css/bootstrap/
git rm public/js/bootstrap.bundle.js public/js/bootstrap.bundle.js.map
git rm public/js/bootstrap.bundle.min.js public/js/bootstrap.bundle.min.js.map
git rm public/js/bootstrap.js public/js/bootstrap.js.map
git rm public/js/bootstrap.min.js public/js/bootstrap.min.js.map
git rm public/js/jquery.min.js
git rm public/css/styles.css
```

- [ ] **Step 2: Verificar que la app sigue cargando correctamente**

```bash
php bin/console cache:clear
symfony serve --no-tls -d
```

Abrir `http://localhost:8000/home` en el navegador. Expected: la página carga con estilos Bootstrap correctos (navbar visible). Si no hay estilos, verificar que `npm run dev` se ejecutó correctamente en Task 14.

Detener el servidor: `symfony server:stop`

- [ ] **Step 3: Commit**

```bash
git commit -m "chore: remove Bootstrap/jQuery vendor files from /public/, served via Webpack now"
```

---

## FASE 5 — Tests

### Task 16: Configurar entorno de test con SQLite

**Files:**
- Modificar: `.env.test`

- [ ] **Step 1: Añadir DATABASE_URL de SQLite a .env.test**

Añadir esta línea al final de `/Users/avantiam/Desktop/proyectos personales/blogger/.env.test`:

```dotenv
DATABASE_URL="sqlite:///%kernel.project_dir%/var/test.db"
```

- [ ] **Step 2: Crear el esquema en la BD de test**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
php bin/console doctrine:schema:create --env=test
```

Expected: `[OK] Database schema created successfully!`

- [ ] **Step 3: Verificar que el esquema se creó**

```bash
ls var/test.db
```

Expected: el archivo existe.

- [ ] **Step 4: Commit**

```bash
git add .env.test
git commit -m "test: configure SQLite database for test environment"
```

---

### Task 17: Tests funcionales para BloggerController

**Files:**
- Crear: `tests/Controller/BloggerControllerTest.php`

- [ ] **Step 1: Crear el directorio y el archivo de test**

```bash
mkdir -p "/Users/avantiam/Desktop/proyectos personales/blogger/tests/Controller"
```

Crear `/Users/avantiam/Desktop/proyectos personales/blogger/tests/Controller/BloggerControllerTest.php`:

```php
<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BloggerControllerTest extends WebTestCase
{
    public function testBlogsListReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blogs');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('nav.navbar');
    }

    public function testCreatePostRedirectsToLoginWhenUnauthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blogger');

        $this->assertResponseRedirects('/login');
    }

    public function testMyEntriesRedirectsToLoginWhenUnauthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/entradas');

        $this->assertResponseRedirects('/login');
    }
}
```

- [ ] **Step 2: Ejecutar el test y verificar que pasa**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
php bin/phpunit tests/Controller/BloggerControllerTest.php --testdox
```

Expected:
```
BloggerController
 ✔ Blogs list returns 200
 ✔ Create post redirects to login when unauthenticated
 ✔ My entries redirects to login when unauthenticated
```

Si falla con error de ruta `/login`, verificar que la ruta `app_login` existe: `php bin/console debug:router | grep login`.

- [ ] **Step 3: Commit**

```bash
git add tests/Controller/BloggerControllerTest.php
git commit -m "test: add functional tests for BloggerController"
```

---

### Task 18: Tests funcionales para ContactsController y SecurityController

**Files:**
- Crear: `tests/Controller/ContactsControllerTest.php`
- Crear: `tests/Controller/SecurityControllerTest.php`

- [ ] **Step 1: Crear ContactsControllerTest.php**

Crear `/Users/avantiam/Desktop/proyectos personales/blogger/tests/Controller/ContactsControllerTest.php`:

```php
<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactsControllerTest extends WebTestCase
{
    public function testContactFormRendersCorrectly(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contacts');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="contact[name]"]');
        $this->assertSelectorExists('input[name="contact[email]"]');
        $this->assertSelectorExists('textarea[name="contact[message]"]');
    }

    public function testContactFormSubmitWithValidDataRedirects(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contacts');

        $form = $crawler->selectButton('Registrar')->form([
            'contact[name]'    => 'Test User',
            'contact[email]'   => 'test@example.com',
            'contact[message]' => 'Este es un mensaje de prueba',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/home');
    }
}
```

- [ ] **Step 2: Crear SecurityControllerTest.php**

Crear `/Users/avantiam/Desktop/proyectos personales/blogger/tests/Controller/SecurityControllerTest.php`:

```php
<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageRendersCorrectly(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="email"]');
        $this->assertSelectorExists('input[name="password"]');
    }

    public function testLoginWithInvalidCredentialsShowsError(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Login')->form([
            'email'    => 'noeexiste@example.com',
            'password' => 'contraseña_incorrecta',
        ]);

        $client->submit($form);
        $client->followRedirect();

        $this->assertSelectorExists('.alert-danger');
    }
}
```

- [ ] **Step 3: Ejecutar todos los tests**

```bash
cd "/Users/avantiam/Desktop/proyectos personales/blogger"
php bin/phpunit tests/Controller/ --testdox
```

Expected:
```
BloggerController
 ✔ Blogs list returns 200
 ✔ Create post redirects to login when unauthenticated
 ✔ My entries redirects to login when unauthenticated

ContactsController
 ✔ Contact form renders correctly
 ✔ Contact form submit with valid data redirects

SecurityController
 ✔ Login page renders correctly
 ✔ Login with invalid credentials shows error
```

- [ ] **Step 4: Commit final**

```bash
git add tests/Controller/ContactsControllerTest.php tests/Controller/SecurityControllerTest.php
git commit -m "test: add functional tests for Contacts and Security controllers"
```

---

## Verificación final

- [ ] Ejecutar todos los tests: `php bin/phpunit --testdox`
- [ ] Verificar que git no rastrea `.env`: `git ls-files .env` → sin output
- [ ] Verificar que git no rastrea `~/`: `git ls-files "~"` → sin output
- [ ] Verificar que git no rastrea `public/uploads/`: `git ls-files public/uploads/` → sin output
- [ ] Verificar que el contenedor DI está limpio: `php bin/console debug:container --env=prod 2>&1 | grep -i error` → sin output
- [ ] Verificar que los assets compilan: `npm run build` → `webpack compiled successfully`
- [ ] Verificar que `git log --all -- .env` no muestra commits con el archivo
