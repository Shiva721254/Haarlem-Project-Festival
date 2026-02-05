# Haarlem Festival – Web Development Project

## Project Overview
This project is a web application developed for the **Haarlem Festival**, created as part of the Web Development course.  
The goal is to deliver a **data-driven, accessible, and maintainable festival website** with a **Content Management System (CMS)** that allows administrators to manage festival content dynamically.

The application follows a **custom MVC architecture**, without the use of external frameworks, in line with course guidelines.

---

## Key Features
### Public Website
- Home page with featured events and festival information
- Event listings and event detail pages
- Artist and venue information
- Program overview and scheduling
- Responsive and accessible design

### CMS (Admin Panel)
- Secure admin authentication
- Create, edit, and delete events
- Upload and manage event images
- Edit homepage content blocks
- Manage festival-related data (events, venues, artists)

---

## Technology Stack
- **PHP** (custom MVC, no frameworks)
- **MySQL / MariaDB**
- **HTML5 / CSS3**
- **JavaScript** (minimal, progressive enhancement only)
- **PDO** for database access
- **Docker** (PHP + Nginx setup)

---

## Project Structure
The project uses a clear MVC-based structure with separation of concerns:

```text
app/
├─ Config/            # Application configuration
├─ Controllers/       # Controllers (public + admin)
├─ Core/              # Core framework classes (Router, DB, Auth, etc.)
├─ Middleware/        # Auth and admin middleware
├─ Repositories/      # Database access (PDO)
├─ Services/          # Reusable business logic (uploads, tickets)
├─ Views/             # Views (public and admin)
public/
├─ index.php          # Application entry point
├─ assets/            # CSS, JS, images
├─ uploads/           # Uploaded event images
routes/
├─ web.php            # Public routes
├─ admin.php          # Admin/CMS routes
database/
├─ schema.sql         # Database schema
├─ seed.sql           # Sample data
storage/
├─ logs/
├─ cache/





