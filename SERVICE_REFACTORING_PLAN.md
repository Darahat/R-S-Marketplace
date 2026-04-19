# Service Refactoring Plan - Single Responsibility Principle

## Current State Analysis

### 🔴 **Critical Violations - Services with Multiple Responsibilities**

#### 1. **CheckoutService** (297 lines)

**Current Responsibilities:**

- ✅ Checkout flow orchestration (index, review, buyNow)
- ❌ **Cart retrieval and calculations**
- ❌ **Order creation** ← Should be in OrderService
- ❌ **Stock updates** ← Should be separate StockService
- ❌ **Address retrieval**
- ❌ **Payment method persistence** (Stripe)
- ❌ **Order queries** (toPayOrder, toCheckSingleOrder)
- ❌ **Payment success handling** (Stripe-specific logic)

**Violation Count:** 7 different concerns

#### 2. **PaymentProcessService** (250 lines)

**Current Responsibilities:**

- ✅ Payment processing orchestration
- ❌ **Cart retrieval** ← Duplicates CartService
- ❌ **Product availability validation** ← Should be in StockService
- ❌ **Address retrieval** ← Duplicates AddressService
- ❌ **Stripe customer management** ← Should be StripeCustomerService
- ❌ **Stripe session building** ← Should be StripeSessionService
- ❌ **Cart cleanup** ← Should be in CartService
- ❌ **Payment record creation** ← Should be OrderService/PaymentService
- ❌ **Calls CheckoutService for order creation** ← Wrong dependency

**Violation Count:** 8 different concerns

#### 3. **ProductService** (201 lines)

**Current Responsibilities:**

- ✅ Product CRUD operations
- ✅ Product queries (filtered/paginated)
- ❌ **Stock management** (updateStock, incrementSoldCount) ← Should be StockService
- ✅ Featured product management (acceptable - product attribute)
- ✅ Low stock queries (acceptable - product query)

**Violation Count:** 1 major concern (stock updates)

---

## 🎯 **Refactoring Strategy**

### **Phase 1: Extract Order Operations** (High Priority)

**Goal:** Centralize all order-related operations in OrderService

**New/Modified Services:**

- `OrderService` - Create orders, manage order lifecycle
- `OrderQueryService` (Optional) - Complex order queries

**Actions:**

1. Move `createOrder()` + `createOrderItem()` from CheckoutRepository → OrderRepository
2. Add `createOrder($orderData, $items)` to OrderService
3. Add `getToPayOrders($userId)` to OrderService
4. Add `findOrderForPayment($orderNumber, $userId)` to OrderService
5. Remove order methods from CheckoutService

**Impact:**

- CheckoutService: 297 → ~200 lines
- OrderService: 64 → ~140 lines

---

### **Phase 2: Extract Stock Management** (High Priority)

**Goal:** Single service responsible for inventory/stock operations

**New Service:**

- `StockManagementService`
    - `decrementStock($productId, $quantity)`
    - `validateAvailability($cartItems)`
    - `reserveStock($items)` (future: for pending orders)
    - `releaseStock($items)` (future: for cancelled orders)

**Actions:**

1. Create StockManagementService
2. Move `updateProductStock()` from CheckoutService
3. Move `verifyProductAvailability()` from PaymentProcessService
4. Move `updateStock()` from ProductService
5. Dispatch `UpdateProductSalesMetricsJob` from this service

**Impact:**

- CheckoutService: ~200 → ~165 lines
- PaymentProcessService: 250 → ~220 lines
- ProductService: 201 → ~185 lines
- New StockManagementService: ~80 lines

---

### **Phase 3: Extract Stripe Operations** (Medium Priority)

**Goal:** Isolate all Stripe-specific logic from business services

**New Services:**

- `StripeCustomerService`
    - `ensureCustomerExists($user)`
    - `syncCustomerData($user)`
