# How to Re-import Updated Postman Collection

If you're still getting errors about `/sanctum/csrf-cookie`, you need to re-import the updated collection.

## Steps:

1. **Delete the old collection in Postman**:
   - In Postman, find "Shopping Cart API" collection
   - Right-click → Delete
   - Confirm deletion

2. **Import the updated collection**:
   - File → Import
   - Click "Upload Files"
   - Select `postman/Shopping Cart API.postman_collection.json`
   - Click "Import"

3. **Verify the "Get CSRF Token" request**:
   - Open "Authentication" folder
   - Click "Get CSRF Token" request
   - Check the URL - it should be `{{base_url}}/` (homepage)
   - It should NOT be `{{base_url}}/sanctum/csrf-cookie`

4. **Test**:
   - Run "Get CSRF Token" - should work without errors
   - Then run "Login"

## Quick Check:

The "Get CSRF Token" request URL should be:
```
GET {{base_url}}/
```

NOT:
```
GET {{base_url}}/sanctum/csrf-cookie  ❌
```

If you still see `/sanctum/csrf-cookie` in the URL, the collection wasn't updated. Delete and re-import.
