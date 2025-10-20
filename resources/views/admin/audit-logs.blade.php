{{-- resources/views/admin/audit-logs.blade.php --}}
@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('admin-content')
<div class="admin-container">
    <!-- Header Section -->
    <div class="admin-header">
        <div class="header-content">
            <div class="header-text">
                <h1 class="header-title">Audit Logs</h1>
                <p class="header-subtitle">Track and monitor all administrator actions and system changes</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" id="exportBtn">
                    <i class="fa-solid fa-download mr-2"></i> Export
                </button>
                <div class="view-toggle">
                    <button class="toggle-btn active" data-view="table">
                        <i class="fa-solid fa-table"></i>
                    </button>
                    <button class="toggle-btn" data-view="timeline">
                        <i class="fa-solid fa-list-timeline"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="header-stats">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ $totalLogs }}</span>
                    <span class="stat-label">Total Actions</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ $activeAdmins }}</span>
                    <span class="stat-label">Active Admins</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ $criticalActions }}</span>
                    <span class="stat-label">Critical Actions</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filter-card">
        <div class="filter-header">
            <h3 class="filter-title">
                <i class="fa-solid fa-sliders"></i> Filters & Search
            </h3>
            <button class="filter-toggle" id="filterToggle">
                <i class="fa-solid fa-chevron-down"></i>
            </button>
        </div>
        <div class="filter-content" id="filterContent">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="filter-form">
                <div class="filter-grid">
                    <!-- Search -->
                    <div class="filter-group">
                        <label class="filter-label">Search</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Search actions, descriptions, users..."
                                   class="filter-input">
                        </div>
                    </div>

                    <!-- Action Type Filter -->
                    <div class="filter-group">
                        <label class="filter-label">Action Type</label>
                        <select name="action" class="filter-select">
                            <option value="">All Actions</option>
                            @foreach($actionTypes as $actionType)
                                <option value="{{ $actionType }}" {{ request('action') == $actionType ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $actionType)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Admin Filter -->
                    <div class="filter-group">
                        <label class="filter-label">Administrator</label>
                        <select name="admin_id" class="filter-select">
                            <option value="">All Administrators</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="filter-group">
                        <label class="filter-label">Date Range</label>
                        <div class="date-range">
                            <div class="input-with-icon">
                                <i class="fa-solid fa-calendar"></i>
                                <input type="date" 
                                       name="start_date" 
                                       value="{{ request('start_date') }}"
                                       class="filter-input">
                            </div>
                            <span class="date-separator">to</span>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-calendar"></i>
                                <input type="date" 
                                       name="end_date" 
                                       value="{{ request('end_date') }}"
                                       class="filter-input">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-filter mr-2"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline">
                        <i class="fa-solid fa-refresh mr-2"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <section class="content-section" id="tableView">
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-list-check"></i> Audit Trail
                </h3>
                <div class="card-actions">
                    <div class="results-count">{{ $auditLogs->total() }} records found</div>
                    <div class="table-actions">
                        <button class="action-btn" id="refreshBtn" title="Refresh">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            @if($auditLogs->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <h3 class="empty-title">No audit logs found</h3>
                    <p class="empty-description">
                        No audit logs match your current filter criteria. Try adjusting your filters or search terms.
                    </p>
                </div>
            @else
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="sortable" data-sort="created_at">
                                    <span>Timestamp</span>
                                    <i class="fa-solid fa-sort"></i>
                                </th>
                                <th class="sortable" data-sort="admin_id">
                                    <span>Administrator</span>
                                    <i class="fa-solid fa-sort"></i>
                                </th>
                                <th class="sortable" data-sort="action">
                                    <span>Action</span>
                                    <i class="fa-solid fa-sort"></i>
                                </th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auditLogs as $log)
                                <tr class="log-row" data-log-id="{{ $log->id }}">
                                    <td>
                                        <div class="timestamp">
                                            <div class="date">{{ $log->created_at->format('M d, Y') }}</div>
                                            <div class="time">{{ $log->created_at->format('H:i:s') }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar" style="--h: {{ $log->admin_id % 360 }};">
                                                {{ strtoupper(substr($log->admin->name, 0, 2)) }}
                                            </div>
                                            <div class="user-info">
                                                <div class="user-name">{{ $log->admin->name }}</div>
                                                <div class="user-role">{{ $log->admin->role->name ?? 'Admin' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $controller = new App\Http\Controllers\AuditLogController();
                                            $badgeColor = $controller->getActionBadgeColor($log->action);
                                        @endphp
                                        <span class="action-badge {{ $badgeColor }}">
                                            <i class="fa-solid fa-{{ $controller->getActionIcon($log->action) }} mr-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                        </span>
                                    </td>
                                    <td class="description-cell">
                                        <div class="description">{{ $log->description }}</div>
                                    </td>
                                    <td>
                                        <div class="ip-address">{{ $log->ip_address }}</div>
                                    </td>
                                    <td>
                                        @if($log->metadata)
                                            <button type="button" 
                                                    class="btn-details"
                                                    onclick="showMetadata({{ $log->id }}, {{ json_encode($log->metadata) }})">
                                                <i class="fa-solid fa-eye mr-1"></i> Details
                                            </button>
                                        @else
                                            <span class="no-details">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div class="table-info">
                        Showing {{ $auditLogs->firstItem() ?? 0 }} to {{ $auditLogs->lastItem() ?? 0 }} of {{ $auditLogs->total() }} entries
                    </div>
                    <div class="pagination-container">
                        {{ $auditLogs->onEachSide(1)->links('pagination::tailwind') }}
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- Timeline View (Hidden by default) -->
    <section class="content-section hidden" id="timelineView">
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-timeline"></i> Activity Timeline
                </h3>
            </div>
            
            @if($auditLogs->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <h3 class="empty-title">No audit logs found</h3>
                    <p class="empty-description">
                        No audit logs match your current filter criteria. Try adjusting your filters or search terms.
                    </p>
                </div>
            @else
                <div class="timeline-container">
                    @foreach($auditLogs as $log)
                        <div class="timeline-item">
                            <div class="timeline-marker">
                                <div class="timeline-icon {{ $controller->getActionBadgeColor($log->action) }}">
                                    <i class="fa-solid fa-{{ $controller->getActionIcon($log->action) }}"></i>
                                </div>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <div class="timeline-user">
                                        <div class="user-avatar sm" style="--h: {{ $log->admin_id % 360 }};">
                                            {{ strtoupper(substr($log->admin->name, 0, 2)) }}
                                        </div>
                                        <span class="user-name">{{ $log->admin->name }}</span>
                                    </div>
                                    <div class="timeline-time">
                                        {{ $log->created_at->format('M d, Y H:i') }}
                                    </div>
                                </div>
                                <div class="timeline-body">
                                    <h4 class="timeline-action">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</h4>
                                    <p class="timeline-description">{{ $log->description }}</p>
                                    @if($log->metadata)
                                        <button type="button" 
                                                class="btn-text"
                                                onclick="showMetadata({{ $log->id }}, {{ json_encode($log->metadata) }})">
                                            <i class="fa-solid fa-eye mr-1"></i> View Details
                                        </button>
                                    @endif
                                </div>
                                <div class="timeline-footer">
                                    <span class="ip-badge">
                                        <i class="fa-solid fa-network-wired mr-1"></i> {{ $log->ip_address }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="table-footer">
                    <div class="pagination-container">
                        {{ $auditLogs->onEachSide(1)->links('pagination::tailwind') }}
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>

<!-- Metadata Modal -->
<div id="metadataModal" class="modal">
    <div class="modal-overlay" onclick="closeMetadataModal()"></div>
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Action Details</h3>
            <button type="button" class="modal-close" onclick="closeMetadataModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="metadata-header">
                <div class="metadata-info">
                    <div class="metadata-item">
                        <span class="metadata-label">Action:</span>
                        <span id="modalAction" class="metadata-value"></span>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Timestamp:</span>
                        <span id="modalTimestamp" class="metadata-value"></span>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Administrator:</span>
                        <span id="modalAdmin" class="metadata-value"></span>
                    </div>
                </div>
            </div>
            <div class="metadata-content">
                <h4 class="metadata-title">Metadata</h4>
                <pre id="metadataContent" class="metadata-pre"></pre>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeMetadataModal()">Close</button>
            <button type="button" class="btn btn-outline" id="copyMetadataBtn">
                <i class="fa-solid fa-copy mr-2"></i> Copy
            </button>
        </div>
    </div>
</div>

<script>
// Toggle between table and timeline views
document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const view = this.dataset.view;
        
        // Update active button
        document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Show/hide views
        document.getElementById('tableView').classList.toggle('hidden', view !== 'table');
        document.getElementById('timelineView').classList.toggle('hidden', view !== 'timeline');
    });
});

// Toggle filter visibility
document.getElementById('filterToggle').addEventListener('click', function() {
    const filterContent = document.getElementById('filterContent');
    const icon = this.querySelector('i');
    
    filterContent.classList.toggle('hidden');
    icon.classList.toggle('fa-chevron-down');
    icon.classList.toggle('fa-chevron-up');
});

// Refresh button
document.getElementById('refreshBtn').addEventListener('click', function() {
    window.location.reload();
});

// Export functionality
document.getElementById('exportBtn').addEventListener('click', function() {
    // In a real implementation, this would trigger a CSV/PDF export
    alert('Export functionality would be implemented here');
});

// Sortable table columns
document.querySelectorAll('.sortable').forEach(header => {
    header.addEventListener('click', function() {
        const sortField = this.dataset.sort;
        const currentUrl = new URL(window.location.href);
        const currentSort = currentUrl.searchParams.get('sort');
        const currentOrder = currentUrl.searchParams.get('order');
        
        let newOrder = 'asc';
        if (currentSort === sortField && currentOrder === 'asc') {
            newOrder = 'desc';
        }
        
        currentUrl.searchParams.set('sort', sortField);
        currentUrl.searchParams.set('order', newOrder);
        window.location.href = currentUrl.toString();
    });
});

// Metadata modal functions
function showMetadata(logId, metadata) {
    const modal = document.getElementById('metadataModal');
    const logRow = document.querySelector(`.log-row[data-log-id="${logId}"]`);
    
    // Extract data from the table row
    const timestamp = logRow.querySelector('.timestamp .date').textContent + ' ' + 
                      logRow.querySelector('.timestamp .time').textContent;
    const adminName = logRow.querySelector('.user-name').textContent;
    const action = logRow.querySelector('.action-badge').textContent.trim();
    
    // Populate modal
    document.getElementById('modalAction').textContent = action;
    document.getElementById('modalTimestamp').textContent = timestamp;
    document.getElementById('modalAdmin').textContent = adminName;
    document.getElementById('metadataContent').textContent = JSON.stringify(metadata, null, 2);
    
    // Show modal
    modal.classList.add('active');
    
    // Set up copy button
    document.getElementById('copyMetadataBtn').onclick = function() {
        navigator.clipboard.writeText(JSON.stringify(metadata, null, 2))
            .then(() => {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fa-solid fa-check mr-2"></i> Copied!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            });
    };
}

function closeMetadataModal() {
    document.getElementById('metadataModal').classList.remove('active');
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMetadataModal();
    }
});
</script>

<style>
/* Modern CSS variables for consistent theming */
:root {
    --primary: #3b82f6;
    --primary-dark: #2563eb;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --radius: 0.375rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
}

/* Enhanced component styles */
.admin-container {
    max-width: 100%;
    margin: 0 auto;
    padding: 1.5rem;
}

.admin-header {
    margin-bottom: 1.5rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.header-text .header-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0 0 0.25rem 0;
}

.header-text .header-subtitle {
    color: var(--gray-500);
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.view-toggle {
    display: flex;
    background: var(--gray-100);
    border-radius: var(--radius);
    padding: 0.25rem;
}

.toggle-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border: none;
    background: transparent;
    border-radius: var(--radius);
    color: var(--gray-500);
    cursor: pointer;
    transition: all 0.2s;
}

.toggle-btn.active {
    background: white;
    color: var(--primary);
    box-shadow: var(--shadow-sm);
}

.toggle-btn:hover:not(.active) {
    color: var(--gray-700);
}

.header-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-card {
    display: flex;
    align-items: center;
    background: white;
    border-radius: var(--radius-md);
    padding: 1rem;
    box-shadow: var(--shadow);
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius);
    margin-right: 1rem;
    color: white;
}

.stat-icon.primary { background: var(--primary); }
.stat-icon.success { background: var(--success); }
.stat-icon.warning { background: var(--warning); }

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-900);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--gray-500);
}

