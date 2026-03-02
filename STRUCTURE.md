# Haarlem Festival - MVC Project Structure

## Directory Overview

```
haarlem-project-festival/
├── public/                      # Web root (exposed to users)
│   ├── index.php               # Application entry point
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
│
├── src/                        # Application source code (PSR-4: App\)
│   ├── Controllers/            # HTTP request handlers
│   │   ├── HomeController.php
│   │   ├── ScheduleController.php
│   │   ├── AuthController.php
│   │   ├── ContactController.php
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       └── EventAdminController.php
│   │
│   ├── Models/                 # Data layer (repositories)
│   │   ├── EventRepository.php
│   │   └── UserRepository.php
│   │
│   ├── Services/               # Business logic
│   │   └── EventService.php
│   │
│   ├── Framework/              # Core framework utilities
│   │   ├── Router.php          # URL routing
│   │   ├── Response.php        # HTTP response handling
│   │   ├── Auth.php            # Authentication/authorization
│   │   ├── Csrf.php            # CSRF protection
│   │   ├── Flash.php           # Session flash messages
│   │   ├── Repository.php      # Base repository class
│   │   └── helpers.php         # Global helper functions
│   │
│   └── Config/                 # Configuration classes
│       ├── Database.php        # Database configuration
│       └── EventCategories.php
│
├── resources/                  # Application resources
│   └── views/                  # View templates
│       ├── layout/
│       │   ├── app.php        # Main layout wrapper
│       │   ├── navbar.php
│       │   └── footer.php
│       ├── home/               # Home page views
│       ├── schedule/           # Schedule views
│       ├── auth/               # Authentication views
│       ├── contact/            # Contact page
│       ├── admin/              # Admin panel views
│       │   ├── dashboard.php
│       │   └── events/
│       │       ├── index.php
│       │       └── form.php
│       ├── components/         # Reusable view components
│       │   └── event-card.php
│       └── partials/           # View partials
│           ├── nav.php
│           └── footer.php
│
├── routes/                     # Route definitions
│   ├── web.php                # Public routes
│   └── admin.php              # Admin routes
│
├── database/                   # Database files
│   ├── migrations/            # Database migrations
│   ├── schema.sql             # Schema definition
│   └── seed.sql               # Data seeding
│
├── bootstrap/                 # Application initialization
│   └── app.php               # Bootstrap configuration
│
├── storage/                   # Runtime files
│   ├── logs/                 # Application logs
│   └── uploads/              # User uploads
│
├── docker/                    # Docker configuration
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── Dockerfile
│
├── scripts/                   # Utility scripts
│   └── create_admin.php      # Admin user creation script
│
├── vendor/                    # Composer dependencies
├── .env.example              # Environment variables template
├── .gitignore
├── composer.json
├── docker-compose.yml
└── README.md
```

## MVC Architecture

### Model (Data Layer)
- **Location**: `src/Models/` (stored as Repositories)
- **Responsibility**: Data access and database operations
- **Files**: `EventRepository.php`, `UserRepository.php`
- Handles queries, inserts, updates, and deletes
- Acts as the single point of database access

### View (Presentation Layer)
- **Location**: `resources/views/`
- **Responsibility**: Rendering HTML templates
- **Files**: PHP template files organized by page/section
- Uses helper functions like `h()` for escaping, `view()` for rendering
- Receives data from controllers via variable extraction

### Controller (Application Logic)
- **Location**: `src/Controllers/`
- **Responsibility**: Handling HTTP requests and orchestrating responses
- **Files**: Controller classes for different features
- Receives requests via routing
- Calls repositories for data
- Renders views with processed data
- Returns responses

## Routing

Routes are defined in the `routes/` directory:

- **`routes/web.php`**: Public-facing routes (GET / , /schedule, /login, etc.)
- **`routes/admin.php`**: Protected admin routes (/admin/events, etc.)

Route format:
```php
['GET|POST', '/path', [ControllerClass::class, 'methodName']]
```

## Request Flow

```
HTTP Request
    ↓
public/index.php (entry point)
    ↓
App\Framework\Router (matches route)
    ↓
Controller (processes request)
    ↓
Model/Repository (data access)
    ↓
Service (business logic - optional)
    ↓
View (renders presentation)
    ↓
Response (HTTP response)
```

## Key Features

### Authentication
- Managed by `App\Framework\Auth` class
- Session-based authentication
- Admin role checking for protected routes
- CSRF protection via `App\Framework\Csrf`

### Flash Messages
- `App\Framework\Flash` for temporary session messages
- Success/error messages persist across redirects

### Helper Functions
- `h()` - HTML escape strings
- `view()` - Render view templates
- Located in `src/Framework/helpers.php`

### Configuration
- Database config in `src/Config/Database.php`
- Event categories in `src/Config/EventCategories.php`

## Database

### Schema
- Located in `database/schema.sql`
- Contains table definitions

### Seeding
- Located in `database/seed.sql`
- Initial data population

### Migrations
- Directory for future migrations in `database/migrations/`

## Best Practices

1. **Controllers**: Keep business logic minimal, delegate to services/repositories
2. **Views**: Only HTML/presentation logic, no business logic
3. **Models/Repositories**: All data access goes through repositories
4. **Configuration**: Keep environment-specific config in `.env`
5. **Security**: Always escape output with `h()`, verify CSRF tokens
6. **Organization**: Follow folder structure to keep code organized

## Development

### Adding a New Feature

1. Create a Controller in `src/Controllers/`
2. Add Repository/Model in `src/Models/` if needed
3. Create Views in `resources/views/your-feature/`
4. Add Routes in `routes/web.php` or `routes/admin.php`
5. Add Services in `src/Services/` if complex logic is needed

### Example Feature Structure
```
Feature: Event Management
├── src/Controllers/EventController.php
├── src/Models/EventRepository.php
├── src/Services/EventService.php
├── resources/views/events/
│   ├── index.php
│   ├── show.php
│   └── form.php
└── routes/web.php (10+ routes)
```

## Running the Application

```bash
# Build and run containers
docker-compose up -d

# Access the application
http://localhost:8080

# View logs
docker-compose logs -f

# Stop containers
docker-compose down
```

## Environment Setup

1. Copy `.env.example` to `.env`
2. Update database credentials as needed
3. Run database migrations
4. Access the application

---

**Last Updated**: March 2026  
**Framework**: Custom PHP MVC Framework  
**PHP Version**: 8.0+
