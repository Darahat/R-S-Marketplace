<nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->

          <li class="nav-item">
            <a href="{{ url('/admin/dashboard')}}" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
              </p>
            </a>
          </li>

          <!-- Product Management with Submenu -->
            <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-boxes"></i>
                    <p>
                        Product Management
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">

                    <!-- Add Product -->
                    <!-- <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Add Product</p>
                        </a>
                    </li> -->

                    <!-- View Products -->
                    <li class="nav-item">
                        <a href="{{ route('admin.viewproduct') }}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>View Products</p>
                        </a>
                    </li>
                </ul>
            </li>


            <!-- Product Setting Management -->
            <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-boxes"></i>
                    <p>
                        Product Setting
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">

                    <!-- Add Product -->
                    <!-- <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Add Product</p>
                        </a>
                    </li> -->

                    <!-- View Products -->
                    <li class="nav-item">
                        <a href="{{ route('admin.viewBrand') }}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Add Brand</p>
                        </a>
                    </li>
                </ul>
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
