@extends('backend_panel_view_admin.layouts.admin')
@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user"></i> {{ $page_header }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                        <li class="breadcrumb-item active">{{ $user->name }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->has('message'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $errors->first('message') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-id-card"></i> User Information</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>ID:</strong> {{ $user->id }}</p>
                            <p><strong>Name:</strong> {{ $user->name }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <p><strong>Mobile:</strong> {{ $user->mobile ?? '-' }}</p>
                            <p><strong>Current Role:</strong> <span class="badge {{ $user->user_type === 'ADMIN' ? 'badge-danger' : 'badge-info' }}">{{ $user->user_type ?? 'CUSTOMER' }}</span></p>
                            <p><strong>Joined:</strong> {{ optional($user->created_at)->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning">
                            <h3 class="card-title mb-0"><i class="fas fa-user-shield"></i> Update Role</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.users.update-role', $user->id) }}">
                                @csrf
                                <div class="form-group">
                                    <label for="user_type">User Role</label>
                                    <select class="form-control @error('user_type') is-invalid @enderror" id="user_type" name="user_type" required>
                                        @foreach($roles as $role)
                                            <option value="{{ $role }}" {{ $user->user_type === $role ? 'selected' : '' }}>{{ $role }}</option>
                                        @endforeach
                                    </select>
                                    @error('user_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Role
                                </button>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
