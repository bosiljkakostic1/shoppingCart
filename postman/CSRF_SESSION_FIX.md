# CSRF Token Mismatch - Session Cookie Issue

## The Problem

You're getting "CSRF token mismatch" even though the token is saved correctly. This happens because **Laravel Fortify requires BOTH cookies from the same session**:

1. `XSRF-TOKEN` cookie (the CSRF token)
2. `laravel_session` cookie (the session cookie)

If these cookies come from different sessions, Laravel will reject the request.

## The Solution: Two-Step Process

### Step 1: Get CSRF Token (MUST RUN FIRST)

1. **Run "Authentication → Get CSRF Token"** request
2. **Verify both cookies are set**:
   - Click "Cookies" link below the URL
   - You should see **TWO cookies**:
     - `XSRF-TOKEN` (the CSRF token)
     - `laravel_session` (the session cookie)
   - If you only see `XSRF-TOKEN`, the session cookie wasn't set

### Step 2: Login (IMMEDIATELY AFTER)

1. **Run "Authentication → Login"** request
2. **Important**: Run Login right after Get CSRF Token (within a few seconds)
3. Both cookies from Step 1 will be automatically sent with Login

## Why This Happens

Laravel Fortify validates CSRF tokens by:
1. Reading the `XSRF-TOKEN` cookie
2. Reading the `laravel_session` cookie
3. Matching the CSRF token to the session
4. If they don't match (different sessions), you get "CSRF token mismatch"

## Troubleshooting

### Issue: Only one cookie is present

**Symptom**: After "Get CSRF Token", you only see `XSRF-TOKEN` cookie, not `laravel_session`

**Fix**:
1. Clear ALL cookies for `localhost:8000` in Postman
2. Make sure your Laravel app is running (`php artisan serve`)
3. Run "Get CSRF Token" again
4. Check cookies - both should be present

### Issue: Cookies are from different sessions

**Symptom**: Both cookies exist but login still fails

**Fix**:
1. Clear ALL cookies for `localhost:8000`
2. Run "Get CSRF Token" (this creates a new session with both cookies)
3. **Immediately** run "Login" (don't wait, don't run other requests)
4. This ensures both cookies are from the same session

### Issue: Token is correct but still fails

**Check**:
1. Open Postman Console (View → Show Postman Console)
2. Run "Get CSRF Token"
3. Check the response - should be 200 OK
4. Check cookies - both `XSRF-TOKEN` and `laravel_session` should be present
5. Run "Login" immediately
6. Check console for any cookie-related errors

## Manual Verification

1. **After "Get CSRF Token"**:
   - Click "Cookies" link below URL
   - Verify you see:
     - `XSRF-TOKEN` cookie
     - `laravel_session` cookie
   - Both should have the same domain (`localhost`) and path (`/`)

2. **Check Environment Variable**:
   - Click eye icon (👁️) next to environment dropdown
   - `xsrf_token` should have a value
   - This value should match the `XSRF-TOKEN` cookie value (after URL decoding)

3. **Before "Login"**:
   - Verify both cookies still exist
   - If cookies disappeared, run "Get CSRF Token" again

## Quick Test

1. Clear all cookies for `localhost:8000`
2. Run "Get CSRF Token" → Should see 2 cookies
3. Run "Login" immediately → Should work

If this doesn't work, the issue is likely:
- Cookies not being sent (check Postman Settings → Cookies is enabled)
- Session cookie not being set by Laravel (check Laravel logs)
- Token format mismatch (check URL encoding)

## Alternative: Use Browser

If Postman continues to have issues:

1. Open browser → `http://localhost:8000/login`
2. Open DevTools → Application → Cookies → `http://localhost:8000`
3. Copy BOTH cookie values:
   - `XSRF-TOKEN`
   - `laravel_session`
4. In Postman:
   - Set `xsrf_token` environment variable to `XSRF-TOKEN` value
   - Click "Cookies" link, manually add both cookies
5. Run Login

This ensures both cookies are from the same browser session.