- `StripeSessionService`
    - `createCheckoutSession($order, $items, $options)`
    - `buildLineItems($cartItems, $isSubscription)`
    - `retrieveSession($sessionId)`

**Actions:**

1. Extract `ensureStripeCustomer()` from PaymentProcessService → StripeCustomerService
2. Extract `buildStripeLineItems()` from PaymentProcessService → StripeSessionService
3. Extract Stripe session creation logic → StripeSessionService
4. Move payment method saving from CheckoutService → StripeWebhookService (belongs with webhook logic)

**Impact:**

- PaymentProcessService: ~220 → ~120 lines
- CheckoutService: ~165 → ~120 lines
- New StripeCustomerService: ~50 lines
- New StripeSessionService: ~80 lines

---

### **Phase 4: Clean CheckoutService** (High Priority)

**Goal:** CheckoutService should ONLY orchestrate checkout flow

**Final CheckoutService Responsibilities:**

- ✅ Display checkout page data
- ✅ Handle "Buy Now" flow
- ✅ Process checkout review/address selection
- ✅ Display payment page data
- ✅ Calculate totals (helper method - acceptable)
- ❌ Everything else moved to proper services

**Refactored Flow:**

```php
// CheckoutService calls:
OrderService->createOrder()        // instead of doing it itself
StockManagementService->decrement() // instead of updateProductStock()
```

**Impact:**

- Final CheckoutService: ~120 lines (60% reduction)

---

### **Phase 5: Clean PaymentProcessService** (Medium Priority)

**Goal:** PaymentProcessService should ONLY orchestrate payment processing

**Final PaymentProcessService Responsibilities:**

- ✅ Orchestrate payment flow
- ✅ Route to correct payment processor (Stripe/Cash/Bkash)
- ❌ No direct cart/stock/order operations

**Refactored Flow:**

```php
// PaymentProcessService calls:
CartService->getItems()                    // instead of getCartItems()
StockManagementService->validateAvailability()  // instead of verifyProductAvailability()
AddressService->getByIdForUser()           // instead of getCheckoutAddress()
StripeSessionService->create()             // instead of building sessions itself
OrderService->createOrder()                // instead of CheckoutService->createOrderData()
CartService->clear()                       // instead of clearCartAndSession()
```

**Impact:**

- Final PaymentProcessService: ~100 lines (60% reduction)

---

## 📋 **Implementation Steps**

### **Step 1: OrderService Refactoring** ⚠️ BREAKING CHANGES

**Files to Create:**

- None (OrderService exists)

**Files to Modify:**

- `app/Services/OrderService.php` - Add order creation methods
- `app/Repositories/OrderRepository.php` - Move createOrder/createOrderItem from CheckoutRepository
- `app/Repositories/CheckoutRepository.php` - Remove order creation methods
- `app/Services/CheckoutService.php` - Remove createOrderData, call OrderService instead
- `app/Services/PaymentProcessService.php` - Call OrderService instead of CheckoutService
- `app/Http/Controllers/PaymentProcessController.php` - Update dependencies

**Estimated Complexity:** Medium  
**Estimated Time:** 30 minutes  
**Risk:** Medium (multiple controllers affected)

---

### **Step 2: StockManagementService Creation** ⚠️ NEW SERVICE

**Files to Create:**

- `app/Services/StockManagementService.php`
- `app/Repositories/StockRepository.php` (optional - can use ProductRepository)

**Files to Modify:**

- `app/Services/CheckoutService.php` - Remove updateProductStock
- `app/Services/PaymentProcessService.php` - Remove verifyProductAvailability
- `app/Services/ProductService.php` - Remove updateStock
- `app/Http/Controllers/PaymentProcessController.php` - Inject StockManagementService

**Estimated Complexity:** Low  
**Estimated Time:** 20 minutes  
**Risk:** Low (isolated concern)

---

### **Step 3: StripeSessionService Creation** ⚠️ NEW SERVICE

**Files to Create:**

