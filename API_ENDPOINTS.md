# API Endpoints Documentation

This document lists all available API endpoints for the Shopping Cart application.

## Base URL

**Local Development**: `http://localhost:8000`

## Authentication

All API endpoints (except authentication endpoints) require authentication. The application uses Laravel Fortify for authentication with session-based authentication and CSRF protection.

### CSRF Token

All authenticated requests require a CSRF token in the header:
```
X-XSRF-TOKEN: <token>
```

The CSRF token is automatically set in cookies after login. Extract it from the `XSRF-TOKEN` cookie.

## Endpoints

### Authentication Endpoints

These endpoints are provided by Laravel Fortify and don't require authentication.

#### Register
- **URL**: `POST /register`
- **Description**: Register a new user account
- **Headers**:
  - `Content-Type: application/json`
  - `Accept: application/json`
- **Body**:
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```
- **Response**: `204 No Content` (on success)

#### Login
- **URL**: `POST /login`
- **Description**: Authenticate user and create session
- **Headers**:
  - `Content-Type: application/json`
  - `Accept: application/json`
- **Body**:
```json
{
    "email": "user@example.com",
    "password": "password"
}
```
- **Response**: `204 No Content` (on success)
- **Note**: CSRF token is set in cookies when visiting any page (like homepage `/`). Visit homepage first or use the "Get CSRF Token" request in Postman before login.

#### Logout
- **URL**: `POST /logout`
- **Description**: Logout the authenticated user
- **Headers**:
  - `Accept: application/json`
  - `X-XSRF-TOKEN: <token>`
- **Response**: `204 No Content` (on success)

---

### Product Endpoints

All product endpoints require authentication.

#### Get All Products
- **URL**: `GET /api/products`
- **Description**: Get all products with their current available stock quantities
- **Headers**:
  - `Accept: application/json`
  - `X-XSRF-TOKEN: <token>`
- **Response**: `200 OK`
```json
[
    {
        "id": 1,
        "name": "Milk",
        "price": 2.5,
        "stockQuantity": 100,
        "minStockQuantity": 20,
        "unit": "l",
        "updatedAt": "2026-01-20T12:00:00.000000Z"
    },
    ...
]
```

#### Get Product by ID
- **URL**: `GET /api/products/{id}`
- **Description**: Get a single product by ID with current stock quantity
- **Headers**:
  - `Accept: application/json`
  - `X-XSRF-TOKEN: <token>`
- **Parameters**:
  - `id` (path, required): Product ID
- **Response**: `200 OK`
```json
{
    "id": 1,
    "name": "Milk",
    "price": 2.5,
    "stockQuantity": 100,
    "minStockQuantity": 20,
    "unit": "l",
    "updatedAt": "2026-01-20T12:00:00.000000Z"
}
```

#### Get Available Quantity
- **URL**: `GET /api/products/{id}/available-quantity`
- **Description**: Get available quantity for a specific product (calculated from productInputs minus shoppingCartProducts)
- **Headers**:
  - `Accept: application/json`
  - `X-XSRF-TOKEN: <token>`
- **Parameters**:
  - `id` (path, required): Product ID
- **Response**: `200 OK`
```json
{
    "productId": 1,
    "availableQuantity": 47
}
```

---

### Shopping Cart Endpoints

All shopping cart endpoints require authentication.

#### Get Active Cart
- **URL**: `GET /api/cart`
- **Description**: Get or create active shopping cart for the authenticated user
- **Headers**:
  - `Accept: application/json`
  - `X-XSRF-TOKEN: <token>`
- **Response**: `200 OK`
```json
{
    "id": 1,
    "userId": 1,
    "sum": 15.50,
    "state": "active",
    "updatedAt": "2026-01-20T12:00:00.000000Z",
    "products": [
        {
            "id": 1,
            "shoppingCartId": 1,
            "userId": 1,
            "productId": 2,
            "quantity": 3,
            "product": {
                "id": 2,
                "name": "Bread",
                "price": 1.8,
                "unit": "pcs"
            }
        }
    ]
}
```

#### Add Product to Cart
- **URL**: `POST /api/cart/add`
- **Description**: Add a product to the shopping cart. If product already exists, quantity will be updated (not duplicated).
- **Headers**:
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `X-XSRF-TOKEN: <token>`
- **Body**:
```json
{
    "productId": 2,
    "quantity": 2
}
```
- **Response**: `200 OK`
```json
{
    "success": true,
    "cart": {
        "id": 1,
        "userId": 1,
        "sum": 15.50,
        "state": "active",
        "updatedAt": "2026-01-20T12:00:00.000000Z",
        "products": [...]
    },
    "updatedProduct": {
        "id": 2,
        "stockQuantity": 45
    }
}
```
- **Error Responses**:
  - `400 Bad Request`: Product not available or insufficient stock
```json
{
    "success": false,
    "message": "Product is not available in the requested quantity.",
    "availableQuantity": 10
}
```

#### Update Cart Item Quantity
- **URL**: `PUT /api/cart/products/{cartProductId}`
- **Description**: Update the quantity of a cart item
- **Headers**:
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `X-XSRF-TOKEN: <token>`
- **Parameters**:
  - `cartProductId` (path, required): Shopping cart product ID
- **Body**:
```json
{
    "quantity": 5
}
```
- **Response**: `200 OK`
```json
{
    "success": true,
    "cart": {...},
    "updatedProduct": {
        "id": 2,
        "stockQuantity": 42
    }
}
```
- **Error Responses**:
  - `400 Bad Request`: Insufficient stock
```json
{
    "success": false,
    "message": "Not enough stock available. Current stock: 10",
    "availableQuantity": 10
}
```

#### Remove Product from Cart
- **URL**: `DELETE /api/cart/products/{cartProductId}`
- **Description**: Remove a product from the shopping cart
- **Headers**:
  - `Accept: application/json`
  - `X-XSRF-TOKEN: <token>`
- **Parameters**:
  - `cartProductId` (path, required): Shopping cart product ID
- **Response**: `200 OK`
```json
{
    "success": true,
    "cart": {...},
    "updatedProduct": {
        "id": 2,
        "stockQuantity": 50
    }
}
```

#### Finish Order
- **URL**: `POST /api/cart/finish`
- **Description**: Finish the order - changes cart state to 'ordered'. A new active cart will be created on next add-to-cart action.
- **Headers**:
  - `Accept: application/json`
  - `X-XSRF-TOKEN: <token>`
- **Response**: `200 OK`
```json
{
    "success": true,
    "cart": {
        "id": 1,
        "userId": 1,
        "sum": 15.50,
        "state": "ordered",
        "updatedAt": "2026-01-20T12:00:00.000000Z",
        "products": [...]
    }
}
```

---

## Stock Calculation

Stock quantity is calculated dynamically:
```
Available Quantity = Sum(productInputs.addedQuantity) - Sum(shoppingCartProducts.quantity)
```

This includes items in all cart states (active, ordered, canceled, payed).

## Error Responses

All endpoints may return standard HTTP error codes:

- `400 Bad Request`: Invalid request data or business logic error
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation errors
- `500 Internal Server Error`: Server error

Error response format:
```json
{
    "success": false,
    "message": "Error message here"
}
```

## Testing with Postman

1. Import the Postman collection: `postman/Shopping Cart API.postman_collection.json`
2. Import the environment: `postman/Local Environment.postman_environment.json`
3. Update environment variables:
   - `user_email`: Your test user email
   - `user_password`: Your test user password
4. Start with the Login request to get CSRF token
5. Use other endpoints with the CSRF token

## Notes

- All authenticated endpoints require a valid session (login first)
- CSRF token must be included in headers for POST/PUT/DELETE requests
- Stock quantities are calculated in real-time
- Low stock notifications are triggered automatically when stock <= minStockQuantity
- Daily sales reports run automatically at 6:00 PM UTC
