@extends('layouts.admin')

@section('title', 'Reclamation #{{ $reclamation->id }} - {{ $reclamation->topic }}')

@section('admin-content')

<div class="reclamation-detail-page">

    {{-- Header & Back Button --}}
    <header class="page-header">
        <a href="{{ route('admin.reclamations.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> All Reclamations
        </a>
        <h1 class="page-title">Reclamation #{{ $reclamation->id }}</h1>
        <div class="status-indicator status-{{ $reclamation->status }}">
            {{ ucfirst($reclamation->status) }}
        </div>
    </header>
    
    {{-- Success Alert --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    {{-- Main Grid Layout --}}
    <div class="detail-grid">

        {{-- LEFT COLUMN: Topic, Description, and Responses --}}
        <div class="main-column">

            {{-- Summary Details (Modified: Priority Removed) --}}
            <div class="summary-details">
                <div class="detail-item"><i class="fas fa-user-circle"></i> Submitted by: <strong>{{ optional($reclamation->user)->name ?? 'Guest User' }}</strong></div>
                <div class="detail-item"><i class="fas fa-calendar-alt"></i> Created: <strong>{{ $reclamation->created_at?->format('M d, Y') }} ({{ $reclamation->created_at?->diffForHumans() }})</strong></div>
            </div>

            {{-- Topic & Description --}}
            <section class="reclamation-body-card">
                <h2 class="card-title">{{ $reclamation->topic }}</h2>
                <div class="description-content">
                    <p class="whitespace-pre-wrap">{{ $reclamation->description }}</p>
                </div>
            </section>

            {{-- Responses Section --}}
<section class="responses-card">
    <header class="responses-header">
        <h2><i class="fas fa-comments"></i> Conversation History</h2>
        <span class="responses-count">{{ $reclamation->responses->count() }} response{{ $reclamation->responses->count() !== 1 ? 's' : '' }}</span>
    </header>
    
    @if($reclamation->responses->count())
        <div class="responses-list">
            @foreach($reclamation->responses->sortBy('created_at') as $response)
                <div class="response-item {{ $response->isFromAdmin() ? 'admin-response' : 'user-response' }}">
                    <div class="response-header">
                        <div class="response-author">
                            <strong>
                                @if($response->isFromAdmin())
                                    {{ $response->admin->name ?? 'Admin' }}
                                @else
                                    {{ $response->user->name ?? $reclamation->user->name }}
                                @endif
                            </strong>
                            @if($response->isFromAdmin())
                                <span class="role-badge">Admin</span>
                            @else
                                <span class="role-badge user-badge">User</span>
                            @endif
                        </div>
                        <span class="response-time">{{ $response->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="response-message">{{ $response->message }}</div>
                    <div class="response-timestamp">{{ $response->created_at->format('M d, Y @ H:i') }}</div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state-responses">
            <i class="far fa-comment-dots"></i>
            <p>No conversation yet. Start the thread by adding a response.</p>
        </div>
    @endif
</section>
        </div>
        
        {{-- RIGHT COLUMN: Actions and Management --}}
        <div class="sidebar-column">
            
            {{-- Unified Response Form (Modified: Status removed) --}}
            <section class="action-card">
                <h3 class="action-title"><i class="fas fa-reply"></i> Add Response</h3>
                <form action="{{ route('admin.reclamations.response.store', $reclamation) }}" method="POST" class="action-form">
                    @csrf
                    
                    <div class="form-group mb-3">
                        <label for="message" class="form-label sr-only">Response Message</label>
                        <textarea name="message" id="message" rows="3" class="form-control" placeholder="Type your admin response here..." required minlength="10"></textarea>
                    </div>
                        
                    {{-- Hidden input to keep status update optional --}}
                    <input type="hidden" name="update_status" value="">
                    
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-paper-plane"></i> Send Response
                    </button>
                </form>
            </section>
            
            {{-- Quick Status Change (Modified: Now dedicated Status Update) --}}
            <section class="action-card quick-status-card">
                <h3 class="action-title"><i class="fas fa-sync-alt"></i> Update Status</h3>
                <form action="{{ route('admin.reclamations.update-status', $reclamation) }}" method="POST" class="status-update-form">
                    @csrf
                    @method('PATCH')
                    
                    <div class="form-group mb-3">
                        <label for="status" class="form-label compact-label">Current Status: {{ ucfirst($reclamation->status) }}</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="pending" {{ $reclamation->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ $reclamation->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ $reclamation->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ $reclamation->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-secondary btn-sm w-100">
                        <i class="fas fa-check-circle"></i> Save New Status
                    </button>
                </form>
            </section>

            {{-- Danger Zone --}}
            <section class="action-card danger-zone">
                <h3 class="action-title danger-title"><i class="fas fa-bomb"></i> Danger Zone</h3>
                <form action="{{ route('admin.reclamations.destroy', $reclamation) }}" method="POST" class="delete-form" onsubmit="return confirm('WARNING: Are you absolutely sure you want to delete this reclamation? This action is irreversible.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="fas fa-trash-alt"></i> Permanently Delete Reclamation
                    </button>
                </form>
            </section>

        </div>
    </div>
</div>

{{-- All previous styles remain the same, except for .form-row-compact which is no longer needed --}}
<style>
/* --- BASE & UTILITIES --- */
.reclamation-detail-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem 1.5rem;
    font-family: 'Inter', sans-serif;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #eef2ff; /* Lighter background */
    color: #4f46e5; /* Primary color text */
    border: 1px solid #c7d2fe;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
    font-size: 0.9375rem;
}

.btn-back:hover {
    background: #e0e7ff;
    color: #4338ca;
    transform: translateY(-1px);
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* --- HEADER --- */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    flex-grow: 1;
    margin: 0 1rem;
}

.status-indicator {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 700;
    text-transform: uppercase;
}

.status-pending { background: #fef3c7; color: #b45309; }
.status-in_progress { background: #e0f2f1; color: #0f766e; }
.status-resolved { background: #d1fae5; color: #059669; }
.status-closed { background: #f3f4f6; color: #4b5563; }

/* --- GRID LAYOUT --- */
.detail-grid {
    display: grid;
    grid-template-columns: 3fr 1fr; /* Main column wider than sidebar */
    gap: 2rem;
}

.main-column {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* --- SUMMARY DETAILS (Modified) --- */
.summary-details {
    display: flex;
    gap: 2rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    margin-bottom: 0.5rem;
}

.detail-item {
    font-size: 0.9375rem;
    color: #4b5563;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-item strong {
    color: #1f2937;
    font-weight: 600;
}

.detail-item .fas {
    color: #6366f1;
}

.priority-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
}

/* --- BODY CARD --- */
.reclamation-body-card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
}

.card-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f3f4f6;
}

.user-badge {
    background: #6b7280 !important; /* Gray color for user badge */
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 500;
}

.description-content p {
    margin: 0;
    color: #374151;
    line-height: 1.6;
    font-size: 1rem;
    white-space: pre-wrap;
}

/* --- RESPONSES --- */
.responses-card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
}

.responses-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.responses-header h2 {
    margin: 0;
    color: #1f2937;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.responses-count {
    background: #f3f4f6;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #4b5563;
}

.responses-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.response-item {
    padding: 1.25rem;
    border-radius: 8px;
    border-left: 4px solid;
}

.admin-response {
    background: #ecfdf5; /* Light green for admin */
    border-left-color: #059669;
}

.user-response {
    background: #eff6ff; /* Light blue for user/system */
    border-left-color: #3b82f6;
}

.response-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.response-author {
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.role-badge {
    background: #3b82f6;
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 500;
}

.response-time {
    font-size: 0.8125rem;
    color: #6b7280;
    font-style: italic;
}

.response-message {
    color: #374151;
    line-height: 1.5;
    margin-bottom: 0.5rem;
}

.response-timestamp {
    font-size: 0.75rem;
    color: #9ca3af;
    text-align: right;
}

.empty-state-responses {
    text-align: center;
    padding: 3rem 0;
    color: #9ca3af;
}
.empty-state-responses i {
    font-size: 3rem;
    margin-bottom: 0.75rem;
    display: block;
}

/* --- SIDEBAR ACTIONS --- */
.sidebar-column {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.action-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
}

.action-title {
    margin: 0 0 1.25rem 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f3f4f6;
}

/* Form Styling */
.form-control, .form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: white;
    font-size: 0.9375rem;
}

.form-control:focus, .form-select:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    outline: none;
}

/* Removed .form-row-compact as it is no longer needed */

.compact-label {
    display: block;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #4b5563;
    margin-bottom: 0.25rem;
}

/* Buttons */
.btn {
    padding: 0.65rem 1rem;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-primary {
    background: #4f46e5;
    color: white;
}

.btn-primary:hover {
    background: #4338ca;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #059669;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.w-100 { width: 100%; }

/* Danger Zone */
.danger-zone {
    border-color: #fca5a5; /* Light red border */
    background: #fef2f2; /* Light red background */
}

.danger-zone .danger-title {
    color: #b91c1c; /* Darker red title */
    border-bottom-color: #fecaca;
}

/* --- RESPONSIVE ADJUSTMENTS --- */
@media (max-width: 1200px) {
    .detail-grid {
        grid-template-columns: 2fr 1fr;
    }
}

@media (max-width: 1024px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
    .main-column {
        order: 1;
    }
    .sidebar-column {
        order: 2;
    }
}

@media (max-width: 768px) {
    .reclamation-detail-page { padding: 1rem; }
    .page-header { flex-wrap: wrap; gap: 1rem; }
    .page-title { order: 1; flex-basis: 100%; margin: 0; }
    .btn-back { order: 2; }
    .status-indicator { order: 3; }
    .summary-details { flex-direction: column; gap: 0.75rem; }
    
    /* Re-introduce this if needed for other forms, but removed for the response form */
    /* .form-row-compact { flex-direction: column; align-items: stretch; } */ 
}

@media (max-width: 480px) {
    .responses-header { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textarea for a better writing experience
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            // Reset height to auto to calculate the correct scrollHeight
            this.style.height = 'auto';
            // Set height to scrollHeight, but cap at 200px to prevent excessive growth
            this.style.height = Math.min(this.scrollHeight, 200) + 'px';
        });
        // Initial adjustment on page load
        if (textarea.scrollHeight > textarea.clientHeight) {
             textarea.style.height = Math.min(textarea.scrollHeight, 200) + 'px';
        }
    });
});
</script>
@endsection