# Fix CSRF Token Mismatch on Login

## The Problem

Laravel Fortify requires:
1. **Cookie**: `XSRF-TOKEN` cookie must be present
2. **Header**: `X-XSRF-TOKEN` header must match the cookie value
3. **Same Session**: Both must come from the same session

## Solution: Manual Two-Step Process

The pre-request script is async and unreliable. Use this manual process:

### Step 1: Get CSRF Token (REQUIRED FIRST)

1. **Run "Authentication → Get CSRF Token"** request
2. **Verify it worked**:
   - Check Postman Console - should see "CSRF token saved"
   - Click "Cookies" link below URL - should see `XSRF-TOKEN` cookie
   - Check environment variable `xsrf_token` has a value

### Step 2: Login (IMMEDIATELY AFTER)

1. **Run "Authentication → Login"** request
2. **Important**: Run Login right after Get CSRF Token (don't wait)
3. The cookie from Step 1 will be automatically sent with Login

## Why This Works

- "Get CSRF Token" visits homepage and gets the cookie
- Postman stores the cookie automatically
- "Login" request sends that cookie + header token
- Laravel verifies they match

## If Still Failing

1. **Check Cookies Are Enabled**:
   - Postman Settings → Cookies tab
   - Make sure Cookies toggle is **enabled** (ON)
   - This is the main requirement - if enabled, Postman will automatically manage cookies

2. **Clear and Retry**:
   - Click "Cookies" link below URL
   - Delete all cookies for `localhost:8000`
   - Run "Get CSRF Token" again
   - Run "Login" immediately

3. **Verify Token Match**:
   - After "Get CSRF Token", check cookie value
   - Check `xsrf_token` environment variable
   - They should match (after URL decoding)

4. **Check Postman Console**:
   - View → Show Postman Console
   - Look for cookie-related messages
   - Verify token was extracted

## Quick Test

1. Run "Get CSRF Token" → Should see cookie in Postman
2. Run "Login" → Should work if cookie exists
3. If Login fails → Cookie wasn't sent or token mismatch

The key is: **Always run "Get CSRF Token" BEFORE "Login"**
