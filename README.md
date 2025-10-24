# Binance Pay API Integration — PHP Starter Kit

> **A production-ready, modular, secure, and Render-deployable Binance Pay integration in pure PHP**  
> Built with **MVC**, **Eloquent ORM**, **FastRoute**, **Guzzle**, and strict OOP principles.  
> Supports **order creation**, **QR/checkout URL generation**, and **verified webhook callbacks**.

---

## Overview

This project provides a complete backend service to integrate **Binance Pay** into your application using PHP. It handles:

- Securely creating Binance Pay orders (with HMAC-SHA512 signing)
- Generating QR codes or redirect URLs for user payment
- Receiving and **verifying signed callbacks** from Binance
- Storing orders, transactions, and raw webhook payloads for audit
- Clean RESTful API with no `.php` extensions
- Ready for **Render**, Docker, or any VPS

Perfect for SaaS, e-commerce, or crypto-native apps needing **USDT/BUSD/BTC payments**.

---

## 🛠️ Tech Stack

| Component        | Technology                     |
|------------------|-------------------------------|
| Language         | PHP 8.1+                      |
| ORM              | Eloquent (via `illuminate/database`) |
| HTTP Client      | Guzzle 7                      |
| Router           | FastRoute                     |
| Request/Response | Symfony HttpFoundation        |
| Validation       | Respect/Validation            |
| Logging          | Monolog                       |
| Env Management   | vlucas/phpdotenv              |
| Web Server       | Apache (with `.htaccess`)     |
| Deployment       | Render, Docker, or any LAMP   |

---

## Project Structure

```
binance-pay-api/
├── public/                  # Web root (only index.php exposed)
│   ├── index.php            # Front controller
│   └── .htaccess            # URL rewriting + security headers
├── app/
│   ├── Controllers/         # Thin HTTP layer
│   ├── Services/            # Business logic (BinancePayService, PaymentService)
│   ├── Repositories/        # DB persistence (Eloquent-based)
│   ├── Models/              # Eloquent models (Order, PaymentCallback)
│   ├── Interfaces/          # Contracts for testability
│   ├── Abstracts/           # Base classes
│   ├── Utils/               # Helpers (Signature, UUID, Logger, Validator)
│   ├── Config/              # Database config
│   ├── Routes/              # API routes
│   ├── Middleware/          # CORS, signature verification
│   └── Migrations/          # SQL schema files
├── storage/logs/            # Application logs
├── .env                     # Environment variables (not committed)
├── .env.example             # Template for .env
├── composer.json            # Dependencies
└── Dockerfile               # For Render or containerized deployment
```

---

## Binance Pay — Key Concepts

### 1. **Sandbox vs Production**
- **Sandbox URL**: `https://bpay-pre.binanceapi.com`  
  Use for development. Get sandbox keys from [Binance Pay Merchant Portal (Testnet)](https://bpay.binance.com/en/dashboard).
- **Production URL**: `https://bpay.binanceapi.com`  
  Only switch after full testing.

### 2. **Authentication**
Binance uses **API Key + Secret + Certificate SN**:
- `merchantId`: Your merchant ID (e.g., `987654321`)
- `merchantApiKey`: Public key (sent in `BinancePay-Certificate-SN` header)
- `merchantSecretKey`: **SECRET** — used to sign requests (never expose!)

### 3. **Request Signing (Outbound)**
Every request to Binance must include:
```http
BinancePay-Timestamp: 1729800000000        # milliseconds
BinancePay-Nonce: a1b2c3d4-e5f6-7890...
BinancePay-Certificate-SN: your_api_key
BinancePay-Signature: UPPERCASE_HEX(HMAC-SHA512(secret, timestamp + "\n" + nonce + "\n" + body + "\n"))
```

### 4. **Callback Verification (Inbound)**
Binance sends the same headers to your webhook. **You must verify the signature** before processing to prevent spoofing.

> **Never trust callback data without signature verification!**

---

## Quick Start (Local Development)

### Step 1: Clone & Install

```bash
git clone https://github.com/yourname/binance-pay-api.git
cd binance-pay-api
composer install
```

### Step 2: Configure Environment

```bash
cp .env.example .env
```

Edit `.env`:

```ini
# Database
DB_HOST=127.0.0.1
DB_DATABASE=binance_pay
DB_USERNAME=root
DB_PASSWORD=your_db_password

# Binance Sandbox (get these from Binance Pay Dashboard)
BINANCE_MERCHANT_ID=987654321
BINANCE_API_KEY=your_sandbox_api_key
BINANCE_SECRET_KEY=your_sandbox_secret_key
BINANCE_BASE_URL=https://bpay-pre.binanceapi.com

# Your public webhook URL (use ngrok for local testing)
WEBHOOK_URL=https://your-ngrok-url.ngrok.io/api/v1/payments/callback
```

### Step 3: Create Database

```sql
CREATE DATABASE binance_pay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 4: Run Migrations

Manually execute SQL files in `app/Migrations/`:
- `2025_10_24_000000_create_orders_table.sql`
- `2025_10_24_000001_create_payment_callbacks_table.sql`
- `2025_10_24_000002_create_transactions_table.sql`

> Or use the CLI runner:
> ```bash
> php migrate.php
> ```

### Step 5: Start Server

```bash
php -S localhost:8000 -t public/
```

> Your API is now running at `http://localhost:8000`

---

## API Endpoints

### `POST /api/v1/payments`
**Create a new Binance Pay order**

**Request Body (JSON):**
```json
{
  "user_id": 123,
  "amount": 10.50,
  "currency": "USDT",
  "product_name": "Premium Plan",
  "returnUrl": "https://yourapp.com/success",
  "cancelUrl": "https://yourapp.com/cancel"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "order_id": "a1b2c3d4-...",
    "merchant_trade_no": "ORDER_A1B2C3D4...",
    "qr_code_url": "https://...",
    "checkout_url": "https://..."
  }
}
```

> Display `qr_code_url` as QR code, or redirect user to `checkout_url`.

---

### `GET /api/v1/payments/{order_id}`
**Get order status**

**Response:**
```json
{
  "id": "a1b2c3d4-...",
  "merchant_trade_no": "ORDER_...",
  "amount": "10.50000000",
  "currency": "USDT",
  "status": "COMPLETED",
  "created_at": "2025-10-24T10:00:00.000000Z",
  "updated_at": "2025-10-24T10:05:00.000000Z"
}
```

---

### `POST /api/v1/payments/callback`
**Binance Webhook Endpoint (DO NOT CALL MANUALLY)**

Binance will POST to this URL when payment status changes.

> Signature is automatically verified.  
> Payload is logged to `payment_callbacks` table.  
> Order status is updated to `COMPLETED` on success.

**Expected Binance Payload (simplified):**
```json
{
  "data": {
    "merchantTradeNo": "ORDER_...",
    "transactionId": "TX123456789",
    "status": "PAY_SUCCESS",
    "amount": "10.50",
    "currency": "USDT"
  }
}
```

> **Register this URL in Binance Pay Merchant Portal** under *Webhook Settings*.

---

## Docker & Render Deployment

### Deploy to Render (Recommended)

1. Push code to GitHub
2. Go to [Render Dashboard](https://dashboard.render.com/)
3. Create **New Web Service**
   - **Build Command**: `composer install`
   - **Start Command**: `apache2-foreground`
   - **Environment**: `Docker`
   - **Root Directory**: `public`
4. Add **Environment Variables** from `.env`
5. Set **Health Check Path**: `/api/v1/payments/invalid` (expect 404)

> Render provides free TLS, custom domains, and MySQL add-ons.

---

### Docker (Alternative)

Build and run:

```bash
docker build -t binance-pay-api .
docker run -p 8080:80 -e DB_HOST=host.docker.internal binance-pay-api
```

> Replace `host.docker.internal` with your DB host.

---

## Security Best Practices

| Risk | Mitigation |
|------|-----------|
| Secret leakage | Store `BINANCE_SECRET_KEY` only in `.env` (never in code/Git) |
| Callback spoofing | Signature verification on every webhook |
| SQL injection | Eloquent ORM parameter binding |
| XSS | No HTML output; API-only |
| Rate abuse | Add rate limiting (Redis + middleware) |
| Replay attacks | Validate `BinancePay-Timestamp` (±5 min window) |

> All outbound requests are signed.  
> All inbound callbacks are verified.  
> Raw payloads are stored for audit.

---

## Testing with Binance Sandbox

1. Get **Sandbox Credentials** from [Binance Pay Testnet](https://bpay.binance.com/en/dashboard)
2. Use **Test USDT** wallet in Binance app
3. Create order via `POST /api/v1/payments`
4. Scan QR or visit `checkout_url`
5. Approve payment in Binance app
6. Observe:
   - Callback received at `/api/v1/payments/callback`
   - Order status updated to `COMPLETED`
   - Log entry in `storage/logs/app.log`

> Use **ngrok** for local webhook testing:
> ```bash
> ngrok http 8000
> # Then set WEBHOOK_URL=https://your-ngrok-url.ngrok.io/api/v1/payments/callback
> ```

---

## Customization Guide

### Add New Currencies
Edit validator in `app/Utils/Validator.php`:
```php
->key('currency', v::stringType()->in(['USDT', 'BUSD', 'BTC', 'ETH', 'BNB'])->notEmpty(), false)
```

### Change Order ID Format
Modify `app/Services/PaymentService.php`:
```php
$merchantTradeNo = 'YOUR_PREFIX_' . date('Ymd') . '_' . strtoupper(bin2hex(random_bytes(8)));
```

### Add User Authentication
- Inject `user_id` from your auth system into the payment request
- Validate user ownership in `PaymentController::show`

### Enable Logging to External Service
Extend `LoggerFactory` to add Slack, Sentry, or CloudWatch handlers.

---

## Troubleshooting

| Issue | Solution |
|------|---------|
| `500 Internal Server Error` | Check `storage/logs/app.log` |
| `Invalid signature` | Verify `BINANCE_SECRET_KEY` matches Binance dashboard |
| `Order not found in callback` | Ensure `merchantTradeNo` matches your local `merchant_trade_no` |
| `404 on all routes` | Confirm `.htaccess` is in `public/` and `mod_rewrite` is enabled |
| `DB connection failed` | Test DB credentials with `mysql -h ... -u ... -p` |

---

## License

MIT — Use freely in commercial or open-source projects.

---

## Support

- Binance Pay Docs: https://developers.binance.com/docs/binance-pay
- Binance Pay Postman Collection: Available in Binance Developer Portal
- Issues? Open a GitHub issue or contact Binance Support

---

> **You’re ready to accept crypto payments in minutes!**  
> Deploy, integrate, and scale with confidence.