@php($data = $notification->data)
<div class="wi-style">
    <div class="view-header-block">
        <h3 class="vh-title" style="display:flex;align-items:center;gap:.55rem;margin:0;">
            <span class="al-icon-badge"><i class="fa-solid fa-shield"></i></span>
            <span>Failed Facial Verification</span>
        </h3>
        <div class="vh-meta">
            <span class="vh-pill badge-cond"><i class="fa-solid fa-bolt"></i> Security</span>
            <span class="vh-pill"><i class="fa-regular fa-clock"></i> {{ $notification->created_at->format('M j, Y g:i A T') }}</span>
            @if(empty($notification->read_at))
                <span class="vh-pill" style="background:#fef3c7;color:#92400e;"><i class="fa-solid fa-sparkles"></i> New</span>
            @endif
        </div>
    </div>

    <div class="wi-info-grid">
        <div class="wi-panel">
            <h4 class="wi-panel-title">User</h4>
            <dl class="wi-attrs">
                <dt>Name</dt><dd>{{ $data['user_name'] ?? 'N/A' }}</dd>
                <dt>Email</dt><dd>{{ $data['user_email'] ?? 'N/A' }}</dd>
                @if(isset($data['user_id']))
                    <dt>User ID</dt><dd>{{ $data['user_id'] }}</dd>
                @endif
            </dl>
        </div>
        <div class="wi-panel">
            <h4 class="wi-panel-title">Security</h4>
            <dl class="wi-attrs">
                <dt>Failed Attempts</dt><dd><span class="badge">{{ $data['failed_attempts'] ?? 'N/A' }}</span></dd>
                <dt>IP Address</dt><dd>{{ $data['ip_address'] ?? 'N/A' }}</dd>
                @if(!empty($data['locked_until']))
                    <dt>Locked Until</dt><dd>{{ \Carbon\Carbon::parse($data['locked_until'])->toDayDateTimeString() }}</dd>
                @endif
            </dl>
        </div>
    </div>

    <div class="wi-panel">
        <h4 class="wi-panel-title">Technical</h4>
        <dl class="wi-attrs" style="grid-template-columns:auto 1fr;">
            <dt>Timestamp</dt><dd>{{ $notification->created_at->format('Y-m-d H:i:s T') }}</dd>
            @if(!empty($data['user_agent']))
                <dt>User Agent</dt><dd><code style="white-space:normal">{{ $data['user_agent'] }}</code></dd>
            @endif
            @if(!empty($data['message']))
                <dt>Message</dt><dd>{{ $data['message'] }}</dd>
            @endif
        </dl>
    </div>
</div>