- `app/Services/StripeSessionService.php`
- `app/Services/StripeCustomerService.php`

**Files to Modify:**

- `app/Services/PaymentProcessService.php` - Remove Stripe-specific code
- `app/Services/CheckoutService.php` - Remove savePaymentMethodFromIntent

**Estimated Complexity:** Medium  
**Estimated Time:** 25 minutes  
**Risk:** Low (Stripe logic is isolated)

---

### **Step 4: Final CheckoutService Cleanup**

**Files to Modify:**

- `app/Services/CheckoutService.php` - Remove all delegated methods

**Estimated Complexity:** Low  
**Estimated Time:** 10 minutes  
**Risk:** Low (cleanup only)

---

### **Step 5: Final PaymentProcessService Cleanup**

**Files to Modify:**

- `app/Services/PaymentProcessService.php` - Delegate to proper services

**Estimated Complexity:** Medium  
**Estimated Time:** 20 minutes  
**Risk:** Medium (payment flow is critical)

---

### **Step 6: Remove Dead Code**

**Files to Modify:**

- `app/Services/PaymentProcessService.php` - Remove `index(){}` empty method

**Estimated Complexity:** Trivial  
**Estimated Time:** 2 minutes  
**Risk:** None

---

## 📊 **Expected Outcome**

### Service Line Counts (Before → After)

| Service                        | Before | After | Reduction           |
| ------------------------------ | ------ | ----- | ------------------- |
| CheckoutService                | 297    | ~120  | **60%**             |
| PaymentProcessService          | 250    | ~100  | **60%**             |
| ProductService                 | 201    | ~185  | **8%**              |
| OrderService                   | 64     | ~140  | +119% (proper size) |
| **NEW** StockManagementService | 0      | ~80   | -                   |
| **NEW** StripeSessionService   | 0      | ~80   | -                   |
| **NEW** StripeCustomerService  | 0      | ~50   | -                   |

### Benefits

- ✅ Single Responsibility Principle enforced
- ✅ Each service has one clear purpose
- ✅ Services are reusable across different contexts
- ✅ Easier to test (smaller, focused units)
- ✅ Easier to maintain (changes isolated)
- ✅ Better separation of concerns
- ✅ Reduced coupling between services

### Dependency Flow (After Refactoring)

```
Controllers
    ↓
PaymentProcessService → OrderService
                     → StockManagementService
                     → StripeSessionService
                     → CartService
                     → AddressService

CheckoutService → OrderService (for queries only)
               → AddressService
               → CartService

OrderService → OrderRepository
            → StockManagementService (for metrics job)

StockManagementService → ProductRepository
                      → UpdateProductSalesMetricsJob
```

---

## 🚀 **Recommended Execution Order**

1. **Step 1:** OrderService refactoring (CRITICAL PATH)
2. **Step 6:** Remove dead code (quick win)
3. **Step 2:** StockManagementService (low risk)
4. **Step 3:** StripeSessionService (isolated)
5. **Step 4:** CheckoutService cleanup
6. **Step 5:** PaymentProcessService cleanup

**Total Estimated Time:** ~2 hours  
**Risk Level:** Medium (requires careful testing)

---

## ⚠️ **Testing Requirements**

After each step, verify:

- [ ] Checkout flow works (cart → address → payment)
- [ ] Buy Now flow works
- [ ] Stripe payment processing works
- [ ] Cash/Bkash payment works
- [ ] Stock decrements correctly
- [ ] Orders are created with correct data
- [ ] "To Pay" orders list works
- [ ] Payment completion works
- [ ] No breaking changes in admin panel

---

## 🔄 **Alternative: Phased Rollout**

If full refactoring is too risky, we can:

1. Start with **Step 1** (OrderService) only
2. Monitor production for 1 week
3. Proceed with Steps 2-3
4. Monitor again
5. Complete Steps 4-6

This reduces deployment risk but extends timeline to ~2-3 weeks.
