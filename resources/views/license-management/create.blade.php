@extends('layouts.app')

@section('title', 'Generate New License Key')

@section('breadcrumb')
@parent
<li><a href="{{ route('license-management.index') }}">License Management</a></li>
<li class="active">Generate New Key</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Generate New License Key</h3>
            </div>
            <form action="{{ route('license-management.store') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_id">Company ID *</label>
                                <input type="text" name="company_id" id="company_id" class="form-control" 
                                       value="{{ old('company_id') }}" required>
                                @error('company_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_name">Company Name</label>
                                <input type="text" name="company_name" id="company_name" class="form-control" 
                                       value="{{ old('company_name') }}">
                                @error('company_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="duration_days">Duration (Days) *</label>
                                <input type="number" name="duration_days" id="duration_days" class="form-control" 
                                       value="{{ old('duration_days', 365) }}" min="1" required>
                                @error('duration_days')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expiry_date">Custom Expiry Date (Optional)</label>
                                <input type="date" name="expiry_date" id="expiry_date" class="form-control" 
                                       value="{{ old('expiry_date') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                @error('expiry_date')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">If not set, will use duration days from today</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-key"></i> Generate License Key
                    </button>
                    <a href="{{ route('license-management.index') }}" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection