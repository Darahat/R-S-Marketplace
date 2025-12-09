<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ $page_title }}</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
 <!-- Google Font: Source Sans Pro -->
 <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
  <!-- Font Awesome (AdminLTE includes this by default) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Toastr -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
  <!-- DataTables -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
  <!-- Select2 -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
  <!-- iCheck -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <!-- JQVMap -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/jqvmap/jqvmap.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
  <!-- summernote -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/summernote/summernote-bs4.css') }}">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css') }}">
  <!-- jQuery -->
  <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/js/smoothie.js') }}"></script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

  <!-- jQuery UI JS -->
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
  <style>
    #overlay {
        position: fixed;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        z-index: 100000;
        background-color: rgba(0, 0, 0, 0.5);
        font-size: 18px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #overlay-content {
        text-align: center;
        color: red !important;
        padding: 20px;
        background-color: rgba(0, 0, 0, 0.8);
        border-radius: 5px;
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed text-sm" onload="createTimeline()">
<div class="wrapper">

    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="{{url('/')}}/images/logo/logo.png" alt="R&SMarketPlace Logo" height="60" width="60">
    </div>

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light ">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">

        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="true">

              <i class="fas fa-user-circle" style="font-size:1.8rem;"></i>

          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="left: inherit; right: 0px;">
            <a href="{{ url('admin/profile') }}" class="dropdown-item"><i class="fas fa-id-card mr-2"></i> Profile</a>
            <div class="dropdown-divider"></div>
            <a href="{{ url('admin/change_password') }}" class="dropdown-item"><i class="fas fa-key mr-2"></i> Change Password</a>
            <div class="dropdown-divider"></div>

            <a href="{{ route('logout') }}" class="dropdown-item"  role="button"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
          </div>
        </li>
      </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{url('/')}}" target="_blank" class="brand-link bg-purple text-center">
      <!-- <img src="{{ asset('assets/dist/img/AdminLTELogo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8"> -->
      <span class="brand-text font-weight-bold"><h4>{{ ucwords(strtolower(Auth::user()->user_type)) }} Panel</h4></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar bg-purple">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel  d-flex">
        <!--<div class="image">
          <img src="{{ asset('assets/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
        </div>-->
        <div class="info text-light">
          Logged in [<a href="#" class="text-warning">{{ Auth::user()->name }}</a>]
        </div>
      </div>
        <!-- Navigation -->
        @include('backend_panel_view.components.shared.navigation_bar')
      <!-- Sidebar Menu -->

    </div>
    <!-- /.sidebar -->
  </aside>


        @yield('content')


  </div>
  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <strong>&copy; 2017-{{ date('Y')}}. Developed by- <a href="https://fb.com/sohelcsm">Md. Sohel Rana</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.0
    </div>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->


<!-- jQuery UI 1.11.4 -->
<script src="{{ asset('assets/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- Select2 -->
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<!-- DataTables  & Plugins -->
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

<!-- ChartJS -->
<script src="{{ asset('assets/plugins/chart.js/Chart.min.js') }}"></script>

<!-- JQVMap -->
<script src="{{ asset('assets/plugins/jqvmap/jquery.vmap.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
<!-- jQuery Knob Chart -->
<script src="{{ asset('assets/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
<!-- daterangepicker -->
<script src="{{ asset('assets/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{ asset('assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<!-- Summernote -->
<script src="{{ asset('assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
<!-- overlayScrollbars -->
<script src="{{ asset('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<!-- Toastr -->
<script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('assets/dist/js/adminlte.js') }}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{ asset('assets/dist/js/demo.js') }}"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="{{ asset('assets/dist/js/pages/dashboard.js') }}"></script>

<script>

  @if(Session::has('status'))
    var type="{{Session::get('alert-type','info')}}";
    switch(type){
        case 'info' :
            toastr.info("{{ Session::get('status') }}");
            break;
        case 'success' :
            toastr.success("{{ Session::get('status') }}");
            break;
        case 'warning' :
            toastr.warning("{{ Session::get('status') }}");
            break;
        case 'error' :
            toastr.error("{{ Session::get('status') }}");
            break;
    }
    @endif

  $(document).ready(function () {
    $('.delete-confirm').on('click', function (event) {
        event.preventDefault();
        const url = $(this).attr('href');
        swal({
            title: 'Are you sure?',
            text: 'This record will be permanantly deleted!',
            icon: 'warning',
            buttons: ["Cancel", "Yes!"],
            dangerMode: true,
        }).then(function(value) {
            if (value) {
                window.location.href = url;
            }
        });
    });

	$('.return-confirm').on('click', function (event) {
        event.preventDefault();
        const url = $(this).attr('href');
        swal({
            title: 'Are you sure?',
            text: 'Product return to root!',
            icon: 'warning',
            buttons: ["Cancel", "Yes!"],
        }).then(function(value) {
            if (value) {
                window.location.href = url;
            }
        });
    });
	$('.damage-confirm').on('click', function (event) {
        event.preventDefault();
        const url = $(this).attr('href');
        swal({
            title: 'Are you sure?',
            text: 'Product is damaged!',
            icon: 'warning',
            buttons: ["Cancel", "Yes!"],
        }).then(function(value) {
            if (value) {
                window.location.href = url;
            }
        });
    });
  });
