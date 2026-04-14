@extends('backend_panel_view_admin.layouts.admin')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-shield-alt"></i> {{ $page_header }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Login Audits</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.login-audits') }}" class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Email / IP / Reason" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="login_type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                <option value="customer" {{ request('login_type') === 'customer' ? 'selected' : '' }}>Customer</option>
                                <option value="admin" {{ request('login_type') === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-1 d-grid">
                            <button type="submit" class="btn btn-primary btn-sm">Go</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0"><i class="fas fa-list"></i> Login Attempts ({{ $audits->total() }})</h3>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>IP</th>
                                <th>Device</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($audits as $audit)
                                <tr>
                                    <td>{{ $audit->email ?? 'N/A' }}</td>
                                    <td><span class="badge bg-secondary">{{ strtoupper($audit->login_type) }}</span></td>
                                    <td>
                                        <span class="badge {{ $audit->status === 'success' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($audit->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $audit->reason ?? '-' }}</td>
                                    <td>{{ $audit->ip ?? '-' }}</td>
                                    <td>{{ $audit->device ?? '-' }}</td>
                                    <td>
                                        <small>{{ optional($audit->attempted_at)->format('M d, Y') }}</small><br>
                                        <small class="text-muted">{{ optional($audit->attempted_at)->format('h:i A') }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p class="mb-0">No login attempts found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($audits->hasPages())
                    <div class="card-footer d-flex justify-content-end">
                        {{ $audits->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
