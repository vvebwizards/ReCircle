@extends('layouts.admin')

@section('title', 'Admin Notifications')

@push('admin-head')
    @vite(['resources/css/waste-items.css','resources/css/admin-listings.css'])
    <style>
    .notification-grid { display:grid; grid-template-columns: 1fr; gap: 2rem; padding: 1rem 0; }
    @media (min-width: 992px){ .notification-grid { grid-template-columns: repeat(auto-fit, minmax(550px, 1fr)); } }
    .notification-card { background: linear-gradient(145deg,#ffffff 0%,#f0fdf4 100%); border: 2px solid #22c55e; border-radius: 16px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 8px 25px rgba(34,197,94,.15); transition: transform .25s ease, box-shadow .25s ease; }
    .notification-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(34,197,94,.25); }
    .notification-card.read { background: linear-gradient(145deg,#ffffff 0%,#f1f5f9 100%); border: 2px solid #94a3b8; }
    .type-icon { width:50px; height:50px; border-radius:12px; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:1.2rem; }
    .type-icon.security { background: linear-gradient(135deg,#ef4444,#dc2626); }
    .type-icon.system { background: linear-gradient(135deg,#8b5cf6,#7c3aed); }
    .card-actions .btn { padding: 8px 16px; border-radius: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; font-size:12px; border:none; margin-right:8px; }
    .btn-outline-primary { background: linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; }
    .btn-outline-success { background: linear-gradient(135deg,#10b981,#059669); color:#fff; }
    .btn-outline-danger { background: linear-gradient(135deg,#ef4444,#dc2626); color:#fff; }
        .filter-toolbar { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; }
        .filter-pill { background:#f1f5f9; border:1px solid #e2e8f0; color:#334155; padding:.4rem .75rem; border-radius:999px; font-size:.85rem; cursor:pointer; }
        .filter-pill.active { background:#0ea5e9; color:#fff; border-color:#0284c7; }
        .results-summary { font-size:.9rem; color:#64748b; }

        /* Match admin listings pagination */
        #an-pagination { padding:.75rem 1rem; border-top:1px solid #e2e8f0; display:flex; justify-content:center; align-items:center; gap:1rem; }
        #an-pageinfo { font-size:.75rem; letter-spacing:.5px; text-transform:uppercase; color:#64748b; }
  </style>
@endpush

@section('admin-content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-sm-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-bell mr-2"></i>Security Notifications
            </h1>
            <p class="mb-0 text-gray-600">Monitor and manage security alerts and system notifications</p>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge badge-warning badge-pill px-3 py-2 mr-2">
                {{ $unreadTotal ?? $notifications->where('read_at', null)->count() }} Unread
            </span>
            <button type="button" class="btn btn-success btn-sm shadow-sm" onclick="markAllAsRead()">
                <i class="fas fa-check-double mr-1"></i> Mark All Read
            </button>
        </div>
    </div>

    <!-- Filters -->
    <form id="notif-filter-form" method="GET" action="{{ route('admin.notifications.index') }}" class="card mb-3 p-3" style="border-radius:12px; border:1px solid #e2e8f0;">
        <div class="mb-2">
            <div class="filter-toolbar d-flex align-items-center flex-wrap" style="gap:12px;">
                <span class="text-muted small" style="margin-right:4px;">Quick:</span>
                @php 
                    $st = $filters['status'] ?? 'all';
                    $tp = $filters['type'] ?? 'all';
                    $so = $filters['sort'] ?? 'newest';
                    $base = request()->except(['status','type','q','from','to','page']);
                    $sortBase = array_merge(request()->only(['status','type']), ['page'=>null]);
                @endphp
                <a href="{{ route('admin.notifications.index', array_merge($base, ['status'=>'all'])) }}" class="filter-pill {{ $st==='all'?'active':'' }}" aria-current="{{ $st==='all' ? 'true' : 'false' }}">All</a>
                <a href="{{ route('admin.notifications.index', array_merge($base, ['status'=>'unread'])) }}" class="filter-pill {{ $st==='unread'?'active':'' }}" aria-current="{{ $st==='unread' ? 'true' : 'false' }}">Unread</a>
                <a href="{{ route('admin.notifications.index', array_merge($base, ['status'=>'read'])) }}" class="filter-pill {{ $st==='read'?'active':'' }}" aria-current="{{ $st==='read' ? 'true' : 'false' }}">Read</a>
                <span class="mx-1 text-muted">|</span>
                <a href="{{ route('admin.notifications.index', array_merge($base, ['type'=>'all'])) }}" class="filter-pill {{ $tp==='all'?'active':'' }}" aria-current="{{ $tp==='all' ? 'true' : 'false' }}">All Types</a>
                <a href="{{ route('admin.notifications.index', array_merge($base, ['type'=>'security'])) }}" class="filter-pill {{ $tp==='security'?'active':'' }}" aria-current="{{ $tp==='security' ? 'true' : 'false' }}">Security</a>
                <a href="{{ route('admin.notifications.index', array_merge($base, ['type'=>'system'])) }}" class="filter-pill {{ $tp==='system'?'active':'' }}" aria-current="{{ $tp==='system' ? 'true' : 'false' }}">System</a>
                @if($st!=='all' || $tp!=='all' || $so!=='newest')
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-link btn-sm ml-2" style="text-decoration:none;">Reset</a>
                @endif
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap:12px; margin-top:12px;">
                <span class="text-muted small" style="margin-right:4px;">Sort:</span>
                <a href="{{ route('admin.notifications.index', array_merge($sortBase, ['sort'=>'newest'])) }}" class="filter-pill {{ $so==='newest'?'active':'' }}" aria-current="{{ $so==='newest' ? 'true' : 'false' }}">Newest first</a>
                <a href="{{ route('admin.notifications.index', array_merge($sortBase, ['sort'=>'oldest'])) }}" class="filter-pill {{ $so==='oldest'?'active':'' }}" aria-current="{{ $so==='oldest' ? 'true' : 'false' }}">Oldest first</a>
                <a href="{{ route('admin.notifications.index', array_merge($sortBase, ['sort'=>'unread_first'])) }}" class="filter-pill {{ $so==='unread_first'?'active':'' }}" aria-current="{{ $so==='unread_first' ? 'true' : 'false' }}">Unread first</a>
            </div>
        </div>
        <div class="mt-2" aria-live="polite">
            <small class="text-muted">
                Showing: <strong>{{ ucfirst($st) }}</strong>@if($tp!=='all') · <strong>{{ ucfirst($tp) }}</strong>@endif · <strong>{{ $so==='newest' ? 'Newest first' : ($so==='oldest' ? 'Oldest first' : 'Unread first') }}</strong>
            </small>
        </div>
        
    </form>

    <!-- Notifications Grid -->
    <div class="row">
        <div class="col-12">
            @if($notifications->total() > 0)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    @php $fromIdx = ($notifications->currentPage()-1)*$notifications->perPage()+1; $toIdx = $fromIdx + $notifications->count()-1; @endphp
                    <div class="results-summary">Showing {{ $fromIdx }}–{{ $toIdx }} of {{ $notifications->total() }} results</div>
                    <div class="text-muted small">Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}</div>
                </div>
            @endif
            @if($notifications->count() > 0)
                <div class="notification-grid">
                    @foreach($notifications as $notification)
                    <div class="notification-card {{ is_null($notification->read_at) ? 'unread' : 'read' }}" data-notification-id="{{ $notification->id }}">
                        
                        <!-- Card Header -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                @if($notification->type === 'App\Notifications\FailedFacialVerificationNotification')
                                    <div class="type-icon security mr-3">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold">Security Alert</h6>
                                        <small class="text-muted">Failed Facial Verification</small>
                                    </div>
                                @else
                                    <div class="type-icon system mr-3">
                                        <i class="fas fa-cog"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold">System Notification</h6>
                                        <small class="text-muted">{{ class_basename($notification->type) }}</small>
                                    </div>
                                @endif
                            </div>
                            <div class="text-right">
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                @if(is_null($notification->read_at))
                                    <br><span class="badge badge-success">New</span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Card Body -->
                        @if($notification->type === 'App\Notifications\FailedFacialVerificationNotification')
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>User:</strong> {{ $notification->data['user_name'] ?? 'N/A' }}<br>
                                <strong>Email:</strong> {{ $notification->data['user_email'] ?? 'N/A' }}
                            </div>
                            <div class="col-md-6">
                                <strong>Failed Attempts:</strong> {{ $notification->data['failed_attempts'] ?? 'N/A' }}<br>
                                <strong>IP Address:</strong> {{ $notification->data['ip_address'] ?? 'N/A' }}
                            </div>
                        </div>
                        @endif
                        
                        <!-- Card Actions -->
                        <div class="card-actions">
                            <button type="button" class="btn btn-outline-primary" data-action="view" onclick="viewNotificationDetails('{{ $notification->id }}', event)">
                                <i class="fas fa-search mr-1"></i> View Details
                            </button>
                            
                            @if(is_null($notification->read_at))
                            <button type="button" class="btn btn-outline-success" data-action="read" onclick="markAsRead('{{ $notification->id }}', event)">
                                <i class="fas fa-check mr-1"></i> Mark Read
                            </button>
                            @endif
                            
                            <button type="button" class="btn btn-outline-danger" data-action="delete" onclick="deleteNotification('{{ $notification->id }}', event)">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                @php($prevUrl = $notifications->previousPageUrl())
                @php($nextUrl = $notifications->nextPageUrl())
                <div id="an-pagination" class="pagination mt-3" aria-label="Notifications pagination">
                  <a href="{{ $prevUrl ?: '#' }}" class="pg-btn" {{ $notifications->onFirstPage() ? 'aria-disabled=true tabindex=-1 style=opacity:.4;pointer-events:none;cursor:not-allowed;' : '' }}>Prev</a>
                  <div id="an-pageinfo">Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}</div>
                  <a href="{{ $nextUrl ?: '#' }}" class="pg-btn" {{ ($notifications->currentPage() === $notifications->lastPage()) ? 'aria-disabled=true tabindex=-1 style=opacity:.4;pointer-events:none;cursor:not-allowed;' : '' }}>Next</a>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                    <h4>All Clear!</h4>
                    <p class="text-muted">No security notifications at this time. Your system is running smoothly.</p>
                </div>
            @endif
        </div>
    </div>
</div>



@push('admin-scripts')
<script>
// Debug log to ensure script is loading
console.log('Notification script loaded');

let currentNotificationId = null;
let notificationOrder = [];

// Lightweight modal utilities (same pattern as admin listings)
const overlay = () => document.getElementById('an-modal-overlay');
const anyOpen = () => overlay()?.querySelector('.modal:not(.hidden)');
function showOverlay(){ const o = overlay(); if(o){ o.setAttribute('aria-hidden','false'); o.classList.add('active'); } }
function hideOverlay(){ const o = overlay(); if(o){ o.setAttribute('aria-hidden','true'); o.classList.remove('active'); } }
function openModalEl(m){ if(!m) return; showOverlay(); m.classList.remove('hidden'); document.body.classList.add('body-modal-open'); }
function closeModalEl(m){ if(!m) return; m.classList.add('hidden'); if(!anyOpen()){ hideOverlay(); document.body.classList.remove('body-modal-open'); } }

// Ensure functions are globally available
window.viewNotificationDetails = function(notificationId, event) {
    console.log('View details clicked for notification:', notificationId);
    
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    currentNotificationId = notificationId;
    
    document.getElementById('notificationDetailsContent').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="text-muted">Loading notification details...</p>
        </div>
    `;
    
    // Open custom modal
    openModalEl(document.getElementById('an-details-modal'));
    
    fetch(`/admin/notifications/${notificationId}`, { headers: { 'Accept': 'application/json, text/html;q=0.9', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(async (response) => {
            const ct = (response.headers.get('content-type') || '').toLowerCase();
            console.debug('Details fetch status', response.status, 'content-type', ct);
            if (response.ok && ct.includes('application/json')) {
                return response.json();
            }
            // Fallback: attempt to inject HTML directly
            const text = await response.text();
            if (text && /<html|<div|<section|<article/i.test(text)) {
                document.getElementById('notificationDetailsContent').innerHTML = text;
                return null;
            }
            throw new Error('Unexpected response type');
        })
        .then(payload => {
            const html = payload?.html || '<div class="text-muted">No details available</div>';
            if (payload) {
                document.getElementById('notificationDetailsContent').innerHTML = html;
                try {
                    console.debug('Injected details HTML length', (html || '').length);
                    const el = document.getElementById('notificationDetailsModal');
                    if (el) {
                        const cs = window.getComputedStyle(el);
                        console.debug('Details modal styles after inject', { display: cs.display, visibility: cs.visibility, opacity: cs.opacity });
                    }
                } catch {}
            }
            // Optimistically update card UI to read
            const card = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (card) {
                card.classList.remove('unread');
                card.classList.add('read');
                card.querySelector('.badge-success')?.remove();
                card.querySelector('.btn-outline-success')?.remove();
            }
            // Update unread badge now that it's read
            if (typeof updateUnreadCount === 'function') {
                updateUnreadCount();
            }
        })
        .catch(error => {
            console.error('Error loading details', error);
            document.getElementById('notificationDetailsContent').innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                    <h5 class="text-danger">Error Loading Details</h5>
                    <p class="text-muted">Unable to load notification details. Please try again.</p>
                </div>
            `;
        });
}

window.markAsRead = function(notificationId, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    // Use route: PATCH /admin/notifications/{id}/read
    fetch(`/admin/notifications/${notificationId}/read`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const card = document.querySelector(`[data-notification-id="${notificationId}"]`);
            card.classList.remove('unread');
            card.classList.add('read');
            
            const markReadBtn = card.querySelector('.btn-outline-success');
            if (markReadBtn) {
                markReadBtn.remove();
            }
            
            const newBadge = card.querySelector('.badge-success');
            if (newBadge) {
                newBadge.remove();
            }
            
            updateUnreadCount();
            showToast('✅ Notification marked as read', 'success');
        }
    })
    .catch(error => {
        showToast('❌ Error marking notification as read', 'error');
    });
}

window.markAllAsRead = function() {
    // Use route: PATCH /admin/notifications/mark-all-read
    fetch('/admin/notifications/mark-all-read', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        showToast('Error marking all notifications as read', 'error');
    });
}

window.deleteNotification = function(notificationId, event) {
    console.log('Delete clicked for notification:', notificationId);
    
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    currentNotificationId = notificationId;
    
    // Open custom delete modal
    openModalEl(document.getElementById('an-delete-modal'));
}

// Wait for document to be ready (no jQuery dependency)
document.addEventListener('DOMContentLoaded', function() {
    // Build ordered list of notification ids on load
    notificationOrder = Array.from(document.querySelectorAll('[data-notification-id]')).map(n => n.getAttribute('data-notification-id'));

    // Prev/Next inside details modal
    const prevBtn = document.getElementById('an-prev');
    const nextBtn = document.getElementById('an-next');
    function goto(offset){
        if(!currentNotificationId) return;
        const idx = notificationOrder.indexOf(String(currentNotificationId));
        if(idx === -1) return;
        const nextIdx = (idx + offset + notificationOrder.length) % notificationOrder.length;
        const nextId = notificationOrder[nextIdx];
        window.viewNotificationDetails(nextId);
    }
    prevBtn?.addEventListener('click', ()=>goto(-1));
    nextBtn?.addEventListener('click', ()=>goto(1));
    console.log('Document ready, setting up delete confirmation handler');

    // Close buttons
    overlay()?.querySelectorAll('[data-close]')?.forEach(btn=>btn.addEventListener('click', ()=> closeModalEl(btn.closest('.modal'))));
    // Escape key closes
    window.addEventListener('keydown', e=>{ if(e.key==='Escape'){ overlay()?.querySelectorAll('.modal')?.forEach(m=>closeModalEl(m)); }});
    // Click backdrop to close
    overlay()?.addEventListener('mousedown', e=>{ if(e.target === overlay()){ overlay()?.querySelectorAll('.modal:not(.hidden)')?.forEach(m=>closeModalEl(m)); }});

    // Event delegation safety net
    document.querySelector('.notification-grid')?.addEventListener('click', function(e) {
        const actionBtn = e.target.closest('[data-action]');
        if (!actionBtn) return;
        const card = actionBtn.closest('[data-notification-id]');
        const id = card?.getAttribute('data-notification-id');
        if (!id) return;
        const action = actionBtn.getAttribute('data-action');
        if (action === 'view') return window.viewNotificationDetails(id, e);
        if (action === 'read') return window.markAsRead(id, e);
        if (action === 'delete') return window.deleteNotification(id, e);
    });

    const confirmBtn = document.getElementById('an-confirm-delete');
    if (confirmBtn) confirmBtn.addEventListener('click', function() {
        if (currentNotificationId) {
            fetch(`/admin/notifications/${currentNotificationId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = document.querySelector(`[data-notification-id="${currentNotificationId}"]`);
                    if (card) {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            card.remove();
                            const remainingCards = document.querySelectorAll('.notification-card');
                            if (remainingCards.length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }
                    closeModalEl(document.getElementById('an-delete-modal'));
                    showToast('🗑️ Notification deleted successfully', 'success');
                    updateUnreadCount();
                }
            })
            .catch(error => {
                showToast('❌ Error deleting notification', 'error');
            });
        }
    });
});

window.updateUnreadCount = function() {
    // Try the preferred route first, fallback to legacy path if 404/HTML
    const tryFetch = (url) => fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(async (r) => {
            const ct = (r.headers.get('content-type') || '').toLowerCase();
            if (!r.ok) throw new Error(String(r.status));
            if (!ct.includes('application/json')) { return null; }
            return r.json();
        })
        .catch((e) => { console.warn('Unread-count fetch failed for', url, e); return null; });

    tryFetch('/admin/notifications/unread-count')
        .then(data => data || tryFetch('/admin/notifications/api/unread-count'))
        .then(data => {
            if (!data) return;
            const headerBadge = document.querySelector('.badge-warning');
            if (headerBadge) {
                if (data.count > 0) {
                    headerBadge.textContent = `${data.count} Unread`;
                    headerBadge.style.display = '';
                } else {
                    headerBadge.style.display = 'none';
                }
            }
        })
        .catch(err => {
            console.warn('Error fetching unread count', err);
        });
}

window.showToast = function(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type === 'success' ? 'success' : 'error'}`;
    toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-triangle-exclamation'}"></i><span>${message}</span>`;
    document.body.appendChild(toast);
    // auto hide
    setTimeout(() => { toast.classList.add('fade'); setTimeout(()=>toast.remove(), 400); }, 3200);
}

if (!document.querySelector('meta[name="csrf-token"]')) {
    const csrfMeta = document.createElement('meta');
    csrfMeta.name = 'csrf-token';
    csrfMeta.content = '{{ csrf_token() }}';
    document.head.appendChild(csrfMeta);
}
</script>
@endpush

@push('admin-modals')
<div class="modal-overlay" id="an-modal-overlay" aria-hidden="true">
    <!-- Details Modal -->
    <div class="modal hidden" id="an-details-modal" role="dialog" aria-modal="true" aria-labelledby="an-details-title">
        <div class="modal-header minimal">
            <h3 class="modal-title" id="an-details-title"><i class="fa-solid fa-shield"></i> <span>Security Notification</span></h3>
            <button class="modal-close" data-close aria-label="Close details"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="notificationDetailsContent">
            <div class="wi-skeleton">
                <div class="sk-head"></div>
                <div class="sk-strip"></div>
                <div class="sk-panels">
                    <div class="sk-panel"></div>
                    <div class="sk-panel"></div>
                </div>
            </div>
        </div>
        <!-- Bottom actions removed per UX request; header close remains -->
    </div>

    <!-- Delete Modal -->
    <div class="modal hidden confirm-box" id="an-delete-modal" role="alertdialog" aria-modal="true" aria-labelledby="an-delete-title">
        <div class="modal-header">
            <h3 class="modal-title" id="an-delete-title"><i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;"></i> <span>Delete Notification</span></h3>
            <button class="modal-close" data-close aria-label="Close delete"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="wi-error" role="alert"><i class="fa-solid fa-circle-exclamation"></i><span>This action is permanent. You can’t undo it.</span></div>
            <div class="divider"></div>
            <p style="margin:0;color:#475569;">Are you sure you want to remove this notification from your admin feed?</p>
        </div>
        <div class="modal-actions" style="justify-content:flex-end; padding: 0 1.25rem 1.25rem;">
            <button type="button" class="btn danger" id="an-confirm-delete" style="min-width:160px;">
                <i class="fa-solid fa-trash"></i> Delete
            </button>
        </div>
    </div>
</div>
@endpush

@endsection