# Redis Learning Guide for R-S-Marketplace

## Table of Contents

1. [What is Redis and Why Use It?](#what-is-redis-and-why-use-it)
2. [Current State Analysis](#current-state-analysis)
3. [Installation & Setup](#installation--setup)
4. [Exercise 1: Database Caching (Beginner)](#exercise-1-database-caching-beginner)
5. [Exercise 2: Session Management (Intermediate)](#exercise-2-session-management-intermediate)
6. [Exercise 3: Queue Jobs (Intermediate)](#exercise-3-queue-jobs-intermediate)
7. [Exercise 4: Advanced Caching Patterns](#exercise-4-advanced-caching-patterns)
8. [Exercise 5: Rate Limiting & Locks (Advanced)](#exercise-5-rate-limiting--locks-advanced)
9. [Monitoring & Debugging](#monitoring--debugging)
10. [Common Issues & Solutions](#common-issues--solutions)

---

## What is Redis and Why Use It?

### What is Redis?

Redis (Remote Dictionary Server) is an **in-memory data structure store** that works as:

-   **Cache**: Store frequently accessed data in RAM for ultra-fast retrieval
-   **Session Store**: Manage user sessions across multiple servers
-   **Message Broker**: Handle background jobs and queues
-   **Database**: Persist data with various data structures (strings, lists, sets, hashes)

### Why Your Marketplace Needs Redis

#### Current Problems in Your Project:

1. **Database Overload**: Every page load queries the database for categories (navigation bar)
2. **Slow Product Listings**: Homepage queries 5 different product sets on every request
3. **Session Bottleneck**: Guest cart/wishlist stored in file-based sessions
4. **No Background Processing**: All tasks run synchronously (emails, notifications)

#### Benefits You'll Gain:

-   **10-100x faster data access** (RAM vs Disk)
-   **Reduced database load** by 60-80%
-   **Better user experience** with instant page loads
-   **Scalability**: Handle more concurrent users
-   **Job queues**: Process tasks in the background

---

## Current State Analysis

### Where Your App Uses Data Storage Now:

#### 1. **Navigation Categories** (`app/Providers/AppServiceProvider.php:32`)

```php
$categories = Cache::remember('nav_categories', 60, function () {
    return DB::table('categories')->where('status', true)->get();
});
```

-   **Current**: Database cache (saves to `cache` table)
-   **Impact**: Still hits database on every cache miss
-   **Redis Benefit**: 100x faster cache retrieval from RAM

#### 2. **Homepage Products** (`app/Http/Controllers/HomeController.php:78-106`)

```php
'latestProducts' => DB::table('products')->where('is_latest', true)->take(8)->get(),
'bestSellingProducts' => DB::table('products')->where('is_best_selling', true)->take(8)->get(),
'discountProducts' => DB::table('products')->where('discount_price', '>', 0)->take(8)->get(),
// ... 2 more queries
```

-   **Current**: 5 database queries on EVERY homepage visit
-   **Impact**: Slow page loads, high database CPU
-   **Redis Benefit**: Cache these for 5-10 minutes, instant retrieval

#### 3. **Guest Cart & Wishlist** (Various Controllers)

```php
$cart = session('cart', []);  // File-based session storage
```

-   **Current**: Stored in `storage/framework/sessions/` files
-   **Impact**: Disk I/O on every cart operation, not scalable
-   **Redis Benefit**: Session data in RAM, share across servers

#### 4. **Product Category Pages** (`HomeController.php:175`)

```php
$products_db = DB::table('products')
    ->whereRaw('FIND_IN_SET(?, category_id)', [$category->id])
    ->paginate(10);
```

-   **Current**: Database query every time, even for popular categories
-   **Redis Benefit**: Cache paginated results per category

---

## Installation & Setup

### Step 1: Install Redis Server (Windows)

#### Option A: Using Docker (Recommended)

```powershell
# Pull Redis image
docker pull redis:7-alpine

# Run Redis container
docker run -d `
  --name redis-marketplace `
  -p 6379:6379 `
  redis:7-alpine

# Verify it's running
docker ps
```

#### Option B: Using WSL2 (Windows Subsystem for Linux)

```powershell
# Open WSL (Ubuntu)
wsl

# In WSL terminal:
sudo apt update
sudo apt install redis-server -y

# Start Redis
sudo service redis-server start

# Verify
redis-cli ping
# Should return: PONG
```

#### Option C: Native Windows (via Memurai - Redis fork)

Download from: https://www.memurai.com/get-memurai

### Step 2: Install PHP Redis Client

You have two options:

#### Option A: Predis (Pure PHP - Easier)

```powershell
composer require predis/predis
```

Then in `.env`:

```env
REDIS_CLIENT=predis
```

#### Option B: PhpRedis Extension (Faster - Requires compilation)

-   Download `php_redis.dll` matching your PHP version from https://windows.php.net/downloads/pecl/releases/redis/
-   Copy to `php/ext/` directory
-   Add to `php.ini`:
    ```ini
    extension=php_redis.dll
    ```
-   Restart Apache/PHP-FPM
-   In `.env`:
    ```env
    REDIS_CLIENT=phpredis
    ```

**Recommendation**: Start with Predis for learning, switch to PhpRedis for production.

### Step 3: Verify Connection

```powershell
# Start your Laravel server
php artisan serve

# In another terminal, test Redis connection
php artisan tinker
```

```php
// In Tinker:
use Illuminate\Support\Facades\Redis;

Redis::set('test', 'Hello Redis!');
Redis::get('test'); // Should return: "Hello Redis!"
```

---

## Exercise 1: Database Caching (Beginner)

### Goal

Learn how Redis caching reduces database queries for frequently accessed data.

### Feature: Homepage Product Lists

#### Current Problem:

Open `app/Http/Controllers/HomeController.php` (lines 78-106). You're running **5 separate database queries** on every homepage load:

-   Latest Products
-   Best Selling Products
-   Discount Products
-   Regular Products
-   Suggested Products

#### Why This Matters:

-   If 100 users visit your homepage = **500 database queries**
-   Product lists rarely change (maybe once per hour)
-   Perfect candidate for caching!

### Step-by-Step Implementation:

#### Step 1: Update `.env` Configuration

```env
# Change from database to redis
CACHE_STORE=redis

# Redis connection (should already exist)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_CLIENT=predis  # or phpredis
```

#### Step 2: Clear Existing Cache

```powershell
php artisan config:clear
php artisan cache:clear
```

#### Step 3: Modify HomeController

Open `app/Http/Controllers/HomeController.php` and find the `index()` method around line 78.

**BEFORE (No Caching):**

```php
'latestProducts' => (clone $productBase)
    ->where('is_latest', true)
    ->orderBy('created_at', 'desc')
    ->take(8)
    ->get(),
```

**AFTER (With Redis Caching):**

```php
use Illuminate\Support\Facades\Cache;

// Add this at the top of index() method, around line 28
$productBase = DB::table('products')->where('stock', '>', 0);

// Then wrap each product query with Cache::remember()
'latestProducts' => Cache::remember('homepage:latest_products', 600, function () use ($productBase) {
    return (clone $productBase)
        ->where('is_latest', true)
        ->orderBy('created_at', 'desc')
        ->take(8)
        ->get();
}),

'bestSellingProducts' => Cache::remember('homepage:best_selling', 600, function () use ($productBase) {
    return (clone $productBase)
        ->where('is_best_selling', true)
        ->orderBy('sold_count', 'desc')
        ->take(8)
        ->get();
}),

'discountProducts' => Cache::remember('homepage:discounts', 600, function () use ($productBase) {
    return (clone $productBase)
        ->where('discount_price', '>', 0)
        ->take(8)
        ->get();
}),

'regularProducts' => Cache::remember('homepage:regular', 600, function () use ($productBase) {
    return (clone $productBase)
        ->inRandomOrder()
        ->take(8)
        ->get();
}),

'suggestedProducts' => Cache::remember('homepage:suggested', 600, function () use ($productBase) {
    return (clone $productBase)
        ->where('featured', true)
        ->take(8)
        ->get();
}),
```

#### What This Does:

-   `Cache::remember(key, ttl, callback)`:
    -   **key**: Unique identifier for this cache entry
    -   **ttl**: Time-to-live in seconds (600 = 10 minutes)
    -   **callback**: Function to execute if cache is empty
-   First visit: Runs query, stores in Redis
-   Next visits: Fetches from Redis (no database query!)

#### Step 4: Test It!

```powershell
# Enable query logging to see database queries
# Add to HomeController index() method temporarily:
DB::enableQueryLog();

# Visit homepage
# Then check queries:
dd(DB::getQueryLog());
```

**First Visit**: You'll see 5+ queries  
**Second Visit (refresh)**: You'll see 0 queries for products!

### Verification Exercise:

1. **See it in Redis:**

```powershell
# If using Docker:
docker exec -it redis-marketplace redis-cli

# In Redis CLI:
KEYS *homepage*
# Should show: homepage:latest_products, homepage:best_selling, etc.

GET homepage:latest_products
# Shows serialized product data
```

2. **Measure Performance:**

```powershell
# Install Laravel Debugbar
composer require barryvdh/laravel-debugbar --dev

# Visit homepage - check "Queries" tab
# First load: 5 queries
# Refresh: 0 queries!
```

3. **Clear Cache When Products Change:**

Create a method in your `ProductController` to clear cache after adding/updating products:

```php
// app/Http/Controllers/Admin/ProductController.php

public function store(Request $request)
{
    // ... your existing store logic ...

    // Clear homepage caches after creating product
    Cache::forget('homepage:latest_products');
    Cache::forget('homepage:best_selling');
    Cache::forget('homepage:discounts');
    Cache::forget('homepage:regular');
    Cache::forget('homepage:suggested');

    return redirect()->route('admin.products.index');
}
```

**Better approach** - Create a helper method:

```php
// Add to ProductController

private function clearHomepageCaches()
{
    $keys = [
        'homepage:latest_products',
        'homepage:best_selling',
        'homepage:discounts',
        'homepage:regular',
        'homepage:suggested'
    ];

    foreach ($keys as $key) {
        Cache::forget($key);
    }
}

// Then call it in store(), update(), destroy() methods:
$this->clearHomepageCaches();
```

### Learning Checkpoints:

-   [ ] Understand what `Cache::remember()` does
-   [ ] Know when to cache (static/rarely changing data)
-   [ ] Know when to invalidate cache (after data changes)
-   [ ] Can verify cache keys in Redis CLI
-   [ ] Measured performance improvement (queries before/after)

---

## Exercise 2: Session Management (Intermediate)

### Goal

Move user sessions from file storage to Redis for better performance and scalability.

### Feature: User Authentication & Guest Cart

#### Current Problem:

Your sessions are stored in `storage/framework/sessions/` as files:

-   **Guest Cart**: Stored in session files
-   **Guest Wishlist**: Stored in session files
-   **Authentication State**: Stored in session files

**Issues:**

1. Disk I/O is slow (especially with many concurrent users)
2. Can't scale horizontally (sessions tied to one server)
3. No automatic expiration (old sessions pile up)

#### Why Redis Sessions?

-   **Speed**: RAM vs Disk (100x faster)
-   **Scalability**: Multiple servers can share Redis
-   **Auto-cleanup**: TTL expires old sessions automatically
-   **Atomicity**: No file locking issues

### Step-by-Step Implementation:

#### Step 1: Update `.env`

```env
SESSION_DRIVER=redis
SESSION_LIFETIME=120  # Minutes
```

#### Step 2: Test Session Storage

Create a test route to verify sessions work:

```php
// routes/web.php

Route::get('/test-session', function () {
    // Set a session value
    session(['test_key' => 'Redis Session Works!']);

    return response()->json([
        'session_id' => session()->getId(),
        'test_value' => session('test_key'),
        'cart_count' => count(session('cart', [])),
    ]);
});
```

Visit: `http://127.0.0.1:8000/test-session`

#### Step 3: Verify in Redis

```bash
# In Redis CLI (docker exec -it redis-marketplace redis-cli)
KEYS *session*

# You'll see something like:
# laravel_session:abc123def456...

# View session data:
GET "laravel_session:abc123def456..."
```

#### Step 4: Test Guest Cart Performance

**Test Scenario**: Add 10 items to cart as guest user

```powershell
# Time with file sessions (change .env back temporarily):
SESSION_DRIVER=file

# Then time with Redis sessions:
SESSION_DRIVER=redis
```

Use browser DevTools Network tab to compare response times.

### Advanced Exercise: Session Analytics

Track how many active users you have:

```php
// Create a new file: app/Http/Middleware/TrackActiveUsers.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class TrackActiveUsers
{
    public function handle($request, Closure $next)
    {
        if ($request->user()) {
            // Track logged-in user
            Redis::setex(
                "active:user:{$request->user()->id}",
                300, // 5 minutes
                now()->toISOString()
            );
        } else {
            // Track guest by session ID
            Redis::setex(
                "active:guest:{$request->session()->getId()}",
                300,
                now()->toISOString()
            );
        }

        return $next($request);
    }
}
```

Register middleware in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ... existing middleware
        \App\Http\Middleware\TrackActiveUsers::class,
    ],
];
```

View active users count:

```php
// routes/web.php
Route::get('/active-users', function () {
    $activeUsers = count(Redis::keys('active:user:*'));
    $activeGuests = count(Redis::keys('active:guest:*'));

    return response()->json([
        'logged_in_users' => $activeUsers,
        'guest_users' => $activeGuests,
        'total_active' => $activeUsers + $activeGuests,
    ]);
});
```

### Learning Checkpoints:

-   [ ] Sessions now stored in Redis (verify with Redis CLI)
-   [ ] Understand session lifetime and TTL
-   [ ] Can track active users in real-time
-   [ ] Know the benefits of Redis sessions over file sessions

---

## Exercise 3: Queue Jobs (Intermediate)

### Goal

Use Redis queues to process background jobs asynchronously.

### Feature: Order Confirmation Emails

#### Current Problem:

When a user places an order in your checkout flow (`PaymentProcessController.php`), everything happens synchronously:

1. Create order
2. Process payment
3. Clear cart
4. Redirect to success page

**What's missing:** Email notifications happen later (or manually), slowing down the checkout process.

#### Why Queue Jobs?

-   **Faster response**: User doesn't wait for email to send
-   **Reliability**: Jobs retry automatically on failure
-   **Scalability**: Process jobs on separate worker processes

### Step-by-Step Implementation:

#### Step 1: Update `.env`

```env
QUEUE_CONNECTION=redis
```

#### Step 2: Create an Email Job

```powershell
php artisan make:job SendOrderConfirmationEmail
```

This creates `app/Jobs/SendOrderConfirmationEmail.php`:

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        // For learning: Just log for now
        // In production: Send actual email via Mail facade

        Log::info('Order Confirmation Email Sent', [
            'order_number' => $this->order->order_number,
            'customer_email' => $this->order->email,
            'total' => $this->order->total_amount,
            'attempts' => $this->attempts(),
        ]);

        // TODO: Implement actual email sending
        // Mail::to($this->order->email)->send(new OrderConfirmationMail($this->order));
    }

    public function failed(\Throwable $exception): void
    {
        // Handle job failure
        Log::error('Order email failed', [
            'order_number' => $this->order->order_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

#### Step 3: Dispatch Job After Order Creation

Open `app/Http/Controllers/PaymentProcessController.php` and find where orders are created.

Add after order creation (around line 250-280):

```php
use App\Jobs\SendOrderConfirmationEmail;

// After creating the order:
$order = Order::create([...]);

// Dispatch the job to Redis queue
SendOrderConfirmationEmail::dispatch($order);

// Or dispatch with delay (send after 5 minutes):
// SendOrderConfirmationEmail::dispatch($order)->delay(now()->addMinutes(5));
```

#### Step 4: Start the Queue Worker

```powershell
# Run in a separate terminal window
php artisan queue:work

# Or with verbose output:
php artisan queue:work --verbose

# Or listen for new jobs continuously:
php artisan queue:listen
```

**Keep this running!** This worker processes jobs from Redis.

#### Step 5: Test It!

1. Place a test order through your checkout
2. Watch the queue worker terminal
3. Check `storage/logs/laravel.log` for the log entry

You should see:

```
[YYYY-MM-DD HH:MM:SS] local.INFO: Order Confirmation Email Sent
{"order_number":"ORD-12345","customer_email":"test@example.com","total":999.99}
```

### Advanced: Multiple Queues

Create different queues for different priorities:

```php
// High priority: Payment processing
SendOrderConfirmationEmail::dispatch($order)->onQueue('high');

// Normal priority: Order notifications
SendShippingNotification::dispatch($order)->onQueue('default');

// Low priority: Analytics, reports
UpdateProductAnalytics::dispatch($product)->onQueue('low');
```

Run workers for each queue:

```powershell
# Terminal 1: High priority
php artisan queue:work --queue=high

# Terminal 2: Default
php artisan queue:work --queue=default

# Terminal 3: Low priority
php artisan queue:work --queue=low
```

### Monitoring Jobs in Redis

```bash
# In Redis CLI:

# See pending jobs
LLEN queues:default
LLEN queues:high
LLEN queues:low

# View job payload
LRANGE queues:default 0 -1
```

### Learning Checkpoints:

-   [ ] Created and dispatched a queue job
-   [ ] Queue worker processes jobs from Redis
-   [ ] Understand synchronous vs asynchronous processing
-   [ ] Can monitor queues in Redis CLI
-   [ ] Know how to handle job failures and retries

---

## Exercise 4: Advanced Caching Patterns

### Goal

Learn cache invalidation, tags, and cache-aside patterns.

### Feature: Category Products with Filters

#### Current Implementation:

`HomeController.php` category method (line 109) queries database every time:

```php
$products = DB::table('products')
    ->whereRaw('FIND_IN_SET(?, category_id)', [$category->id])
    ->paginate(10);
```

#### Problem:

-   Same query runs for every visitor
-   Popular categories get hammered
-   Filters (brand, search) multiply the load

### Pattern 1: Cache-Aside with Filters

```php
// app/Http/Controllers/HomeController.php - category method

public function category(Request $request, $slug)
{
    // ... existing code ...

    // Build cache key based on filters
    $brandFilter = $request->filled('brands') ? $request->input('brands') : 'all';
    $searchFilter = $request->filled('search') ? md5($request->search) : 'none';
    $page = $request->input('page', 1);

    $cacheKey = "category:{$category->id}:brands:{$brandFilter}:search:{$searchFilter}:page:{$page}";

    $products = Cache::remember($cacheKey, 300, function () use ($category, $brandIds, $request) {
        $query = DB::table('products')
            ->whereRaw('FIND_IN_SET(?, category_id)', [$category->id]);

        if (!empty($brandIds)) {
            $query->whereIn('brand_id', $brandIds);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return $query->paginate(10);
    });

    // ... rest of code ...
}
```

**What you learned:**

-   Dynamic cache keys based on user input
-   Hashing sensitive data (search terms)
-   Cache pagination results

### Pattern 2: Cache Tags (Group Invalidation)

Laravel supports cache tags with Redis:

```php
// Cache with tags
Cache::tags(['products', 'category:' . $categoryId])
    ->remember($cacheKey, 600, function () {
        return DB::table('products')->get();
    });

// Invalidate all product caches at once
Cache::tags(['products'])->flush();

// Invalidate specific category only
Cache::tags(['category:5'])->flush();
```

**Exercise:** Modify your ProductController to use tags:

```php
// app/Http/Controllers/Admin/ProductController.php

public function store(Request $request)
{
    // ... create product ...

    $product = Product::create($validated);

    // Flush all caches tagged with this category
    Cache::tags(['products', 'category:' . $product->category_id])->flush();

    return redirect()->route('admin.products.index');
}
```

### Pattern 3: Remember Forever (Until Explicit Clear)

For truly static data:

```php
// Cache until manually cleared
$brands = Cache::rememberForever('brands:all', function () {
    return DB::table('brands')->where('status', true)->get();
});

// Clear when brand is added/updated
// In BrandController:
Cache::forget('brands:all');
```

### Pattern 4: Cache Warming (Pre-populate)

Create an artisan command to warm caches:

```powershell
php artisan make:command WarmCache
```

```php
// app/Console/Commands/WarmCache.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WarmCache extends Command
{
    protected $signature = 'cache:warm';
    protected $description = 'Pre-populate important caches';

    public function handle()
    {
        $this->info('Warming caches...');

        // Homepage products
        Cache::remember('homepage:latest_products', 600, function () {
            return DB::table('products')->where('is_latest', true)->take(8)->get();
        });
        $this->info('✓ Homepage products cached');

        // Categories
        Cache::remember('nav_categories', 600, function () {
            return DB::table('categories')->where('status', true)->get();
        });
        $this->info('✓ Categories cached');

        // Popular categories
        $popularCategoryIds = [1, 2, 3, 5, 8]; // Your top categories
        foreach ($popularCategoryIds as $catId) {
            Cache::remember("category:{$catId}:products:page:1", 300, function () use ($catId) {
                return DB::table('products')
                    ->whereRaw('FIND_IN_SET(?, category_id)', [$catId])
                    ->paginate(10);
            });
        }
        $this->info('✓ Popular categories cached');

        $this->info('Cache warming complete!');
    }
}
```

Run after deployments:

```powershell
php artisan cache:warm
```

### Learning Checkpoints:

-   [ ] Built dynamic cache keys with parameters
-   [ ] Used cache tags for grouped invalidation
-   [ ] Implemented cache warming strategy
-   [ ] Understand trade-offs (stale data vs performance)

---

## Exercise 5: Rate Limiting & Locks (Advanced)

### Goal

Prevent abuse and race conditions using Redis.

### Pattern 1: API Rate Limiting

Prevent users from spamming search or checkout:

```php
// app/Http/Controllers/HomeController.php - search method

use Illuminate\Support\Facades\RateLimiter;

public function search(Request $request)
{
    $key = 'search:' . ($request->user() ? $request->user()->id : $request->ip());

    // Allow 10 searches per minute
    if (RateLimiter::tooManyAttempts($key, 10)) {
        $seconds = RateLimiter::availableIn($key);

        return response()->json([
            'error' => "Too many searches. Try again in {$seconds} seconds."
        ], 429);
    }

    RateLimiter::hit($key, 60); // 60 seconds window

    // ... your search logic ...
}
```

### Pattern 2: Distributed Locks (Prevent Double Payment)

**Problem:** User clicks "Pay" multiple times → multiple charges

```php
// app/Http/Controllers/PaymentProcessController.php

use Illuminate\Support\Facades\Cache;

public function process(Request $request)
{
    $lockKey = 'checkout:' . $request->user()->id;

    // Try to acquire lock for 10 seconds
    $lock = Cache::lock($lockKey, 10);

    if (!$lock->get()) {
        return redirect()->route('checkout.payment')
            ->with('error', 'Payment already in progress. Please wait.');
    }

    try {
        // Process payment safely
        $order = $this->createOrder($request);
        $payment = $this->processStripePayment($order);

        return redirect()->route('checkout.success');
    } finally {
        // Always release the lock
        $lock->release();
    }
}
```

### Pattern 3: Atomic Counters (Product Stock)

Prevent overselling:

```php
use Illuminate\Support\Facades\Redis;

// app/Http/Controllers/CartController.php

public function addToCart(Request $request)
{
    $productId = $request->product_id;
    $quantity = $request->quantity;

    // Atomic decrement in Redis
    $stockKey = "product:{$productId}:stock";

    // Initialize from database if not in Redis
    if (!Redis::exists($stockKey)) {
        $product = Product::find($productId);
        Redis::set($stockKey, $product->stock);
    }

    // Try to decrement
    $remaining = Redis::decrby($stockKey, $quantity);

    if ($remaining < 0) {
        // Rollback
        Redis::incrby($stockKey, $quantity);
        return response()->json(['error' => 'Not enough stock'], 400);
    }

    // Add to cart...

    // Sync back to database periodically (in a job)
}
```

### Learning Checkpoints:

-   [ ] Implemented rate limiting on an endpoint
-   [ ] Used distributed locks to prevent race conditions
-   [ ] Understand atomic operations in Redis
-   [ ] Know when to use locks vs queues vs transactions

---

## Monitoring & Debugging

### Tools & Commands

#### 1. Laravel Tinker (Interactive Redis)

```powershell
php artisan tinker
```

```php
// Get cache stats
Cache::get('homepage:latest_products');

// Set a value
Cache::put('test', 'value', 600);

// Check if key exists
Cache::has('homepage:latest_products');

// Raw Redis commands
Redis::info();
Redis::dbsize();
Redis::keys('*');
```

#### 2. Redis CLI Commands

```bash
# Connect (Docker)
docker exec -it redis-marketplace redis-cli

# Or native
redis-cli

# Essential commands:
KEYS *               # List all keys (DON'T use in production!)
KEYS homepage:*      # List keys matching pattern
GET key              # Get value
TTL key              # Time to live
DEL key              # Delete key
FLUSHDB              # Clear current database
INFO                 # Server stats
MONITOR              # Watch all commands in real-time
```

#### 3. Laravel Telescope (Advanced Monitoring)

```powershell
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Visit: `http://127.0.0.1:8000/telescope`

See:

-   Cache hits/misses
-   Queue jobs
-   Redis commands
-   Performance metrics

### Performance Benchmarking

Create a benchmark route:

```php
// routes/web.php
Route::get('/benchmark', function () {
    $iterations = 100;

    // Database
    $dbStart = microtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        DB::table('products')->where('is_latest', true)->take(8)->get();
    }
    $dbTime = microtime(true) - $dbStart;

    // Redis Cache
    Cache::remember('benchmark:products', 600, function () {
        return DB::table('products')->where('is_latest', true)->take(8)->get();
    });

    $cacheStart = microtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        Cache::get('benchmark:products');
    }
    $cacheTime = microtime(true) - $cacheStart;

    return response()->json([
        'iterations' => $iterations,
        'database_time' => round($dbTime, 4) . 's',
        'redis_cache_time' => round($cacheTime, 4) . 's',
        'speedup' => round($dbTime / $cacheTime, 2) . 'x faster',
    ]);
});
```

---

## Common Issues & Solutions

### Issue 1: "Connection refused" Error

**Symptom:**

```
Connection refused [tcp://127.0.0.1:6379]
```

**Solutions:**

```powershell
# Check if Redis is running
docker ps  # Should show redis-marketplace

# Start Redis if stopped
docker start redis-marketplace

# Or in WSL:
sudo service redis-server status
sudo service redis-server start
```

### Issue 2: Cache Not Clearing

**Symptom:** Updated products but homepage shows old data

**Solutions:**

```powershell
# Clear all caches
php artisan cache:clear
php artisan config:clear

# Or in code:
Cache::flush();

# Or specific key:
Cache::forget('homepage:latest_products');
```

### Issue 3: Queue Jobs Not Processing

**Symptom:** Jobs dispatched but not running

**Solutions:**

```powershell
# Make sure queue worker is running
php artisan queue:work

# Check failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry <job-id>

# Clear all jobs (careful!)
php artisan queue:flush
```

### Issue 4: Session Data Lost

**Symptom:** Cart cleared after changing SESSION_DRIVER

**Solution:**
Sessions are NOT automatically migrated. You must:

1. Change driver in `.env`
2. Clear old sessions: `php artisan session:table` then truncate table
3. Or keep guest cart in database table instead of sessions

### Issue 5: Memory Usage High

**Symptom:** Redis using too much RAM

**Solutions:**

```bash
# In Redis CLI:
INFO memory

# Set max memory (e.g., 256MB)
CONFIG SET maxmemory 256mb
CONFIG SET maxmemory-policy allkeys-lru  # Evict least recently used

# Or in Redis config file:
# maxmemory 256mb
# maxmemory-policy allkeys-lru
```

---

## Next Steps & Production Checklist

### What You've Learned:

✅ Redis basics and use cases  
✅ Caching database queries  
✅ Session management in Redis  
✅ Background job queues  
✅ Rate limiting and locks  
✅ Monitoring and debugging

### Before Going to Production:

1. **Security:**
    ```env
    REDIS_PASSWORD=your-secure-password
    ```
2. **Persistence:**
   Enable RDB or AOF in `redis.conf` to survive restarts

3. **Monitoring:**

    - Install Laravel Horizon: `composer require laravel/horizon`
    - Set up Redis monitoring (RedisInsight, Prometheus)

4. **Backup Strategy:**

    ```bash
    # Automatic backups in redis.conf
    save 900 1     # Save after 900s if 1 key changed
    save 300 10    # Save after 300s if 10 keys changed
    ```

5. **Queue Workers:**
   Use Supervisor to keep workers running:

    ```ini
    [program:marketplace-queue]
    command=php /path/to/artisan queue:work --tries=3
    autostart=true
    autorestart=true
    ```

6. **Cache Strategy Documentation:**
   Document what's cached, TTLs, and invalidation triggers

---

## Additional Resources

-   **Laravel Docs**: https://laravel.com/docs/cache
-   **Redis Commands**: https://redis.io/commands
-   **Laravel Horizon**: https://laravel.com/docs/horizon
-   **Redis Best Practices**: https://redis.io/docs/manual/patterns/

---

## Practice Exercises Summary

| Exercise              | Difficulty   | Time      | Feature           |
| --------------------- | ------------ | --------- | ----------------- |
| 1. Database Caching   | Beginner     | 30 min    | Homepage products |
| 2. Session Management | Intermediate | 45 min    | Guest cart/auth   |
| 3. Queue Jobs         | Intermediate | 1 hour    | Order emails      |
| 4. Advanced Caching   | Advanced     | 1.5 hours | Category filters  |
| 5. Locks & Limits     | Advanced     | 1 hour    | Payment safety    |

**Total Learning Time:** ~5 hours of hands-on practice

---

## Your Action Plan

### Week 1: Foundations

-   [ ] Install Redis (Day 1)
-   [ ] Exercise 1: Cache homepage (Day 2-3)
-   [ ] Exercise 2: Redis sessions (Day 4-5)

### Week 2: Advanced

-   [ ] Exercise 3: Queue jobs (Day 1-2)
-   [ ] Exercise 4: Cache patterns (Day 3-4)
-   [ ] Exercise 5: Locks & limits (Day 5)

### Week 3: Production Ready

-   [ ] Add monitoring (Telescope/Horizon)
-   [ ] Write tests for cache logic
-   [ ] Document your implementation
-   [ ] Deploy to staging environment

**Good luck with your Redis learning journey!** 🚀

Remember: Start small, test thoroughly, and gradually add Redis to more features as you gain confidence.
