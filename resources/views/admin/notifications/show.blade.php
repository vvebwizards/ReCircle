@extends('layouts.admin')

@section('title', 'Notification Details')

@section('admin-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="notification-detail-content">
                @php
                    $data = $notification->data;
                @endphp
                
                @if($notification->type === 'App\Notifications\FailedFacialVerificationNotification')
                    <div class="security-alert-detail">
                        <!-- Alert Header -->
                        <div class="alert-header mb-4">
                            <div class="d-flex align-items-center">
                                <div class="alert-icon-large mr-3">
                                    <i class="fas fa-shield-exclamation text-danger"></i>
                                </div>
                                <div>
                                    <h4 class="mb-1">Failed Facial Verification Attempt</h4>
                                    <p class="mb-0 text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $notification->created_at->format('F j, Y \a\t g:i A T') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- User Information Section -->
                        <div class="detail-section mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-user mr-2"></i>User Information
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-card">
                                        <div class="info-label">User ID</div>
                                        <div class="info-value">{{ $data['user_id'] ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-card">
                                        <div class="info-label">Full Name</div>
                                        <div class="info-value">{{ $data['user_name'] ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-card">
                                        <div class="info-label">Email Address</div>
                                        <div class="info-value">
                                            <i class="fas fa-envelope mr-2 text-info"></i>
                                            {{ $data['user_email'] ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Security Details Section -->
                        <div class="detail-section mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-shield-alt mr-2"></i>Security Details
                            </h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-card highlight-warning">
                                        <div class="info-label">Failed Attempts</div>
                                        <div class="info-value">
                                            <span class="badge badge-warning badge-lg">
                                                {{ $data['failed_attempts'] ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-card">
                                        <div class="info-label">Account Status</div>
                                        <div class="info-value">
                                            @if(isset($data['locked_until']))
                                                <span class="badge badge-danger badge-lg">
                                                    <i class="fas fa-lock mr-1"></i>Locked
                                                </span>
                                            @else
                                                <span class="badge badge-success badge-lg">
                                                    <i class="fas fa-unlock mr-1"></i>Active
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if(isset($data['locked_until']))
                                <div class="col-md-4">
                                    <div class="info-card highlight-danger">
                                        <div class="info-label">Locked Until</div>
                                        <div class="info-value">
                                            {{ \Carbon\Carbon::parse($data['locked_until'])->format('M j, g:i A') }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Technical Details Section -->
                        <div class="detail-section mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-network-wired mr-2"></i>Technical Details
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-card">
                                        <div class="info-label">IP Address</div>
                                        <div class="info-value font-monospace">
                                            <i class="fas fa-map-marker-alt mr-2 text-danger"></i>
                                            {{ $data['ip_address'] ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-card">
                                        <div class="info-label">Timestamp (UTC)</div>
                                        <div class="info-value font-monospace">
                                            {{ $notification->created_at->format('Y-m-d H:i:s T') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="info-card">
                                        <div class="info-label">User Agent</div>
                                        <div class="info-value font-monospace small">
                                            {{ $data['user_agent'] ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Alert Message Section -->
                        <div class="detail-section">
                            <h5 class="section-title">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Alert Message
                            </h5>
                            <div class="alert-message-box">
                                <i class="fas fa-info-circle mr-2"></i>
                                {{ $data['message'] ?? 'Failed facial verification attempt detected for user account.' }}
                            </div>
                        </div>
                    </div>
                @else
                    <!-- General Notification Detail -->
                    <div class="general-notification-detail">
                        <div class="alert-header mb-4">
                            <div class="d-flex align-items-center">
                                <div class="alert-icon-large mr-3">
                                    <i class="fas fa-bell text-info"></i>
                                </div>
                                <div>
                                    <h4 class="mb-1">{{ class_basename($notification->type) }}</h4>
                                    <p class="mb-0 text-muted">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $notification->created_at->format('F j, Y \a\t g:i A T') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h5 class="section-title">
                                <i class="fas fa-info-circle mr-2"></i>Notification Details
                            </h5>
                            <div class="notification-content-box">
                                @if(isset($data['message']))
                                    {{ $data['message'] }}
                                @else
                                    <pre class="json-display">{{ json_encode($data, JSON_PRETTY_PRINT) }}</pre>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Action Buttons for Modal -->
                @if(!$notification->read_at)
                <div class="mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-success" onclick="markAsRead('{{ $notification->id }}')">
                        <i class="fas fa-check mr-1"></i> Mark as Read
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.notification-detail-content {
    max-height: 70vh;
    overflow-y: auto;
}

.alert-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid #dc3545;
}

.alert-icon-large {
    font-size: 2.5rem;
}

.section-title {
    color: #495057;
    font-weight: 600;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e9ecef;
}

.info-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.info-card:hover {
    background: #e9ecef;
}

.info-card.highlight-warning {
    border-left: 4px solid #ffc107;
    background: #fff9e6;
}

.info-card.highlight-danger {
    border-left: 4px solid #dc3545;
    background: #ffebee;
}

.info-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.info-value {
    font-size: 1rem;
    font-weight: 500;
    color: #495057;
}

.badge-lg {
    font-size: 0.9rem;
    padding: 8px 12px;
}

.alert-message-box {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 20px;
    color: #856404;
    font-size: 1rem;
    line-height: 1.5;
}

.notification-content-box {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #e9ecef;
}

.json-display {
    background: #2d3748;
    color: #e2e8f0;
    border-radius: 6px;
    padding: 15px;
    font-size: 0.85rem;
    max-height: 200px;
    overflow-y: auto;
}

.font-monospace {
    font-family: 'Courier New', Courier, monospace !important;
}

.detail-section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    border: 1px solid #e9ecef;
}
</style>
@endsection