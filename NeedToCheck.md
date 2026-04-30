Since you've seen that jQuery can "break" Alpine when it touches the same HTML, let's
clean up the rest of home.blade.php. There are three main "Clean Up" tasks to make
your site 100% Alpine-friendly.

1. Delete the "Manual Builders"
   Function like upsertNavCartDropdownItem and setNavCartCount are doing work that Alpine
   is already doing now.

The Problem: When you add a product, jQuery manually changes the number on the badge.
Then Alpine refreshes and changes it again. This "Double Update" is what makes the
site feel jumpy.

The Fix:

- Remove the upsertNavCartDropdownItem function entirely.
- In your "Add to Cart" success handler, remove the setNavCartCount call. Alpine's
  refreshCart() will handle it.

---

2. Move Wishlist to Alpine
   Your Wishlist toggle is currently a long block of jQuery that manually changes SVG
   colors and fills.

The Teacher's Plan:

1. Add wishlistCount to your navbar data in navigation_bar.blade.php.
2. In home.blade.php, change the wishlist button to use Alpine state.
    - Instead of jQuery svg.addClass('text-danger'), use:
      :class="isWishlisted ? 'text-danger' : 'text-gray-400'"

---

3. Handle "Loading" States with Alpine
   You have setButtonLoading and setIconLoading which manually change button HTML.

The Alpine Way:
Add a loadingProducts: [] array to your Alpine state. When a user clicks "Add to Cart"
for Product #5:

1. Push 5 into the array.
2. Use Alpine to show the spinner: <i x-show="loadingProducts.includes(product.id)"
   class="fas fa-spinner fa-spin"></i>.

---

Step-by-Step Replacement:

Part A: The "Add to Cart" Cleanup
In your $(document).on('submit', '.add-to-cart-form', ...) block:

Change the Success Handler:

1 success: function (response) {
2 toastr.success('Product added to cart!');
3  
 4 // JUST CALL ALPINE REFRESH - That's it!
5 const nav = document.querySelector('[x-data="navbar()"]');
6 if (nav && nav.**x) {
7 nav.**x.$data.refreshCart();
8 }
9 },

Part B: The Badge Numbers
In account-buttons.blade.php, replace the hardcoded IDs with Alpine x-text:

Instead of:
<span id="nav-cart-count">...</span>

Use:
<span x-text="totalItemCount">...</span>

---

Teacher's Question:
By removing these jQuery functions, you are deleting almost 100 lines of code.

Does it feel "safer" to have one single place (the Alpine state) controlling your
numbers, or do you prefer having multiple separate scripts (jQuery and Alpine) trying
to keep everything in sync?

Shall we start by deleting those manual count/dropdown builders? It will make your
code much easier to read!