</script>


<script>
  $(function () {
    // Summernote
    $('#summernote').summernote({
        placeholder: 'Enter your text here',
        tabsize: 2,
        height: 300
      });

	  //Initialize Select2 Elements
    $('.select2').select2();

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    });


		$('[data-toggle="tooltip"]').tooltip()


  });
</script>
<script>
  $(function () {
    $("#example1").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

    $("#onlybutton").DataTable({
      "paging": false,
      "lengthChange": true,
        "searching": true,
        "ordering": false,
        "info": true,
      dom: "B",
        "responsive": true, "lengthChange": false, "autoWidth": true,
    "buttons": [
      {extend: "copy",title: "{{$page_header.'_'.date('d_m_Y',time())}}"},
      {extend:  "csv",title: "{{$page_header.'_'.date('d_m_Y',time())}}"},
      {extend: "excel",title: "{{$page_header.'_'.date('d_m_Y',time())}}"},
      {extend: "pdf",title: "{{$page_header.'_'.date('d_m_Y',time())}}",orientation: 'landscape',pageSize: 'A4'},
      {extend: "print",title: "{{$page_header.'_'.date('d_m_Y',time())}}",orientation: 'landscape',pageSize: 'A4'},
      "colvis"
      ]
      }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

      $('#datatable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": false,
        "info": true,
        // "autoWidth": false,
        // "responsive": true,
      });

      $('#datatable2').DataTable({
          "paging": false,
          "filter": true,
          "lengthChange": true,
          "searching": true,
          "ordering": false,
          "info": false,
          dom: "Blfrtip",
          "responsive": true,
          "autoWidth": true,
          "buttons": [
            {extend: "copy",title: "{{$page_header.'_'.date('d_m_Y',time())}}"},
            {extend:  "csv",title: "{{$page_header.'_'.date('d_m_Y',time())}}"},
            {extend: "excel",title: "{{$page_header.'_'.date('d_m_Y',time())}}"},
            {extend: "pdf",title: "{{$page_header.'_'.date('d_m_Y',time())}}",orientation: 'landscape',pageSize: 'A4'},
            {extend: "print",title: "{{$page_header.'_'.date('d_m_Y',time())}}",orientation: 'landscape',pageSize: 'A4'},
            "colvis"
          ]
        }).buttons().container().appendTo('#wrapper .col-md-6:eq(0)');

        $('#dataTablesButton').DataTable({
            dom: 'lBfrtip',
            bSort: false,
            buttons: [
                {
                    extend: 'copy',
                    text: '<i class="fas fa-copy"></i>',
                    titleAttr: 'Copy',
                    title: 'ACN_' + '{{ $page_header }}_' + '{{ date("d_m_Y") }}',
                    className: 'btn btn-md mr-2 btn-copy',
                    exportOptions: {
                        columns: ':not(:last-child)',
                    },
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i>',
                    titleAttr: 'Excel',
                    title: 'ACN_' + '{{ $page_header }}_' + '{{ date("d_m_Y") }}',
                    className: 'btn btn-md mr-2 btn-excel',
                    exportOptions: {
                        columns: ':not(:last-child)',
                    },
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i>',
                    titleAttr: 'PDF',
                    title: 'ACN_' + '{{ $page_header }}_' + '{{ date("d_m_Y") }}',
                    className: 'btn btn-md mr-2 btn-pdf',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':not(:last-child)',
                    },
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i>',
                    titleAttr: 'Print',
                    title: 'ACN_' + '{{ $page_header }}_' + '{{ date("d_m_Y") }}',
                    className: 'btn btn-md mr-2 btn-print',
                    exportOptions: {
                        columns: ':not(:last-child)',
                    },
                },
            ]
        })
        .buttons()
        .container()
        .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)");



    });

</script>
<script>
var url = window.location;

// for sidebar menu entirely but not cover treeview
$('ul.nav-sidebar a').filter(function() {
    return this.href == url;
}).addClass('active');

// for treeview
$('ul.nav-treeview a').filter(function() {
    return this.href == url;
}).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open') .prev('a').addClass('active');

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>
</body>
</html>
