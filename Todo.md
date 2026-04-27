Run this on your **live local or staging instance** (not test DB) before submission.

---

### 1. Guest Flow

- [✅] Home page loads with hero banner, categories, products
- [✅ ] Category page filters products correctly
- [✅ ] Product detail page shows name, price, stock, images, reviews
- [✅ ] Search returns relevant products; empty query shows all or redirects

- [✅] Out-of-stock badge shows on product with `stock = 0`
- [ ] Add to cart as guest → session cart persists across pages
- [ ] View cart — correct product name, qty, subtotal, total
- [ ] Update quantity in cart — total recalculates
- [ ] Remove item from cart — item gone, total updates
- [ ] Cart icon shows correct item count in nav
- [ ] Guest tries to access `/checkout` → redirected to login

---

### 2. Auth Flow (Customer)

- [ ] Register with valid data → email verification sent
- [ ] Register with duplicate email → validation error shown
- [ ] Register with invalid mobile/password → field-level errors shown
- [ ] Unverified user logs in → redirected to verify email notice
- [ ] Click email verification link → account verified, redirected to home
- [ ] Login with correct credentials → session started, redirected
- [ ] Login with wrong password → error shown, no session
- [ ] Forgot password → email sent with reset link
- [ ] Reset password with valid token → password changed, redirected to login
- [ ] Reset password with expired token → error shown
- [ ] Logout → session destroyed, redirected to home

---

### 3. Customer Flow (Post-Login)

- [ ] Cart from guest session merges into logged-in cart
- [ ] Profile page shows correct name, email, mobile
- [ ] Update profile → changes saved, success flash shown
- [ ] Change password (correct old → new password) → success
- [ ] Change password (wrong old) → error shown
- [ ] Profile photo upload → photo visible in nav/profile
- [ ] Wishlist: add product → appears in wishlist page
- [ ] Wishlist: remove product → removed from list

---

### 4. Cart & Checkout Flow

- [ ] Add same product twice → quantity increments, not duplicate row
- [ ] Add product with `stock = 1`, try qty = 2 → validation error
- [ ] Cart total = sum of (price × qty) for all items
- [ ] Proceed to checkout → order summary shows correct items + total
- [ ] Submit checkout → order created, redirected to payment or confirmation
- [ ] After order placed → cart is cleared

---

### 5. Payment Flow (Stripe)

- [ ] Checkout redirects to Stripe hosted page
- [ ] Pay with test card `4242 4242 4242 4242` → payment success
- [ ] Pay with declined card `4000 0000 0000 0002` → payment failure page shown (no raw error exposed to user)
- [ ] After successful payment → order status updated to `paid`
- [ ] After successful payment → order confirmation email sent
- [ ] Stripe webhook fires `checkout.session.completed` → payment record created in DB
- [ ] Duplicate webhook event → idempotent (no duplicate payment record)
- [ ] Invalid Stripe signature on webhook → 403 returned

---

### 6. Order Flow (Customer)

- [ ] My Orders page lists all orders for logged-in user
- [ ] Order detail page shows items, quantities, prices, status
- [ ] Customer cannot view another customer's order (403 or redirect)
- [ ] Order status badge shows correct state (pending / processing / shipped / delivered)
- [ ] Status-change notification email received at each step (trigger from admin side)

---

### 7. Admin Flow — Access & Security

- [ ] Non-admin user tries `/admin/*` → redirected (403 or login)
- [ ] Admin login → redirected to admin dashboard
- [ ] Admin dashboard shows stats (orders, products, users counts)
- [ ] `/clear-cache` requires admin auth → unauthenticated → 403

---

### 8. Admin — Product Management

- [ ] Create product with image → listed on site immediately
- [ ] Create product without image → succeeds (image optional)
- [ ] Create product with image > 5 MB → validation error shown
- [ ] Edit product → changes reflected on frontend
- [ ] Toggle Featured → product appears/disappears on featured section
- [ ] Toggle Latest → same for latest section
- [ ] Delete product → removed from site; no 500 if it had orders (check FK handling)
- [ ] Bulk delete 2+ products → both removed

---

### 9. Admin — Inventory / Stock

- [ ] Set product stock to 0 → "Out of stock" shows on product page
- [ ] Customer cannot add out-of-stock product to cart
- [ ] After order placed → product stock decremented correctly
- [ ] Stock shows correct count in admin product list

---

### 10. Admin — Order Management

- [ ] Orders list shows all orders with correct statuses
- [ ] Change order status → customer receives email notification
- [ ] Order detail page shows correct items, user, payment info
- [ ] Filter/search orders by status or order number (if implemented)

---

### 11. Admin — User Management

- [ ] Users list shows all registered users
- [ ] View user detail → order history visible
- [ ] Change user role (admin ↔ customer) → takes effect immediately

---

### 12. Admin — Category & Brand Management

- [ ] Create category (active/inactive) → appears/disappears in nav
- [ ] Edit category name → reflected in nav and product listings
- [ ] Delete category with no products → succeeds
- [ ] Delete category with products → handles gracefully (error or reassign)
- [ ] Create / edit / delete brand → works without errors

---

### 13. Cross-Cutting Checks

- [ ] All pages return 200 on desktop Chrome + mobile Firefox
- [ ] No `APP_DEBUG` stack traces visible on any error (check `.env APP_DEBUG=false`)
- [ ] Security headers present on every response (`X-Content-Type-Options`, `Referrer-Policy`)
- [ ] HTTPS redirects work (if on live server)
- [ ] robots.txt accessible at robots.txt
- [ ] 404 page shows for non-existent routes (not Laravel exception page)
- [ ] 403 page shows for forbidden routes
- [ ] PWA manifest accessible at `/manifest.json`

---

**Confirm the deletions above and I'll run them all at once.**
