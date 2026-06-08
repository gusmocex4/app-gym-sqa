# App Gym / FitZone

FitZone is a small PHP 8 MVC gym website with a public landing page, authentication screens, and basic admin/user dashboard routes. The project is built with plain PHP, Azure Cosmos DB for MongoDB-compatible storage, and a Gulp/Sass front-end pipeline.

## Stack

- PHP 8.2 with Apache
- Azure Cosmos DB for MongoDB
- MongoDB PHP extension
- Composer autoloading
- Gulp, Sass, PostCSS, and JavaScript asset compilation
- Clean URLs handled through `public/.htaccess`

## Project Layout

- `public/index.php` - web entry point and route definitions
- `Router.php` - minimal router used by the app
- `controllers/` - request handlers for login, landing, admin, and user pages
- `models/` - Active Record base class and the `Usuario` model
- `views/` - page templates and shared alert partials
- `includes/` - bootstrap file, database connection, and helper functions
- `src/` - source SCSS, JavaScript, images, and media assets
- `public/build/` - compiled assets served by the browser
- `database.mongodb.json` - seed users for Cosmos DB for MongoDB

## Requirements

- PHP 8.2 or compatible
- MongoDB-compatible database connection string
- Composer
- Node.js and npm
- Apache or another web server that supports URL rewriting
- Docker and Docker Compose if you want the quickest local setup

## Local Setup

### Option 1: Docker

This is the easiest path for container validation. The app container expects a MongoDB-compatible connection string, which is what Azure Cosmos DB for MongoDB provides.

1. Build and start the containers:

```bash
docker compose up --build
```

2. Pass the Mongo settings into the container environment:

```bash
export MONGODB_URI="<your-cosmos-or-mongodb-uri>"
export MONGODB_DATABASE="appgym"
export MONGODB_USERS_COLLECTION="usuarios"
```

3. Open the app at:

```text
http://localhost:8080
```

### Option 2: Local PHP

1. Provision a MongoDB-compatible database such as Azure Cosmos DB for MongoDB.
2. Import the seed data from `database.mongodb.json` into the `usuarios` collection.
3. Set these environment variables:

```text
MONGODB_URI
MONGODB_DATABASE
MONGODB_USERS_COLLECTION
```

4. Install PHP dependencies:

```bash
composer install
```

5. Install front-end dependencies:

```bash
npm install
```

6. Compile the assets:

```bash
npm run gulp
```

7. Serve the `public/` directory through Apache or another web server with rewrite support.

## QA Login Data

The repository ships with sample users in `database.mongodb.json`:

- Admin: `admin@example.com` / `123456`
- User: `user@example.com` / `123456`

Use these only for local QA. Change them before any non-test deployment.

## Main Routes

- `/` - landing page
- `/login` - login form and authentication
- `/crear-cuenta` - account creation
- `/olvide` - forgot-password screen
- `/recuperar` - password recovery handler stub
- `/logout` - clears the session and returns to the landing page
- `/inicio-admin` - admin dashboard
- `/inicio-user` - user dashboard

## Known Limitations

- Password recovery is not implemented yet; the UI now shows a placeholder error instead of silently failing.
- Admin and user sub-pages currently render the same placeholder dashboard content instead of separate management screens.
- The project does not include an automated test suite in the repository.
- The route names in `public/index.php` are case-sensitive, so links like `/Planes`, `/Suscripciones`, and `/Clases` must match exactly.
- The current persistence layer only covers the user/auth flow in Cosmos DB for MongoDB terms. If you add more data-backed features, keep them document-oriented or add a proper repository layer first.

## Azure Deployment

The Azure deployment path is documented in [AZURE_DEPLOYMENT_GUIDE.md](/Users/unidentified/Downloads/app-gym-main/AZURE_DEPLOYMENT_GUIDE.md:1).

## Asset Workflow

Source files live in `src/` and are compiled into `public/build/` by Gulp.

```bash
npm run gulp
```

If you change SCSS, JavaScript, or source media, regenerate the compiled assets before testing.
