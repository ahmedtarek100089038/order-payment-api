# API Documentation

Base URL: `http://localhost:8000/api`

## Authentication Endpoints

### Register User
**POST** `/auth/register`

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {...},
    "token": "eyJ0eXAiOiJKV1Qi...",
    "token_type": "bearer"
  }
}
```

---

### Login User
**POST** `/auth/login`

**Request:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

---

### Get User Profile
**GET** `/auth/me`

**Headers:**
```
Authorization: Bearer {token}
```

---

### Logout
**POST** `/auth/logout`

**Headers:**
```
Authorization: Bearer {token}
```

---

### Refresh Token
**POST** `/auth/refresh`

**Headers:**
```
Authorization: Bearer {token}
```

---

## Order Endpoints

### Create Order
**POST** `/orders`

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "items": [
    {
      "product_name": "Laptop",
      "quantity": 1,
      "price": 999.99
    }
  ]
}
```

---

### Get All Orders
**GET** `/orders?status=confirmed&per_page=10`

**Headers:**
```
Authorization: Bearer {token}
```

---

### Get Single Order
**GET** `/orders/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

---

### Update Order
**PUT** `/orders/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Request:**
```json
{
  "status": "confirmed"
}
```

---

### Delete Order
**DELETE** `/orders/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

---

## Payment Endpoints

### Process Payment
**POST** `/payments`

**Headers:**
```
Authorization: Bearer {token}
```

**Request (Credit Card):**
```json
{
  "order_id": 1,
  "payment_method": "credit_card",
  "payment_details": {
    "card_number": "4111111111111111",
    "expiry_month": 12,
    "expiry_year": 2025,
    "cvv": "123"
  }
}
```

**Request (PayPal):**
```json
{
  "order_id": 1,
  "payment_method": "paypal",
  "payment_details": {
    "paypal_email": "buyer@example.com"
  }
}
```

**Request (Stripe):**
```json
{
  "order_id": 1,
  "payment_method": "stripe",
  "payment_details": {
    "stripe_token": "tok_visa"
  }
}
```

---

### Get All Payments
**GET** `/payments?order_id=1&status=successful`

**Headers:**
```
Authorization: Bearer {token}
```

---

### Get Single Payment
**GET** `/payments/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

---

### Get Order Payments
**GET** `/payments/order/{order_id}`

**Headers:**
```
Authorization: Bearer {token}
```

---

## Business Rules

1. ✅ Orders start with status `pending`
2. ✅ Payments can only be processed for `confirmed` orders
3. ✅ Orders with payments cannot be deleted
4. ✅ Users can only access their own orders and payments

## Payment Gateway Extensibility

To add a new payment gateway (e.g., Square):

1. Create gateway class implementing `PaymentGatewayInterface`
2. Register in `PaymentGatewayManager::registerDefaultGateways()`
3. Add config to `config/payment.php`
4. Update `ProcessPaymentRequest` validation rules

No changes to controllers or services required!