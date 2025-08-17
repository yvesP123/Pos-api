@extends('layouts.app')

@section('title', 'Extend License')

@section('breadcrumb')
@parent
<li><a href="{{ route('license-management.index') }}">License Management</a></li>
<li><a href="{{ route('license-management.keys') }}">View Keys</a></li>
<li class="active">Extend License</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Extend License Key</h3>
            </div>
            <form action="{{ route('license-management.extend', $licenseKey->id) }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="alert alert-info">
                        <h4><i class="icon fa fa-info"></i> Current License Information</h4>
                        <strong>Company ID:</strong> {{ $licenseKey->company_id }}<br>
                        <strong>Current Expiry:</strong> {{ $licenseKey->expiry_date->format('d/m/Y') }}<br>
                        <strong>Current Duration:</strong> {{ $licenseKey->duration_days }} days<br>
                        <strong>Days Remaining:</strong> 
                        @if($licenseKey->daysRemaining() > 0)
                            <span class="text-success">{{ $licenseKey->daysRemaining() }} days</span>
                        @else
                            <span class="text-danger">Expired {{ abs($licenseKey->daysRemaining()) }} days ago</span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="extend_days">Extend by (Days) *</label>
                        <input type="number" name="extend_days" id="extend_days" class="form-control" 
                               value="{{ old('extend_days', 365) }}" min="1" required>
                        @error('extend_days')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">
                            Number of days to extend the license. New expiry date will be calculated automatically.
                        </small>
                    </div>

                    <div class="alert alert-warning">
                        <h4><i class="icon fa fa-warning"></i> Important Notes:</h4>
                        <ul>
                            <li>This will generate a new license key with extended expiry date</li>
                            <li>The old key will be updated with the new information</li>
                            <li>Extension will be added to the current expiry date</li>
                        </ul>
                    </div>
                </div>
                
                <div class="box-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-calendar-plus-o"></i> Extend License
                    </button>
                    <a href="{{ route('license-management.show', $licenseKey->id) }}" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
```('layouts.app')

@section('title', 'License Management Dashboard')

@section('breadcrumb')
@parent
<li class="active">License Management</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">License Key Management Portal</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-aqua"><i class="fa fa-key"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Keys</span>
                                <span class="info-box-number">{{ \App\Models\LicenseKey::count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Active Keys</span>
                                <span class="info-box-number">{{ \App\Models\LicenseKey::active()->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-red"><i class="fa fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Expired Keys</span>
                                <span class="info-box-number">{{ \App\Models\LicenseKey::expired()->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-yellow"><i class="fa fa-exclamation"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Expiring Soon</span>
                                <span class="info-box-number">{{ \App\Models\LicenseKey::where('expiry_date', '<=', now()->addDays(30))->where('expiry_date', '>', now())->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="btn-group btn-group-justified" role="group">
                            <a href="{{ route('license-management.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Generate New Key
                            </a>
                            <a href="{{ route('license-management.keys') }}" class="btn btn-info">
                                <i class="fa fa-list"></i> View All Keys
                            </a>
                            <a href="{{ route('license-management.search') }}" class="btn btn-warning">
                                <i class="fa fa-search"></i> Search Keys
                            </a>
                            <a href="{{ route('license-management.export') }}" class="btn btn-success">
                                <i class="fa fa-download"></i> Export CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection