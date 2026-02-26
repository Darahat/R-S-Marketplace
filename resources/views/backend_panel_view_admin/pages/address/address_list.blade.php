@extends('backend_panel_view_admin.layouts.admin')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0"><i class="fas fa-map-marker-alt"></i> User Address List</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
						<li class="breadcrumb-item active">User Address List</li>
					</ol>
				</div>
			</div>
		</div>
	</div>

	<section class="content">
		<div class="container-fluid">
			@if(session('success'))
				<div class="alert alert-success alert-dismissible fade show" role="alert">
					<i class="fas fa-check-circle"></i> {{ session('success') }}
					<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
				</div>
			@endif

			@if(session('error'))
				<div class="alert alert-danger alert-dismissible fade show" role="alert">
					<i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
					<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
				</div>
			@endif

			<div class="card shadow-sm">
				<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
					<h3 class="card-title mb-0"><i class="fas fa-list"></i> All Addresses</h3>
					<span class="badge bg-light text-dark">Total: {{ $addresses->total() }}</span>
				</div>

				<div class="card-body table-responsive p-0">
					<table class="table table-hover table-striped align-middle mb-0">
						<thead class="bg-light">
							<tr>
								<th>ID</th>
								<th>User</th>
								<th>Type</th>
								<th>Full Name</th>
								<th>Phone</th>
								<th>Email</th>
								<th>Location</th>
								<th>Street</th>
								<th>Postal</th>
								<th>Country</th>
								<th>Default</th>
								<th>Created</th>
								<th class="text-center">Actions</th>
							</tr>
						</thead>
						<tbody>
							@forelse($addresses as $address)
								<tr>
									<td>{{ $address->id }}</td>
									<td>
										<strong>{{ $address->user->name ?? 'N/A' }}</strong><br>
										<small class="text-muted">ID: {{ $address->user_id }}</small>
									</td>
									<td>
										<span class="badge {{ $address->address_type === 'shipping' ? 'bg-info' : 'bg-secondary' }}">
											{{ ucfirst($address->address_type) }}
										</span>
									</td>
									<td>{{ $address->full_name ?? '-' }}</td>
									<td>{{ $address->phone ?? '-' }}</td>
									<td>{{ $address->email ?? '-' }}</td>
									<td>
										{{ $address->district->name ?? '-' }}
										@if($address->upazila)
											, {{ $address->upazila->name }}
										@endif
										@if($address->union)
											, {{ $address->union->name }}
										@endif
									</td>
									<td>{{ $address->street_address ?? '-' }}</td>
									<td>{{ $address->postal_code ?? '-' }}</td>
									<td>{{ $address->country ?? '-' }}</td>
									<td>
										@if($address->is_default)
											<span class="badge bg-success">Yes</span>
										@else
											<span class="badge bg-light text-dark">No</span>
										@endif
									</td>
									<td>{{ optional($address->created_at)->format('M d, Y h:i A') }}</td>
									<td class="text-center">
										<div class="btn-group btn-group-sm" role="group">
											<a href="{{ url('/admin/addresses/'.$address->id.'/edit') }}" class="btn btn-info" title="Edit">
												<i class="fas fa-edit"></i>
											</a>
											<form action="{{ url('/admin/addresses/'.$address->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this address?');">
												@csrf
												@method('DELETE')
												<button type="submit" class="btn btn-danger" title="Delete">
													<i class="fas fa-trash"></i>
												</button>
											</form>
										</div>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="13" class="text-center text-muted py-4">
										<i class="fas fa-inbox fa-2x mb-2"></i><br>
										No addresses found.
									</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				@if($addresses->hasPages())
					<div class="card-footer d-flex justify-content-between align-items-center">
						<div class="text-muted small">
							Showing {{ $addresses->firstItem() }} to {{ $addresses->lastItem() }} of {{ $addresses->total() }} entries
						</div>
						<div>
							{{ $addresses->links() }}
						</div>
					</div>
				@endif
			</div>
		</div>
	</section>
</div>

@endsection
