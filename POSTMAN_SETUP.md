# Postman Setup Guide

## Important: Enable Cookies in Postman

Before using the collection, make sure cookies are enabled in Postman:

1. Open Postman Settings (gear icon in top right)
2. Go to **Cookies** tab
3. Make sure **Cookies** are **enabled** (toggle should be ON)
4. Go to **General** tab (optional)
5. Make sure **"Automatically follow redirects"** is enabled (recommended)

## Setup Steps

1. **Import Collection**:
   - File → Import → Select `postman/Shopping Cart API.postman_collection.json`

2. **Import Environment**:
   - File → Import → Select `postman/Local Environment.postman_environment.json`
   - Select "Local Environment" from the environment dropdown (top right)

3. **Configure Environment Variables**:
   - Click the eye icon (👁️) next to environment dropdown
   - Click "Edit" on "Local Environment"
   - Update these values:
     - `user_email`: Your test user email (e.g., "test@example.com")
     - `user_password`: Your test user password
     - `product_id`: A product ID to test with (e.g., "1")

4. **Get CSRF Token First** (IMPORTANT):
   - Run **"Authentication → Get CSRF Token"** request first (visits homepage to get CSRF cookie)
   - This will set the CSRF cookie and token
   - Check Postman Console (View → Show Postman Console) to verify token was saved
   - **Note**: The Login request automatically does this, but manual fetch is more reliable

5. **Login**:
   - Run **"Authentication → Login"** request
   - The Login request has a pre-request script that automatically gets the CSRF token
   - But if it fails, manually run "Get CSRF Token" first

6. **Use Other Endpoints**:
   - All other requests will automatically use the `{{xsrf_token}}` variable

## Troubleshooting CSRF Token Issues

If you get "CSRF token mismatch" error:

1. **Check Cookies**:
   - In Postman, go to the request
   - Click "Cookies" link below the URL
   - Verify you see `XSRF-TOKEN` cookie for `localhost:8000`
   - If not, cookies might be disabled

2. **Manually Get Token**:
   - Run "Get CSRF Token" request first
   - Check Postman Console for confirmation
   - Then try Login again

3. **Clear Cookies**:
   - In Postman, click "Cookies" link below URL
   - Delete all cookies for `localhost:8000`
   - Run "Get CSRF Token" again
   - Then Login

4. **Verify Environment Variable**:
   - Click the eye icon (👁️)
   - Check that `xsrf_token` has a value
   - If empty, the token extraction failed

5. **Check Postman Console**:
   - View → Show Postman Console
   - Look for error messages or token extraction logs

## Alternative: Use Browser First

If Postman continues to have issues:

1. Open your browser and go to `http://localhost:8000/login`
2. Open browser DevTools → Application → Cookies
3. Copy the `XSRF-TOKEN` cookie value
4. In Postman, set `xsrf_token` environment variable to that value
5. Then use Login request

## Notes

- CSRF tokens expire after some time - if requests start failing, get a new token
- The Login request automatically fetches CSRF token before login, but manual fetch is more reliable
- Make sure your Laravel app is running (`composer run dev` or `php artisan serve`)
