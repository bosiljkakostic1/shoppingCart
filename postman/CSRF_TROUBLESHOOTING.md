# CSRF Token Troubleshooting Guide

## Problem: CSRF Token Mismatch on Login

If you're getting "CSRF token mismatch" error even after getting the token, follow these steps:

## Step-by-Step Fix:

### 1. Ensure Cookies Are Enabled in Postman

**Critical**: Cookies must be enabled in Postman.

1. Open Postman Settings (gear icon)
2. Go to **Cookies** tab
3. Make sure **Cookies** toggle is **enabled** (ON)
4. Go to **General** tab (optional)
5. Enable **"Automatically follow redirects"** (recommended)

### 2. Clear All Cookies

1. In Postman, click **"Cookies"** link below any request URL
2. Delete all cookies for `localhost:8000`
3. Close the cookies window

### 3. Get CSRF Token First

1. Run **"Authentication → Get CSRF Token"** request
2. Check Postman Console (View → Show Postman Console)
3. You should see: "CSRF token saved: [token]..."
4. Verify cookie exists:
   - Click "Cookies" link below the URL
   - You should see `XSRF-TOKEN` cookie for `localhost:8000`

### 4. Verify Environment Variable

1. Click the eye icon (👁️) next to environment dropdown
2. Check that `xsrf_token` has a value
3. If empty, the token extraction failed

### 5. Run Login Request

1. **Important**: Make sure you run "Get CSRF Token" FIRST
2. Then run "Authentication → Login"
3. The Login request should automatically include:
   - `X-XSRF-TOKEN` header with the token value
   - `XSRF-TOKEN` cookie (automatically sent by Postman)

## Common Issues:

### Issue 1: Token Not Being Sent

**Symptom**: CSRF token mismatch error

**Fix**:
- Make sure cookies are enabled in Postman Settings → Cookies tab (toggle ON)
- Run "Get CSRF Token" before Login
- Check that the cookie exists: Click "Cookies" link below URL, verify `XSRF-TOKEN` cookie exists

### Issue 2: Token Mismatch

**Symptom**: Token exists but doesn't match

**Fix**:
- Clear all cookies
- Run "Get CSRF Token" again
- Immediately run Login (don't wait too long)
- Tokens can expire or change

### Issue 3: Cookie Not Persisting

**Symptom**: Token works once, then fails

**Fix**:
- Verify cookies are enabled in Postman Settings → Cookies tab
- Make sure you're using the same domain (`localhost:8000`)
- Don't clear cookies between requests
- Check that cookies are being sent: Click "Cookies" link below URL to verify

## Manual Verification:

1. **Check Cookie Value**:
   - Click "Cookies" below URL
   - Copy the `XSRF-TOKEN` cookie value
   - Compare with `xsrf_token` environment variable
   - They should match (after URL decoding)

2. **Check Request Headers**:
   - In Login request, go to Headers tab
   - Verify `X-XSRF-TOKEN` header exists
   - Value should match the cookie (URL decoded)

3. **Check Postman Console**:
   - View → Show Postman Console
   - Look for cookie-related messages
   - Check for any errors

## Alternative: Use Browser First

If Postman continues to have issues:

1. Open browser → `http://localhost:8000/login`
2. Open DevTools → Application → Cookies
3. Copy `XSRF-TOKEN` cookie value
4. In Postman, set `xsrf_token` environment variable to that value
5. Make sure Postman has the cookie too (click Cookies link, add manually if needed)
6. Then run Login

## Why This Happens:

Laravel Fortify requires:
1. **Cookie**: `XSRF-TOKEN` cookie must be present (set by visiting any page)
2. **Header**: `X-XSRF-TOKEN` header must match the cookie value
3. **Session**: Both must come from the same session

Postman needs to:
- Store the cookie from "Get CSRF Token" request
- Send that cookie with "Login" request
- Include the matching token in the header

If any of these fail, you get "CSRF token mismatch".
