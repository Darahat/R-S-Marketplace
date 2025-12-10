@extends('backend_panel_view.layouts.admin')
@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">{{ $page_header }}</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ $page_header }}</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-users"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text"><a href="#" class="link-primary link-offset-2">Total Users</a></span>
                        <span class="info-box-number">
                        
                        <small></small>
                        </span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
          
            
           
           
           
            <!-- <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-wifi"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text"><a href="" class="link-primary link-offset-2">Total Offline</a></span>
                        <span class="info-box-number">
                        
                        <small></small>
                        </span>
                    </div>
                </div>
            </div> -->
            
        </div>
 



</section>

@endsection