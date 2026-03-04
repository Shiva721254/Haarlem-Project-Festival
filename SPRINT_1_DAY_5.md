# Day 5: User Features & Order Management

## Goals
Build user-facing order history and admin capabilities to complete the order lifecycle.

## Features to Implement

### 1️⃣ **User Order History Page** (Priority: HIGH)
**Endpoint**: `GET /profile/orders`

**What Users See**:
- List of all their completed orders
- Order ID, date, total amount, status
- Link to view full order details
- Pagination (10 orders per page)

**Implementation**:
- Create `UserOrderController` or extend `AuthController`
- Query orders by logged-in user_id
- Create `resources/views/profile/orders.php` view
- Add route: `GET /profile/orders`

**Database**: Use `OrderRepository::findByUserId()`

---

### 2️⃣ **Order Details Page** (Priority: HIGH)
**Endpoint**: `GET /profile/orders/:order_id`

**What Users See**:
- Full order summary (like checkout review)
- All line items with prices
- Order status badge (completed/pending/failed)
- Date and total
- Download/print button for tickets

**Implementation**:
- Create view: `resources/views/profile/order-detail.php`
- Add route: `GET /profile/orders/:order_id`
- Verify user owns order (user_id check)
- Use `OrderRepository::findById()`

---

### 3️⃣ **Email Confirmation** (Priority: MEDIUM)
**Trigger**: After payment success

**Email Content**:
- Order confirmation subject: "Your Haarlem Festival Order #123"
- Order details (items, total, date)
- Ticket QR codes (or download link)
- Event details (date, time, location)

**Implementation**:
- Create `EmailService` class
- Use PHP `mail()` or Mailer library
- Modify `PaymentController::success()` to send email
- Create `resources/emails/order-confirmation.html` template

**Email Template Should Include**:
- Header with festival logo
- Order number and date
- List of tickets with event info
- QR code or ticket ID
- Footer with support info

---

### 4️⃣ **Admin Order Dashboard** (Priority: MEDIUM)
**Endpoint**: `GET /admin/orders`

**What Admins See**:
- All orders (paginated, 50 per page)
- Filters: status, date range, customer email
- Total revenue statistics
- Export to CSV option

**Implementation**:
- Create `AdminOrderController`
- View: `resources/views/admin/orders/index.php`
- Route: `GET /admin/orders`
- Require admin role (use `@guardAdminRoutes`)

---

### 5️⃣ **Admin Order Management** (Priority: LOW)
**Endpoint**: `GET /admin/orders/:order_id`

**Admin Capabilities**:
- View order details
- Change order status (completed → cancelled)
- Refund payment (if Stripe intent exists)
- Send reminder email to customer
- View customer details

**Implementation**:
- View: `resources/views/admin/orders/detail.php`
- Controller: Add methods `updateStatus()`, `refund()`
- Routes: 
  - `GET /admin/orders/:order_id`
  - `PATCH /admin/orders/:order_id/status`
  - `POST /admin/orders/:order_id/refund`

---

## Database Queries Needed
All queries already in `OrderRepository`:
- ✅ `findById()` - Get order with items
- ✅ `findByUserId()` - Get user's orders
- ✅ Add: `findAll()` - Get all orders (admin)
- ✅ Add: `findByStatus()` - Filter by status

---

## File Structure

```
New Files to Create:
├── src/Controllers/UserOrderController.php
├── src/Services/EmailService.php
├── src/Controllers/Admin/AdminOrderController.php
├── resources/views/profile/orders.php
├── resources/views/profile/order-detail.php
├── resources/views/admin/orders/
│   ├── index.php
│   └── detail.php
└── resources/emails/
    └── order-confirmation.html
```

---

## Day 5 Roadmap

**Session 1** (2-3 hours):
- [ ] Create `UserOrderController`
- [ ] Add `findAll()` to `OrderRepository`
- [ ] Build order history page
- [ ] Build order detail page
- [ ] Test user can view their orders

**Session 2** (1-2 hours):
- [ ] Create admin orders page (list all)
- [ ] Add admin order detail page
- [ ] Add status update functionality
- [ ] Test admin dashboard

**Session 3** (1-2 hours):
- [ ] Create `EmailService`
- [ ] Build email template
- [ ] Integrate into `PaymentController::success()`
- [ ] Test email sending
- [ ] Commit and push to GitHub

---

## Success Criteria ✅

- [ ] Users can view their order history
- [ ] Users can see order details
- [ ] Admins can view all orders
- [ ] Admins can manage order status
- [ ] Order confirmation emails send
- [ ] All views styled consistently
- [ ] No errors in logs
- [ ] Code committed and pushed

---

**Start Time**: Now
**Branch**: `feature/sprint-1-day-5-user-features`
**Status**: Ready to begin 🚀
