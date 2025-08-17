{{-- resources/views/components/license-notifications.blade.php --}}
<div id="license-notifications" class="license-notifications">
    {{-- Critical Alert: Expired and in Grace Period --}}
    @if(session('license_critical'))
        <div class="alert alert-danger license-alert-critical" role="alert">
            <div class="d-flex align-items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h5 class="alert-heading mb-2">
                        <i class="fas fa-skull-crossbones"></i> LICENSE EXPIRED
                    </h5>
                    <p class="mb-3">{!! session('license_critical') !!}</p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        <a href="{{ route('license.renew') }}" class="btn btn-danger btn-lg">
                            <i class="fas fa-key"></i> RENEW NOW
                        </a>
                        <a href="{{ route('license.status') }}" class="btn btn-outline-danger">
                            <i class="fas fa-info-circle"></i> Check Status
                        </a>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    {{-- Urgent Alert: 3 Days or Less --}}
    @if(session('license_urgent'))
        <div class="alert alert-warning license-alert-urgent" role="alert">
            <div class="d-flex align-items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-fire fa-2x text-danger"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h5 class="alert-heading mb-2">
                        <i class="fas fa-hourglass-end"></i> URGENT: LICENSE EXPIRING SOON
                    </h5>
                    <p class="mb-3">{!! session('license_urgent') !!}</p>
                    <div class="license-countdown" id="license-countdown">
                        @if(session('license_info'))
                            @php $daysLeft = session('license_info')['days_until_expiry']; @endphp
                            <div class="countdown-display">
                                <span class="countdown-number">{{ $daysLeft }}</span>
                                <span class="countdown-label">Day{{ $daysLeft != 1 ? 's' : '' }} Left</span>
                            </div>
                        @endif
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-3">
                        <a href="{{ route('license.renew') }}" class="btn btn-warning btn-lg">
                            <i class="fas fa-key"></i> RENEW LICENSE
                        </a>
                        <button type="button" class="btn btn-outline-warning" onclick="dismissUrgentAlert()">
                            <i class="fas fa-clock"></i> Remind Later
                        </button>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    {{-- Warning Alert: 4-7 Days --}}
    @if(session('license_warning'))
        <div class="alert alert-info license-alert-warning" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle text-info me-3"></i>
                <div class="flex-grow-1">
                    <strong>License Renewal Reminder:</strong> {!! session('license_warning') !!}
                </div>
                <a href="{{ route('license.renew') }}" class="btn btn-info btn-sm ms-3">
                    <i class="fas fa-key"></i> Renew
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif
</div>

{{-- Styles --}}
<style>
.license-notifications {
    position: sticky;
    top: 0;
    z-index: 1050;
}

.license-alert-critical {
    background: linear-gradient(135deg, #dc3545, #c82333);
    border: none;
    color: white;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
    animation: pulse-critical 2s infinite;
}

.license-alert-urgent {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    border: none;
    color: #000;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
    animation: pulse-urgent 3s infinite;
}

.license-alert-warning {
    background: linear-gradient(135deg, #17a2b8, #138496);
    border: none;
    color: white;
    box-shadow: 0 2px 10px rgba(23, 162, 184, 0.3);
}

.license-countdown {
    background: rgba(255, 255, 255, 0.2);
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    margin: 10px 0;
}

.countdown-display {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.countdown-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #dc3545;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.countdown-label {
    font-size: 1.1rem;
    font-weight: 600;
    color: #495057;
}

@keyframes pulse-critical {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

@keyframes pulse-urgent {
    0% { opacity: 1; }
    50% { opacity: 0.8; }
    100% { opacity: 1; }
}

.license-alert-critical .btn-close,
.license-alert-urgent .btn-close,
.license-alert-warning .btn-close {
    filter: invert(1);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .license-notifications .alert {
        margin: 0 10px;
        font-size: 0.9rem;
    }
    
    .countdown-number {
        font-size: 2rem;
    }
    
    .d-md-flex {
        flex-direction: column !important;
    }
    
    .d-md-flex .btn {
        margin-bottom: 10px;
    }
}
</style>

{{-- JavaScript for enhanced functionality --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss warning alerts after 10 seconds
    const warningAlerts = document.querySelectorAll('.license-alert-warning');
    warningAlerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 10000);
    });
    
    // Prevent dismissing critical alerts too quickly
    const criticalAlerts = document.querySelectorAll('.license-alert-critical');
    criticalAlerts.forEach(function(alert) {
        const closeBtn = alert.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to dismiss this critical license warning? Your system will become inaccessible when the grace period ends.')) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }
    });
});

function dismissUrgentAlert() {
    const urgentAlert = document.querySelector('.license-alert-urgent');
    if (urgentAlert) {
        // Set a localStorage flag to temporarily suppress the urgent alert
        localStorage.setItem('license_urgent_dismissed', Date.now());
        const bsAlert = new bootstrap.Alert(urgentAlert);
        bsAlert.close();
        
        // Show a subtle reminder
        setTimeout(function() {
            showSubtleReminder();
        }, 300000); // Show reminder after 5 minutes
    }
}

function showSubtleReminder() {
    // Check if the urgent alert was dismissed recently (within 1 hour)
    const dismissedTime = localStorage.getItem('license_urgent_dismissed');
    if (dismissedTime && (Date.now() - parseInt(dismissedTime)) < 3600000) {
        const reminderHtml = `
            <div class="alert alert-secondary alert-dismissible fade show subtle-reminder" role="alert">
                <i class="fas fa-bell"></i> 
                <small>Don't forget: Your license expires soon. <a href="${'{{ route("license.renew") }}'}" class="alert-link">Renew now</a></small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        const notificationsContainer = document.getElementById('license-notifications');
        if (notificationsContainer) {
            notificationsContainer.insertAdjacentHTML('beforeend', reminderHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                const reminder = document.querySelector('.subtle-reminder');
                if (reminder) {
                    const bsAlert = new bootstrap.Alert(reminder);
                    bsAlert.close();
                }
            }, 5000);
        }
    }
}

// Clear the dismissed flag when page loads if more than 1 hour has passed
document.addEventListener('DOMContentLoaded', function() {
    const dismissedTime = localStorage.getItem('license_urgent_dismissed');
    if (dismissedTime && (Date.now() - parseInt(dismissedTime)) > 3600000) {
        localStorage.removeItem('license_urgent_dismissed');
    }
});
</script>