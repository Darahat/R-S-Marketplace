<nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Dashboard -->
          <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <li class="nav-item">
            <div style="height: 1px; background: rgba(255,255,255,0.1); margin: 10px 0;"></div>
          </li>

          <!-- Product Management -->
          <li class="nav-item has-treeview {{ Request::routeIs('admin.products.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ Request::routeIs('admin.products.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-boxes"></i>
              <p>
                Products
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('admin.products.index') }}" class="nav-link {{ Request::routeIs('admin.products.index') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>All Products</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.products.create') }}" class="nav-link {{ Request::routeIs('admin.products.create') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Add New Product</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- Brand Management -->
          <li class="nav-item has-treeview {{ Request::routeIs('admin.brands.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ Request::routeIs('admin.brands.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tag"></i>
              <p>
                Brands
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('admin.brands.index') }}" class="nav-link {{ Request::routeIs('admin.brands.index') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>All Brands</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.brands.create') }}" class="nav-link {{ Request::routeIs('admin.brands.create') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Add New Brand</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- Category Management -->
          <li class="nav-item has-treeview {{ Request::routeIs('admin.categories.*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ Request::routeIs('admin.categories.*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-sitemap"></i>
              <p>
                Categories
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('admin.categories.index') }}" class="nav-link {{ Request::routeIs('admin.categories.index') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>All Categories</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ route('admin.categories.create') }}" class="nav-link {{ Request::routeIs('admin.categories.create') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Add New Category</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <div style="height: 1px; background: rgba(255,255,255,0.1); margin: 10px 0;"></div>
          </li>

          <!-- Order Management -->
          <li class="nav-item has-treeview {{ Request::routeIs('admin.orders*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ Request::routeIs('admin.orders*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-shopping-cart"></i>
              <p>
                Orders
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('admin.orders') }}" class="nav-link {{ Request::routeIs('admin.orders') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>All Orders</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- Payment Management -->
          <li class="nav-item has-treeview {{ Request::routeIs('admin.payments*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ Request::routeIs('admin.payments*') ? 'active' : '' }}">
              <i class="nav-icon fas fa-credit-card"></i>
              <p>
                Payments
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ route('admin.payments') }}" class="nav-link {{ Request::routeIs('admin.payments') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>All Payments</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <div style="height: 1px; background: rgba(255,255,255,0.1); margin: 10px 0;"></div>
          </li>





          <li class="nav-item">
            <a href="{{ route('logout')}}" class="nav-link">
            <i class="nav-icon fas fa-sign-out-alt nav-icon"></i>
              <p>
                Logout
              </p>
            </a>
          </li>

        </ul>
      </nav>
      <!-- /.sidebar-menu -->