.filter-card {
    background: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow);
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}

.filter-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-toggle {
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
    padding: 0.25rem;
    border-radius: var(--radius);
    transition: all 0.2s;
}

.filter-toggle:hover {
    background: var(--gray-100);
    color: var(--gray-700);
}

.filter-content {
    padding: 1.5rem;
}

.filter-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--gray-700);
}

.input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
}

.input-with-icon i {
    position: absolute;
    left: 0.75rem;
    color: var(--gray-400);
    z-index: 10;
}

.filter-input, .filter-select {
    width: 100%;
    padding: 0.5rem 0.75rem 0.5rem 2.5rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius);
    font-size: 0.875rem;
    transition: all 0.2s;
}

.filter-input:focus, .filter-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.date-range {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.date-separator {
    color: var(--gray-500);
    font-size: 0.875rem;
    padding: 0 0.25rem;
}

.filter-actions {
    display: flex;
    gap: 0.75rem;
    padding-top: 0.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border: 1px solid transparent;
    border-radius: var(--radius);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-primary {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.btn-primary:hover {
    background: var(--primary-dark);
    border-color: var(--primary-dark);
}

.btn-secondary {
    background: var(--gray-100);
    color: var(--gray-700);
    border-color: var(--gray-300);
}

.btn-secondary:hover {
    background: var(--gray-200);
}

.btn-outline {
    background: transparent;
    color: var(--gray-700);
    border-color: var(--gray-300);
}

.btn-outline:hover {
    background: var(--gray-50);
}

.content-section {
    margin-bottom: 2rem;
}

.content-card {
    background: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}

.card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.results-count {
    font-size: 0.875rem;
    color: var(--gray-500);
}

.table-actions {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border: none;
    background: transparent;
    border-radius: var(--radius);
    color: var(--gray-500);
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn:hover {
    background: var(--gray-100);
    color: var(--gray-700);
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1.5rem;
    text-align: center;
}

.empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 4rem;
    height: 4rem;
    background: var(--gray-100);
    border-radius: 50%;
    margin-bottom: 1rem;
    color: var(--gray-400);
    font-size: 1.5rem;
}

.empty-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0 0 0.5rem 0;
}

.empty-description {
    color: var(--gray-500);
    margin: 0;
    max-width: 24rem;
}

.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: var(--gray-50);
    padding: 0.75rem 1rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid var(--gray-200);
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--gray-200);
    font-size: 0.875rem;
}

.data-table tbody tr {
    transition: background 0.2s;
}

.data-table tbody tr:hover {
    background: var(--gray-50);
}

.sortable {
    cursor: pointer;
    user-select: none;
}

.sortable span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.timestamp .date {
    font-weight: 500;
    color: var(--gray-900);
}

.timestamp .time {
    font-size: 0.75rem;
    color: var(--gray-500);
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: hsl(var(--h), 70%, 45%);
    color: white;
    font-weight: 600;
    font-size: 0.75rem;
}

.user-avatar.sm {
    width: 1.75rem;
    height: 1.75rem;
    font-size: 0.625rem;
}

.user-name {
    font-weight: 500;
    color: var(--gray-900);
}

.user-role {
    font-size: 0.75rem;
    color: var(--gray-500);
}

.action-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.badge.security { background: #e0e7ff; color: #3730a3; }
.badge.user_management { background: #dbeafe; color: #1e40af; }
.badge.system { background: #f3f4f6; color: #374151; }
.badge.moderation { background: #fef3c7; color: #92400e; }

.description-cell {
    max-width: 20rem;
}

.description {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ip-address {
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Monaco, Consolas, monospace;
    font-size: 0.75rem;
    color: var(--gray-600);
    background: var(--gray-100);
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius);
}

.btn-details {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    background: transparent;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius);
    font-size: 0.75rem;
    color: var(--gray-700);
    cursor: pointer;
    transition: all 0.2s;
}

.btn-details:hover {
    background: var(--gray-50);
    border-color: var(--gray-400);
}

.no-details {
    color: var(--gray-400);
    font-size: 0.875rem;
}

.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--gray-200);
}

.table-info {
    font-size: 0.875rem;
    color: var(--gray-500);
}

.pagination-container {
    display: flex;
    justify-content: center;
}

/* Timeline View */
.timeline-container {
    padding: 1.5rem;
}

.timeline-item {
    display: flex;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-right: 1rem;
}

.timeline-marker::after {
    content: '';
    position: absolute;
    top: 2.5rem;
    bottom: -1.5rem;
    width: 2px;
    background: var(--gray-200);
    z-index: 1;
}

.timeline-item:last-child .timeline-marker::after {
    display: none;
}

.timeline-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    color: white;
    z-index: 2;
    position: relative;
}

