# Day 2 Testing Guide

## Quick Check
Visit: **http://localhost:8000/debug_day2.php**

This debug page shows all Day 2 features and their status.

---

## Feature Checklist

### ✅ 1. Session Security Configuration
**What to check:**
- Session uses HTTPOnly cookies (prevents XSS)
- Session uses secure cookies (when on HTTPS)
- Session uses SameSite=Lax (prevents CSRF)
- Session timeout set to 3600 seconds (1 hour)

**How to verify:**
```bash
# Visit debug page
http://localhost:8000/debug_day2.php

# Or check in browser DevTools:
# 1. Open DevTools (F12)
# 2. Go to Application tab
# 3. Check Cookies → localhost:8000
# 4. Look for PHPSESSID cookie
# 5. Verify: HttpOnly = ✓, SameSite = Lax
```

---

### ✅ 2. Session Timeout (1 hour)
**What to check:**
- Session expires after 1 hour of inactivity
- User gets redirected to login with message

**How to verify:**
```bash
# Option 1: Manual wait (takes 1 hour)
1. Login at /login
2. Wait 61 minutes
3. Visit /admin/events
4. Should redirect to /login with "Session expired" message

# Option 2: Quick test (modify timeout temporarily)
# Edit src/Framework/SessionManager.php
# Change: private const SESSION_TIMEOUT = 3600;
# To:     private const SESSION_TIMEOUT = 60; // 1 minute
# Then test in 1 minute
```

**Check remaining time:**
- Visit `/profile` → Shows countdown timer
- Or check `/debug_day2.php` → Shows remaining time

---

### ✅ 3. Login Rate Limiting
**What to check:**
- Max 5 failed login attempts
- 15-minute lockout after 5 failures
- Success clears the counter

**How to verify:**
```bash
# Test lockout:
1. Go to /login
2. Enter: admin@haarlemfestival.nl
3. Enter wrong password 5 times
4. On attempt 6, should see:
   "Too many failed login attempts. Please try again in 15 minute(s)."

# Test counter reset:
1. Wait 15 minutes OR clear session
2. Login with CORRECT password
3. Rate limit counter should be cleared

# Check via debug:
http://localhost:8000/debug_ratelimit.php
```

---

### ✅ 4. Failed Login Tracking & Security Logging
**What to check:**
- Failed logins logged to `storage/logs/security.log`
- Logs include: timestamp, event type, masked email, IP

**How to verify:**
```bash
# Trigger some events:
1. Try logging in with wrong password
2. Try registering with invalid data
3. Logout

# Check the log:
cat storage/logs/security.log

# Or view in browser:
http://localhost:8000/debug_day2.php
# (scroll to Security Logging section)
```

**Expected log entries:**
```
2026-03-04 12:34:56 [WARNING] auth.login.failed {"email":"a***@haarlemfestival.nl","ip":"172.20.0.1"}
2026-03-04 12:35:10 [INFO] auth.login.success {"email":"a***@haarlemfestival.nl","ip":"172.20.0.1","role":"admin"}
2026-03-04 12:40:30 [INFO] auth.logout.success {"email":"a***@haarlemfestival.nl","ip":"172.20.0.1"}
2026-03-04 12:41:00 [WARNING] auth.login.rate_limited {"email":"t***@example.com","ip":"172.20.0.1","remaining_seconds":850}
```

---

### ✅ 5. User Profile Page
**What to check:**
- Profile page accessible at `/profile`
- Shows user email, role
- Shows session remaining time
- Protected (requires login)

**How to verify:**
```bash
# Test without login:
1. Logout (or open incognito)
2. Visit http://localhost:8000/profile
3. Should redirect to /login

# Test with login:
1. Login as admin
2. Visit http://localhost:8000/profile
3. Should see:
   - Email: admin@haarlemfestival.nl
   - Role: admin
   - Session Remaining: 00:59:45 (countdown timer)
   - Logout button
```

---

### ✅ 6. Better Error Messages
**What to check:**
- Validation errors display clearly
- Flash messages styled nicely
- CSRF errors handled gracefully

**How to verify:**
```bash
# Test registration validation:
1. Go to /register
2. Enter:
   - Email: invalid-email
   - Password: 123
   - First Name: A
3. Submit
4. Should see list of errors:
   ✗ Invalid email format
   ✗ Password must be at least 8 characters
   ✗ Password must contain uppercase letter
   (etc.)

# Test CSRF:
1. Open /login in browser
2. Open DevTools Console
3. Run: document.querySelector('input[name="_csrf"]').value = 'fake'
4. Submit form
5. Should see: "Invalid CSRF token"
```

---

### ✅ 7. Flash Message Improvements
**What to check:**
- Flash messages persist through redirects
- Flash messages auto-clear after display
- Flash works after logout (session destroy)

**How to verify:**
```bash
# Test flash after logout:
1. Login
2. Click Logout
3. Should see green "Logged out." message on homepage
4. Refresh page
5. Message should disappear

# Test flash persistence:
1. Try logging in with wrong password
2. Should see red error message
3. Refresh /login
4. Message should disappear
```

---

## All-in-One Test Script

Run this complete test flow:

```bash
# 1. Start fresh
docker compose restart php nginx

# 2. Check debug page
curl http://localhost:8000/debug_day2.php

# 3. Test registration validation
curl -X POST http://localhost:8000/register \
  -d "email=bad" \
  -d "password=weak" \
  -d "first_name=A"
# Should fail validation

# 4. Test rate limiting
for i in {1..6}; do
  curl -X POST http://localhost:8000/login \
    -d "email=test@test.com" \
    -d "password=wrong"
done
# Attempt 6 should be rate-limited

# 5. Check security log
cat storage/logs/security.log | tail -10
```

---

## Expected Files Created/Modified

### Created:
- ✅ `src/Framework/SessionManager.php`
- ✅ `src/Framework/RateLimiter.php`
- ✅ `src/Framework/SecurityLogger.php`
- ✅ `resources/views/auth/profile.php`
- ✅ `storage/logs/security.log` (auto-created on first event)

### Modified:
- ✅ `src/Controllers/AuthController.php` (added logging, rate limiting)
- ✅ `routes/web.php` (added /profile route)
- ✅ `public/index.php` (calls SessionManager::start())

---

## Troubleshooting

### Session not timing out?
- Check `ini_get('session.gc_maxlifetime')` in debug page
- Make sure SessionManager::start() is called in index.php

### Rate limiting not working?
- Clear session: delete `storage/sessions/*`
- Verify RateLimiter class is loaded

### No security.log file?
- Trigger an event (failed login)
- Check `storage/logs/` directory exists
- Check write permissions on storage/

### Profile page redirects to login?
- Make sure you're logged in first
- Check Auth::isLoggedIn() returns true

---

## Success Criteria

Day 2 is complete when:
- ✅ All 6 sections in debug page show green checkmarks
- ✅ Rate limiting blocks after 5 failed attempts
- ✅ Security log captures events
- ✅ Profile page displays correctly
- ✅ Session timeout works (test with reduced timeout)
- ✅ Flash messages work after logout

---

## Next Steps (Day 3)

After Day 2 is verified:
- Tickets system (attach tickets to events)
- Shopping cart (add/remove tickets)
- Checkout preparation
