@extends('layouts.app')

@section('title', 'License Management Dashboard')

@section('breadcrumb')
@parent
<li class="active">License Management</li>
@endsection

@section('content')
<div class="row">
    <!-- Current License Status -->
    <div class="col-lg-12">
        <div class="box box-{{ $currentLicense ? ($currentLicense->isExpired() ? 'danger' : ($currentLicense->daysRemaining() <= 7 ? 'warning' : 'success')) : 'danger' }}">
            <div class="box-header with-border">
                <h3 class="box-title">Current System License Status</h3>
                <div class="box-tools pull-right">
                    <a href="{{ route('license-management.manual-renewal') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-key"></i> Manual Renewal
                    </a>
                    @if($currentLicense)
                    <a href="{{ route('license-management.current-license') }}" class="btn btn-info btn-sm">
                        <i class="fa fa-eye"></i> View Details
                    </a>
                    @endif
                </div>
            </div>
            <div class="box-body">
                @if($currentLicense)
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Company ID:</strong><br>
                            {{ $currentLicense->company_id }}
                        </div>
                        <div class="col-md-3">
                            <strong>Renewal Date:</strong><br>
                            {{ $currentLicense->renewal_date ? \Carbon\Carbon::parse($currentLicense->renewal_date)->format('d/m/Y') : 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Expiry Date:</strong><br>
                            {{ \Carbon\Carbon::parse($currentLicense->expiry_date)->format('d/m/Y') }}
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong><br>
                            @if($currentLicense->isExpired())
                                <span class="label label-danger">
                                    @if($currentLicense->isInGracePeriod())
                                        Grace Period ({{ abs($currentLicense->daysRemaining()) }} days over)
                                    @else
                                        Expired ({{ abs($currentLicense->daysRemaining()) }} days ago)
                                    @endif
                                </span>
                            @else
                                <span class="label label-{{ $currentLicense->daysRemaining() <= 7 ? 'warning' : 'success' }}">
                                    Active ({{ $currentLicense->daysRemaining() }} days left)
                                </span>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>No License Found!</strong> Please install a license key to activate the system.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Statistics -->
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-key"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Generated Keys</span>
                <span class="info-box-number">{{ $generatedKeys }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Active Keys</span>
                <span class="info-box-number">{{ $activeKeys }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-times"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Expired Keys</span>
                <span class="info-box-number">{{ $expiredKeys }}</span>
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

<div class="row">
    <!-- Quick Actions -->
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Quick Actions</h3>
            </div>
            <div class="box-body">
                <div class="row">
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