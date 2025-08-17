@extends('layouts.app')

@section('title', 'Search License Keys')

@section('breadcrumb')
@parent
<li><a href="{{ route('license-management.index') }}">License Management</a></li>
<li class="active">Search Keys</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Search License Keys</h3>
            </div>
            <form action="{{ route('license-management.search') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_id">Company ID</label>
                                <input type="text" name="company_id" id="company_id" class="form-control" 
                                       value="{{ old('company_id') }}" placeholder="Enter company ID to search">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="revoked" {{ old('status') == 'revoked' ? 'selected' : '' }}>Revoked</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_from">Issue Date From</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" 
                                       value="{{ old('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_to">Issue Date To</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" 
                                       value="{{ old('date_to') }}">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <a href="{{ route('license-management.keys') }}" class="btn btn-default">View All Keys</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection