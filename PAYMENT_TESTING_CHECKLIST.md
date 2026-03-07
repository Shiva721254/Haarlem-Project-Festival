# Payment Testing Checklist

## Stripe Test Cards (Use these in test mode)

### 1. ✅ **Successful Payment**
```
Card Number: 4242 4242 4242 4242
Expiry: Any future date (e.g., 12/28)
CVC: Any 3 digits (e.g., 123)
ZIP: Any 5 digits (e.g., 12345)
```
**Expected Result**: Payment succeeds, order marked as "completed"

---

### 2. ⚠️ **3D Secure Authentication Required**
```
Card Number: 4000 0025 0000 3155
Expiry: Any future date
CVC: Any 3 digits
ZIP: Any 5 digits
```
**Expected Result**: Stripe shows authentication modal, payment succeeds after authentication

---

### 3. ❌ **Payment Declined (Generic)**
```
Card Number: 4000 0000 0000 0002
Expiry: Any future date
CVC: Any 3 digits
ZIP: Any 5 digits
```
**Expected Result**: Payment fails with "card declined" error

---

### 4. ❌ **Insufficient Funds**
```
Card Number: 4000 0000 0000 9995
Expiry: Any future date
CVC: Any 3 digits
ZIP: Any 5 digits
```
**Expected Result**: Payment fails with "insufficient funds" error

---

### 5. ❌ **Expired Card**
```
Card Number: 4000 0000 0000 0069
Expiry: Any future date
CVC: Any 3 digits
ZIP: Any 5 digits
```
**Expected Result**: Payment fails with "expired card" error

---

### 6. ❌ **Incorrect CVC**
```
Card Number: 4000 0000 0000 0127
Expiry: Any future date
CVC: Any 3 digits
ZIP: Any 5 digits
```
**Expected Result**: Payment fails with "incorrect CVC" error

---

## Testing Steps

1. **Browse Events**: http://localhost:8000/schedule
2. **Add Tickets to Cart**: Select event, choose quantity, add to cart
3. **Review Cart**: http://localhost:8000/cart
4. **Proceed to Checkout**: http://localhost:8000/checkout
5. **Click "Proceed to Payment"**: Redirected to Stripe
6. **Enter Test Card**: Use one of the cards above
7. **Complete Payment**: Submit payment form
8. **Verify Result**: 
   - Success → Redirected to confirmation page
   - Failure → Error message displayed

---

## Verification Checklist

After testing each card:

- [ ] **Database Check**: Order status is correct
  ```sql
  SELECT id, customer_email, total_amount, status, stripe_payment_intent_id 
  FROM orders 
  ORDER BY created_at DESC 
  LIMIT 5;
  ```

- [ ] **Email Sent**: Check if confirmation email was sent (successful payments only)
  - Check MailHog: http://localhost:8025

- [ ] **PDF Invoice**: Email contains PDF attachment with QR code

- [ ] **Ticket Quantities**: Ticket inventory is reduced correctly
  ```sql
  SELECT t.ticket_type, t.quantity_available, t.quantity_sold, e.title
  FROM tickets t
  JOIN events e ON t.event_id = e.id
  WHERE e.id = [EVENT_ID];
  ```

---

## Security Testing

### SQL Injection Test
Try these in login/registration forms:
```
Email: admin' OR '1'='1' --
Email: '; DROP TABLE users; --
```
**Expected**: Form validation rejects, no SQL error

### XSS Test
Try these in event creation/editing:
```
Title: <script>alert('XSS')</script>
Description: <img src=x onerror=alert('XSS')>
```
**Expected**: Content is escaped, no alert shows on page

### CSRF Test
1. Create a form on external site posting to: http://localhost:8000/cart/add
2. **Expected**: Request rejected (missing CSRF token)

---

## Sample Data Verification (100% Rubric)

Run these queries to verify requirements:

```sql
-- ✓ Check: 5+ events
SELECT COUNT(*) as total_events FROM events;

-- ✓ Check: 5+ categories
SELECT COUNT(DISTINCT category) as total_categories FROM events;

-- ✓ Check: Events per category
SELECT category, COUNT(*) as events FROM events GROUP BY category;

-- ✓ Check: 3+ ticket types per event
SELECT e.title, COUNT(t.id) as ticket_types 
FROM events e 
LEFT JOIN tickets t ON e.id = t.event_id 
GROUP BY e.id, e.title;
```

**Required Results**:
- ✅ total_events >= 5
- ✅ total_categories >= 5
- ✅ ticket_types >= 3 for ALL events

---

## Current Status (After Sample Data Load)

✅ **Events**: 12 total (8 new + 4 old with existing orders)
✅ **Categories**: 6 unique (Dance, Food, Kids, Museum, Music, Workshop)
✅ **Ticket Types**: 4 per event (exceeds 3+ requirement)
✅ **Sample Data**: Comprehensive seed file created

**Next Tasks**:
1. Test all Stripe payment scenarios
2. Verify email delivery for each successful payment
3. Test security vulnerabilities (SQL injection, XSS, CSRF)
4. Create technical documentation (ERD, UML diagrams)
