@extends('backend_panel_view.layouts.admin')
@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-image"></i> {{ $page_header }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Hero Section</li>
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

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0"><i class="fas fa-edit"></i> Update Hero Content</h3>
                </div>
                <form method="POST" action="{{ route('admin.hero.update') }}">
                    @csrf
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Headline</label>
                            <input type="text" name="headline" class="form-control" value="{{ old('headline', $hero['headline']) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Highlight Text</label>
                            <input type="text" name="highlight" class="form-control" value="{{ old('highlight', $hero['highlight']) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subheadline</label>
                            <textarea name="subheadline" rows="3" class="form-control" required>{{ old('subheadline', $hero['subheadline']) }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Primary Button Text</label>
                                <input type="text" name="primary_text" class="form-control" value="{{ old('primary_text', $hero['primary_text']) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Primary Button URL</label>
                                <input type="text" name="primary_url" class="form-control" value="{{ old('primary_url', $hero['primary_url']) }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Secondary Button Text</label>
                                <input type="text" name="secondary_text" class="form-control" value="{{ old('secondary_text', $hero['secondary_text']) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Secondary Button URL</label>
                                <input type="text" name="secondary_url" class="form-control" value="{{ old('secondary_url', $hero['secondary_url']) }}">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
