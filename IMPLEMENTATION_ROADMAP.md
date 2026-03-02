# Haarlem Festival - Implementation Roadmap (100% Grade Target)

## 🎯 Grading Breakdown (100%)
- **Application (80%)**: Functionality, Security, Completeness
- **Technical Documentation (20%)**: ERD, UML Diagrams, Extra diagrams

---

## PHASE 1: Core Application Foundation (40% of 80% = 32%)

### Sprint 1: Database & User Management
**Duration**: Days 1-3

#### Features:
- ✓ User registration (already has auth)
- ✓ Admin authentication & authorization
- ✓ User roles (Visitor, Employee, Administrator)
- ✓ Session management

#### Database Tables Needed:
```sql
users (id, email, password_hash, role, created_at)
events (id, title, event_date, category, description, image_url, created_at)
tickets (id, event_id, price, quantity_available, quantity_sold)
orders (id, user_id, total_amount, status, created_at)
order_items (id, order_id, ticket_id, quantity, price)
```

#### Security Checklist:
- ✅ Password hashing (bcrypt)
- ✅ CSRF protection (already has)
- ✅ Prepared statements/PDO (implement)
- ✅ Input validation & sanitization
- ✅ Authorization checks on admin routes

---

### Sprint 2: Event Management System (CMS)
**Duration**: Days 4-7

#### Features:
1. **Admin Event Management**
   - Create events with title, date, category, description
   - Edit events
   - Delete events
   - Image uploading for events

2. **Image Upload System**
   ```
   storage/uploads/
   ├── events/
   │   └── {event_id}/
   │       └── {filename}
   └── products/
   ```

3. **View All Events**
   - Homepage with featured events
   - Schedule page with filtering by category
   - Event details page

#### Controllers Needed:
```
src/Controllers/
├── EventController.php (public views)
│   ├── index() - all events
│   ├── show($id) - event details
│
└── Admin/
    └── EventAdminController.php (CRUD)
        ├── index()
        ├── create()
        ├── store()
        ├── edit()
        ├── update()
        ├── delete()
```

#### Models Needed:
```
src/Models/
├── EventRepository.php
├── ImageService.php
└── FileUploadValidator.php
```

---

### Sprint 3: Shopping Cart & Tickets
**Duration**: Days 8-10

#### Features:
1. **Ticket System**
   - Associate tickets with events
   - Set price per ticket
   - Track quantity available

2. **Shopping Cart**
   - Add/remove tickets to cart
   - Cart persistence (session-based for MVP)
   - Display cart summary

3. **Checkout Page**
   - Review order before payment
   - Confirm customer info
   - Ready for payment integration

#### Routes:
```php
// Cart routes
['GET', '/cart', [CartController::class, 'show']]
['POST', '/cart/add', [CartController::class, 'add']]
['POST', '/cart/remove', [CartController::class, 'remove']]

// Checkout
['GET', '/checkout', [OrderController::class, 'checkout']]
['POST', '/checkout/confirm', [OrderController::class, 'confirm']]
```

---

## PHASE 2: Payment Processing (25% of 80% = 20%)

### Sprint 4: Payment Integration (YOUR FOCUS)
**Duration**: Days 11-14

#### Payment Processor Setup:
**Recommended**: Stripe (development mode available)

#### Implementation:
1. **Create PaymentService**
   ```php
   src/Services/PaymentService.php
   
   Methods:
   - createPaymentIntent($amount, $currency)
   - verifyPayment($paymentId)
   - handleWebhook($payload)
   ```

2. **Database for Payments**
   ```sql
   payments (
       id, order_id, amount, currency, 
       provider, provider_transaction_id,
       status, created_at, updated_at
   )
   ```

3. **Payment Routes**
   ```php
   ['POST', '/payment/create-intent', [PaymentController::class, 'createIntent']]
   ['POST', '/payment/confirm', [PaymentController::class, 'confirm']]
   ['POST', '/payment/webhook', [PaymentController::class, 'webhook']]
   ```

#### Security Requirements:
- ✅ PCI DSS compliance (never store full CC)
- ✅ HTTPS only for payment pages
- ✅ Server-side validation of payment
- ✅ Secure webhook handling
- ✅ Order status management

---

## PHASE 3: Advanced Features (15% of 80% = 12%)

### Sprint 5: WYSIWYG Content Editing
**Duration**: Days 15-16

#### CMS Page Management:
- Create editable pages (About, FAQ, T&C)
- WYSIWYG editor for content (TinyMCE or similar)
- Dynamic menu management

#### Database:
```sql
pages (id, slug, title, content, is_published, created_at)
menu_items (id, label, url, position, parent_id)
```

---

## PHASE 4: Documentation & Polish (20%)

### Sprint 6: Technical Documentation
**Duration**: Days 17-18

#### Required Deliverables:

1. **Entity Relationship Diagram (ERD)**
   - All tables and relationships
   - Primary/foreign keys
   - 3NF normalization

2. **UML Class Diagrams**
   - Core classes: Controller, Repository, Service
   - Relationships between classes
   - Show methods and attributes

3. **Extra (bonus points)**
   - Sequence diagram: Payment processing flow
   - Activity diagram: Order checkout process
   - State diagram: Order lifecycle

---

## Security Checklist (Critical for 80%)

### Authentication & Authorization
- [ ] Password hashing with bcrypt
- [ ] Session timeout (30 min)
- [ ] CSRF tokens on all POST forms
- [ ] Rate limiting on login attempts
- [ ] Admin-only routes protected

### Data Protection
- [ ] All user input validated server-side
- [ ] Prepared statements for all DB queries (No SQL injection)
- [ ] Input sanitization (prevent XSS)
- [ ] HTML escaping in templates

### Payment Security
- [ ] No card data stored locally
- [ ] SSL/TLS for payment pages
- [ ] Secure webhook verification
- [ ] Order validation before payment

### File Upload Security
- [ ] File type validation (whitelist)
- [ ] File size limits
- [ ] Virus scanning
- [ ] Store outside web root
- [ ] Rename uploaded files

---

## Testing Checklist

Before submission, test:
- [ ] All CRUD operations work
- [ ] Authentication flows correctly
- [ ] Admin authorization enforced
- [ ] Shopping cart functions
- [ ] Payment processing (test mode)
- [ ] Image uploads secure
- [ ] Form validation works
- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities
- [ ] Responsive design

---

## Current Status

✅ **Completed**:
- MVC folder structure
- Database setup
- Basic auth system
- Event management (partial)
- View organization

⏳ **Next**: Implement Shopping Cart & Tickets (Sprint 3)

---

## Time Estimate
- **Core App (60%)**: 12 days
- **Payment Integration (20%)**: 4 days
- **Polishing (10%)**: 2 days
- **Documentation (20%)**: 2 days
- **Total**: ~20 days (4 weeks) for 100%

