What is still not fully proven:

**\*\***There are no real feature tests covering full user journey (register/login/browse/cart/checkout/order tracking) in tests. Current feature tests are mostly brand/admin related: BrandTest.php:63
Risk remains in cart add flow if invalid product id is posted (null product access): CartService.php:63, CartService.php:78
Empty-cart guard in checkout service is commented out, so behavior depends on UI flow: CheckoutService.php:45, CheckoutService.php:119