.timeline-content {
    flex: 1;
    background: var(--gray-50);
    border-radius: var(--radius-md);
    padding: 1rem;
    margin-bottom: 1rem;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.timeline-user {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.timeline-time {
    font-size: 0.75rem;
    color: var(--gray-500);
}

.timeline-body {
    margin-bottom: 0.75rem;
}

.timeline-action {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0 0 0.25rem 0;
}

.timeline-description {
    font-size: 0.875rem;
    color: var(--gray-700);
    margin: 0 0 0.5rem 0;
}

.btn-text {
    display: inline-flex;
    align-items: center;
    background: none;
    border: none;
    color: var(--primary);
    font-size: 0.75rem;
    cursor: pointer;
    padding: 0;
    transition: color 0.2s;
}

.btn-text:hover {
    color: var(--primary-dark);
}

.timeline-footer {
    display: flex;
    justify-content: flex-end;
}

.ip-badge {
    display: inline-flex;
    align-items: center;
    font-size: 0.75rem;
    color: var(--gray-500);
    background: white;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius);
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 50;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.modal-container {
    position: relative;
    width: 90%;
    max-width: 42rem;
    max-height: 90vh;
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0;
}

.modal-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border: none;
    background: transparent;
    border-radius: var(--radius);
    color: var(--gray-500);
    cursor: pointer;
    transition: all 0.2s;
}

.modal-close:hover {
    background: var(--gray-100);
    color: var(--gray-700);
}

.modal-body {
    flex: 1;
    padding: 1.5rem;
    overflow-y: auto;
}

.metadata-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray-200);
}

.metadata-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.metadata-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.metadata-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.metadata-value {
    font-size: 0.875rem;
    color: var(--gray-900);
}

.metadata-content {
    margin-top: 1rem;
}

.metadata-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--gray-900);
    margin: 0 0 0.75rem 0;
}

.metadata-pre {
    background: var(--gray-100);
    border-radius: var(--radius);
    padding: 1rem;
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Monaco, Consolas, monospace;
    font-size: 0.75rem;
    line-height: 1.5;
    color: var(--gray-800);
    overflow-x: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--gray-200);
}

/* Utility classes */
.hidden {
    display: none !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .admin-container {
        padding: 1rem;
    }
    
    .header-content {
        flex-direction: column;
        gap: 1rem;
    }
    
    .header-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .date-range {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .date-separator {
        padding: 0;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .card-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .table-footer {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .modal-container {
        width: 95%;
        margin: 1rem;
    }
    
    .metadata-info {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection