# Stripe Webhook Setup Guide

## The Issue

Stripe webhooks are **NOT working automatically**. You need to configure them for payment methods to be saved.

---

## Solution: Two Options

### Option 1: For LOCAL Development (Testing)

#### Step 1: Install Stripe CLI

Download from: https://stripe.com/docs/stripe-cli

#### Step 2: Login to Stripe CLI

```bash
stripe login
```

#### Step 3: Forward Webhooks to Local Server

```bash
stripe listen --forward-to http://localhost:8000/stripe/webhook
```

This will give you a **webhook signing secret** like: `whsec_xxxxxxxxxxxxx`

#### Step 4: Update `.env` file

```env
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
```

#### Step 5: Test

Make a test payment with "Save Card" checked. Check logs:

```bash
tail -f storage/logs/laravel.log
```

You should see:

-   `Stripe webhook hit (pre-verify)`
-   `Stripe session object received`
-   `Payment method saved successfully`

---

### Option 2: For PRODUCTION (Live Server)

#### Step 1: Deploy Your Application

Your app must be accessible via a public URL (e.g., `https://yourdomain.com`)

#### Step 2: Configure Webhook in Stripe Dashboard

1. Go to: https://dashboard.stripe.com/webhooks
2. Click **"Add endpoint"**
3. Enter your webhook URL:
    ```
    https://yourdomain.com/stripe/webhook
    ```
4. Select events to listen for:

    - ✅ `checkout.session.completed` (REQUIRED)
    - ✅ `payment_intent.succeeded` (Recommended)
    - ✅ `payment_intent.payment_failed` (Recommended)

5. Click **"Add endpoint"**
6. Copy the **Signing Secret** (starts with `whsec_`)

#### Step 3: Update Production `.env`

```env
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
```

#### Step 4: Test in Production

-   Make a real/test payment
-   Check Stripe Dashboard → Webhooks → Events
-   Verify webhook was delivered successfully
-   Check your app logs

---

## Verify Setup

### 1. Check CSRF Exemption ✅

Already configured in `bootstrap/app.php`:

```php
$middleware->validateCsrfTokens(except:[
    'stripe/webhook',
]);
```

### 2. Check Route ✅

Already configured in `routes/web.php`:

```php
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);
```

### 3. Check Logs

After a payment, check `storage/logs/laravel.log` for:

```
[2025-12-24 10:30:00] local.WARNING: Stripe webhook hit (pre-verify)
[2025-12-24 10:30:01] local.INFO: Payment method saved successfully
```

---

## Troubleshooting

### No logs appearing?

-   Webhook not configured in Stripe Dashboard
-   Wrong webhook URL
-   Stripe CLI not running (local dev)

### "Signature verification failed"?

-   Wrong `STRIPE_WEBHOOK_SECRET` in `.env`
-   Clear config cache: `php artisan config:clear`

### Payment method not saved?

-   "Save Card" checkbox not checked during checkout
-   Webhook received but payment failed
-   Check logs for errors in `savePaymentMethodIfPresent()`

---

## Quick Test Command (Local)

```bash
# Terminal 1: Run Laravel
php artisan serve

# Terminal 2: Run Stripe CLI
stripe listen --forward-to http://localhost:8000/stripe/webhook

# Terminal 3: Trigger test webhook
stripe trigger checkout.session.completed
```

---

## Current Implementation Status

✅ CSRF exemption configured
✅ Webhook route created
✅ StripeWebhookController with payment method saving
✅ Customer attached to Stripe session
✅ Enhanced logging for debugging

⚠️ **ACTION REQUIRED:** Configure webhook endpoint (Option 1 or 2 above)
