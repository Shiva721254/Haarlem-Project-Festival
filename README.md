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
```

---

## Email Confirmation & PDF Invoice

The checkout flow sends a confirmation email with a branded PDF invoice attachment.

### What is implemented
- Gmail SMTP sending via PHPMailer
- PDF invoice generation via Dompdf
- Branded invoice with Haarlem Festival logo
- Order QR code embedded in the invoice
- Totals section aligned for print-friendly layout

### Required environment variables
```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_16_char_app_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=your_gmail@gmail.com
MAIL_FROM_NAME=Haarlem Festival
```

### Local test command
```powershell
docker compose exec php php -r "require '/app/bootstrap/app.php'; var_export(\App\Services\EmailService::sendOrderConfirmation(8));"
```

---

## Status

- Stripe checkout flow: implemented
- Email confirmation + PDF invoice: implemented
- QR-based invoice enhancement: implemented

