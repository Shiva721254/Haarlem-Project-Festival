# Sprint 1 - Day 6: Ticket Verification & QR Scanning

## Objectives
Implement invoice generation, ticket verification system, and QR code scanning for event entrance management.

## Features to Implement

### 1. Invoice Generation ✅ (IN PROGRESS)
- [x] Add download invoice button to order detail page
- [x] Create printable invoice export (HTML + print)
- [x] Add print-friendly styling
- [ ] Download invoice as true PDF option

### 2. Ticket Verification System
- [x] Create TicketVerificationController
- [x] QR code scanner interface
- [x] Verify ticket validity
- [x] Check ticket expiration
- [x] Mark tickets as used/scanned

### 3. Ticket Status Tracking
- [ ] Add ticket_status column (unused/used/verified)
- [x] Create TicketRepository with status methods
- [x] Admin interface to check-in tickets
- [x] Display ticket status on order page

### 4. Admin Check-in Dashboard
- [x] Create check-in interface
- [x] Live QR scanning
- [x] Real-time ticket validation
- [x] Statistics: Total/Checked-in/Remaining

### 5. Security & Validation
- [x] Prevent duplicate ticket scans
- [x] Validate order ownership
- [x] Admin-only access to check-in
- [x] Audit log for scans

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
- [x] Core Day 6 ticket verification, check-in, and status tracking

### In Progress
- [x] Feature branch created: `feature/ticket-verification-qr`
- [x] Schema and dashboard alignment

### Todo
- [ ] True PDF generation (library integration)
- [ ] End-to-end testing and validation checklist
