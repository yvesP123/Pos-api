<!-- License Expiration Banner - Only show if license is about to expire -->
@if(isset($showLicenseWarning) && $showLicenseWarning)
<div class="row">
    <div class="col-lg-12">
        <div class="alert alert-warning alert-dismissible" style="border-left: 5px solid #f39c12; background-color: #fff3cd; border-color: #ffeaa7;">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-exclamation-triangle"></i> License Key Expiration Warning!</h4>
            <p>
                <strong>Your license key is about to expire 
                @if($daysUntilExpiry == 0)
                    today
                @elseif($daysUntilExpiry == 1)
                    in {{ $daysUntilExpiry }} day
                @else
                    in {{ $daysUntilExpiry }} days
                @endif
                ({{ $licenseExpiryDate }}).</strong> 
                Please update your license to continue using all features without interruption. 
                Contact your administrator or <a href="{{ url('/license/renew') }}" class="alert-link">click here to renew</a>.
            </p>
        </div>
    </div>
</div>
@endif