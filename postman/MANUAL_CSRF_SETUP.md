# Manual CSRF Token Setup (If Automatic Fails)

If the automatic CSRF token extraction isn't working, use this manual method:

## Method 1: Use Browser to Get Token

1. **Open Browser**:
   - Go to `http://localhost:8000/login` in your browser
   - Open DevTools (F12)
   - Go to **Application** tab → **Cookies** → `http://localhost:8000`

2. **Copy CSRF Token**:
   - Find `XSRF-TOKEN` cookie
   - Copy its **Value** (it will be URL-encoded)

3. **Set in Postman**:
   - Click eye icon (👁️) next to environment dropdown
   - Edit "Local Environment"
   - Set `xsrf_token` to the cookie value (URL-decoded)
   - Save

4. **Add Cookie Manually in Postman**:
   - In Login request, click "Cookies" link below URL
   - Click "Add Cookie"
   - Set:
     - Name: `XSRF-TOKEN`
     - Value: (paste the cookie value from browser)
     - Domain: `localhost`
     - Path: `/`
   - Save

5. **Run Login**:
   - Now run "Authentication → Login"
   - Should work!

## Method 2: Check Test Results Tab

Instead of Console, check the **Test Results** tab:

1. Run "Get CSRF Token" request
2. Look at the **Test Results** tab (below the response)
3. You should see test results showing if token was extracted
4. Check if tests passed or failed

## Method 3: Verify Cookie Manually

1. Run "Get CSRF Token" request
2. Click **"Cookies"** link below the URL
3. Check if `XSRF-TOKEN` cookie exists for `localhost:8000`
4. If it exists:
   - Copy the cookie value
   - Manually set `xsrf_token` environment variable to that value (URL-decoded)
5. Then run Login

## Debugging Steps

1. **Check if cookie is being set**:
   - Run "Get CSRF Token"
   - Click "Cookies" link
   - If no cookie → Laravel isn't setting it (check app is running)

2. **Check environment variable**:
   - Eye icon → Check `xsrf_token` value
   - If empty → Script didn't run or cookie wasn't found

3. **Check request headers**:
   - In Login request → Headers tab
   - Verify `X-XSRF-TOKEN` header exists
   - Value should match cookie (URL-decoded)

4. **Check response**:
   - If CSRF error → Token mismatch
   - If 401/403 → Not authenticated
   - If 404 → Route not found

## Quick Test

Run this in Postman Console (View → Show Postman Console):

```javascript
// After running "Get CSRF Token", check:
const cookies = pm.cookies.toObject();
console.log('Cookies:', cookies);
console.log('XSRF-TOKEN:', cookies['XSRF-TOKEN']);
console.log('xsrf_token env:', pm.environment.get('xsrf_token'));
```

This will show you what Postman sees.
