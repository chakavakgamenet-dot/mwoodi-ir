# MWoodi API Contract v1

Base: `/api/v1`

## Auth
- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/logout`
- `GET /auth/me`
- `POST /auth/forgot-password`
- `POST /auth/reset-password`

## Catalog
- `GET /products`
- `GET /products/{slug}`
- `GET /categories`

## Cart
- `GET /cart`
- `POST /cart/items`
- `PATCH /cart/items/{productId}`
- `DELETE /cart/items/{productId}`
- `POST /cart/merge-guest`

## Checkout
- `GET /addresses`
- `POST /addresses`
- `PATCH /addresses/{id}`
- `DELETE /addresses/{id}`
- `POST /checkout/quote`
- `POST /orders`
- `GET /orders`
- `GET /orders/{orderNumber}`

## Payments
- `POST /payments/{orderNumber}/start`
- `GET /payments/callback`
- `POST /payments/webhook`

## Admin
- `GET /admin/dashboard`
- `GET /admin/orders`
- `PATCH /admin/orders/{id}`
- `GET /admin/customers`
- `GET /admin/products`
- `POST /admin/products`
- `PATCH /admin/products/{id}`
- `DELETE /admin/products/{id}`
- `GET /admin/inventory`
- `PATCH /admin/inventory/{productId}`
- `GET /admin/reports/sales`
- `GET /admin/settings`
- `PATCH /admin/settings`

## Rules
- Client never supplies authoritative price/discount/stock totals.
- Backend recalculates checkout totals inside a DB transaction.
- Order creation reserves inventory atomically.
- Payment callback verifies the gateway response before marking paid.
- Repeated callback returns the existing payment result without double fulfillment.
- Admin endpoints enforce role middleware.
