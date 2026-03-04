# Sprint 1 - Day 6: Ticket Verification & QR Scanning

## Objectives
Implement invoice generation, ticket verification system, and QR code scanning for event entrance management.

## Features to Implement

### 1. Invoice Generation ✅ (IN PROGRESS)
- [ ] Add download invoice button to order detail page
- [ ] Create PDF/printable invoice export
- [ ] Add print-friendly styling
- [ ] Download invoice as PDF option

### 2. Ticket Verification System
- [ ] Create TicketVerificationController
- [ ] QR code scanner interface
- [ ] Verify ticket validity
- [ ] Check ticket expiration
- [ ] Mark tickets as used/scanned

### 3. Ticket Status Tracking
- [ ] Add ticket_status column (unused/used/verified)
- [ ] Create TicketRepository with status methods
- [ ] Admin interface to check-in tickets
- [ ] Display ticket status on order page

### 4. Admin Check-in Dashboard
- [ ] Create check-in interface
- [ ] Live QR scanning
- [ ] Real-time ticket validation
- [ ] Statistics: Total/Checked-in/Remaining

### 5. Security & Validation
- [ ] Prevent duplicate ticket scans
- [ ] Validate order ownership
- [ ] Admin-only access to check-in
- [ ] Audit log for scans

## Technical Details

### Database Changes
```sql
ALTER TABLE orders ADD COLUMN ticket_status ENUM('pending', 'partially_used', 'fully_used') DEFAULT 'pending';
ALTER TABLE order_items ADD COLUMN is_used BOOLEAN DEFAULT FALSE;
ALTER TABLE order_items ADD COLUMN used_at TIMESTAMP NULL;
ALTER TABLE order_items ADD COLUMN checked_by_user_id INT NULL;
```

### Routes to Add
- POST `/ticket/verify` - Verify QR code
- GET `/admin/checkin` - Check-in interface
- POST `/admin/checkin/scan` - Process QR scan
- GET `/profile/orders/invoice/:id` - Download invoice
- POST `/admin/checkin/verify` - Manual verification

### Files to Create
- `src/Controllers/TicketVerificationController.php`
- `src/Controllers/Admin/CheckinController.php`
- `src/Repositories/TicketRepository.php`
- `resources/views/checkin/index.php`
- `resources/views/admin/checkin.php`

## Progress

### Completed
- [ ] None yet

### In Progress
- [x] Feature branch created: `feature/ticket-verification-qr`

### Todo
- [ ] Invoice download/print
- [ ] Database migrations
- [ ] Controllers implementation
- [ ] Views implementation
- [ ] Testing
