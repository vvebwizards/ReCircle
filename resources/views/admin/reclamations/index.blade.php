@extends('layouts.admin')

@section('title', 'Reclamations')

@section('admin-content')

<div class="page-container">
    {{-- Header & Actions --}}
    <div class="admin-topbar">
        <div>
            <h1>Reclamations Management</h1>
            <div class="tb-sub">View and manage all user reclamations</div>
        </div>
        {{-- Bulk Actions button removed, functionality integrated below --}}
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <i class="fa-solid fa-check-circle"></i>
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats Cards (REDESIGNED) --}}
    <div class="stats-grid mb-4">
        {{-- Pending --}}
        <div class="stat-card status-pending">
            <div class="stat-icon"><i class="fa-solid fa-hourglass-start"></i></div>
            <div class="stat-details">
                <span class="stat-value">{{ $statusCounts['pending'] }}</span>
                <span class="stat-label">Pending</span>
            </div>
        </div>
        {{-- In Progress --}}
        <div class="stat-card status-in_progress">
            <div class="stat-icon"><i class="fa-solid fa-cog"></i></div>
            <div class="stat-details">
                <span class="stat-value">{{ $statusCounts['in_progress'] }}</span>
                <span class="stat-label">In Progress</span>
            </div>
        </div>
        {{-- Resolved --}}
        <div class="stat-card status-resolved">
            <div class="stat-icon"><i class="fa-solid fa-clipboard-check"></i></div>
            <div class="stat-details">
                <span class="stat-value">{{ $statusCounts['resolved'] }}</span>
                <span class="stat-label">Resolved</span>
            </div>
        </div>
        {{-- Closed --}}
        <div class="stat-card status-closed">
            <div class="stat-icon"><i class="fa-solid fa-folder-closed"></i></div>
            <div class="stat-details">
                <span class="stat-value">{{ $statusCounts['closed'] }}</span>
                <span class="stat-label">Closed</span>
            </div>
        </div>
    </div>

    {{-- Unified Control Bar: Switches between Filters and Bulk Actions --}}
    <div class="filter-bar-container mb-4">
        
        {{-- 1. Standard Filter View (Shown when 0 items are selected) --}}
        <div id="filter-view" class="filter-bar-view">
            <form action="{{ route('admin.reclamations.index') }}" method="GET" class="filter-form">
                <div class="filter-bar">
                    <div class="filter-search-group">
                        <i class="fa-solid fa-search filter-icon"></i>
                        <input type="text" 
                                name="search" 
                                class="a-input filter-search-input" 
                                placeholder="Search by topic, description, user, or email..."
                                value="{{ request('search') }}">
                    </div>

                    <div class="filter-controls">
                        <select name="status" class="a-select filter-status-select">
                            <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending ({{ $statusCounts['pending'] }})</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress ({{ $statusCounts['in_progress'] }})</option>
                            <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved ({{ $statusCounts['resolved'] }})</option>
                            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed ({{ $statusCounts['closed'] }})</option>
                        </select>

                        <select name="severity" class="a-select filter-severity-select">
                            <option value="all" {{ request('severity', 'all') === 'all' ? 'selected' : '' }}>All Severity</option>
                            <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High ({{ $severityCounts['high'] ?? 0 }})</option>
                            <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium ({{ $severityCounts['medium'] ?? 0 }})</option>
                            <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Low ({{ $severityCounts['low'] ?? 0 }})</option>
                        </select>
                    
                        <button type="submit" class="btn btn-secondary filter-btn">
                            Apply Filters
                        </button>
                        
                        @if(request('search') || request('status') !== 'all' || request('severity') !== 'all')
                        <a href="{{ route('admin.reclamations.index') }}" class="btn btn-clear filter-clear-btn" title="Clear Filters">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- 2. Bulk Actions View (Shown when >0 items are selected) --}}
        <div id="bulk-view" class="filter-bar-view" style="display: none;">
            <form action="{{ route('admin.reclamations.bulk-action') }}" method="POST" id="bulk-action-form">
                @csrf
                {{-- Hidden inputs for selected reclamation_ids will be added by JavaScript --}}
                <div class="bulk-action-content">
                    <div class="bulk-action-info">
                        <i class="fa-solid fa-list-check bulk-icon"></i>
                        <span id="selected-count" class="selected-count">0 selected</span>
                    </div>
                    
                    <div class="bulk-action-controls">
                        <select name="action" id="bulk-action" class="a-select bulk-select" required>
                            <option value="">Select action...</option>
                            <option value="mark_in_progress">Mark as In Progress</option>
                            <option value="mark_resolved">Mark as Resolved</option>
                            <option value="mark_closed">Mark as Closed</option>
                            <option value="delete">Delete</option>
                        </select>
                        
                        <button type="submit" class="btn btn-primary bulk-apply-btn" onclick="return confirm('Are you sure you want to perform this action on selected reclamations?')">
                            Apply Action
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Reclamations Table --}}
    <div class="a-card wide">
        <div class="a-title"><i class="fa-solid fa-flag"></i> Reclamations</div>
        
        @if($reclamations->count() > 0)
        <table class="a-table">
            <thead>
                <tr>
                    <th width="30">
                        <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)">
                    </th>
                    <th>Topic</th>
                    <th>Severity</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Responses</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($reclamations as $reclamation)
                <tr data-id="{{ $reclamation->id }}">
                    <td>
                        <input type="checkbox" 
                                name="reclamation_ids_ui[]" 
                                value="{{ $reclamation->id }}" 
                                class="reclamation-checkbox"
                                onchange="updateSelectedCount()">
                    </td>
                    <td>
                        <a href="{{ route('admin.reclamations.show', $reclamation) }}" class="fw-bold" style="color:#4f46e5">
                            {{ $reclamation->topic }}
                        </a>
                        <div style="font-size:.875rem;color:#64748b;margin-top:.25rem">
                            {{ Str::limit($reclamation->description, 80) }}
                        </div>
                    </td>
                    <td>
                        <span class="severity-badge severity-{{ $reclamation->severity }}">
                            {{ $reclamation->severity_label }}
                        </span>
                    </td>
                    <td>
                        <div class="user-info">
                            <span class="avatar sm">{{ strtoupper(substr($reclamation->user->name, 0, 2)) }}</span>
                            <div style="display:inline-block;margin-left:.5rem">
                                {{ $reclamation->user->name }}
                                <div style="font-size:.75rem;color:#64748b">{{ $reclamation->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge status-{{ $reclamation->status }}">
                            {{ ucfirst(str_replace('_', ' ', $reclamation->status)) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge" style="background:#f1f5f9;color:#334155">
                            {{ $reclamation->responses->count() }} response(s)
                        </span>
                    </td>
                    <td>
                        <div style="font-size:.875rem">{{ $reclamation->created_at->format('M d, Y') }}</div>
                        <div style="font-size:.75rem;color:#64748b">{{ $reclamation->created_at->format('h:i A') }}</div>
                    </td>
                    <td class="actions">
                        <div class="action-group">
                            <a href="{{ route('admin.reclamations.show', $reclamation) }}" 
                            class="icon-btn view is-blue" 
                            title="View Details">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <form action="{{ route('admin.reclamations.destroy', $reclamation) }}" 
                                method="POST" 
                                style="display:inline"
                                onsubmit="return confirm('Are you sure you want to delete this reclamation?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn delete is-red" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="admin-pagination mt-4">
            {{ $reclamations->appends(request()->query())->links('pagination::simple-tailwind') }}
        </div>
        @else
        <div class="empty-state">
            <i class="fa-solid fa-flag fa-3x text-muted mb-3"></i>
            <h3>No Reclamations Found</h3>
            <p style="color:#64748b">
                @if(request('search') || request('status') !== 'all' || request('severity') !== 'all')
                  No reclamations match your filters. Try adjusting your search criteria.
                @else
                  There are no reclamations in the system yet.
                @endif
            </p>
        </div>
        @endif
    </div>
</div>

<script>
function toggleSelectAll(checkbox) {
    document.querySelectorAll('.reclamation-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.reclamation-checkbox:checked');
    const count = checkboxes.length;
    
    const filterView = document.getElementById('filter-view');
    const bulkView = document.getElementById('bulk-view');
    const selectedCountSpan = document.getElementById('selected-count');
    const form = document.getElementById('bulk-action-form');
    
    // 1. Toggle Visibility (Unified Bar Logic)
    if (count > 0) {
        filterView.style.display = 'none';
        bulkView.style.display = 'block';
    } else {
        filterView.style.display = 'block';
        bulkView.style.display = 'none';
    }

    // 2. Update Count Text
    selectedCountSpan.textContent = `${count} reclamation${count === 1 ? '' : 's'} selected`;
    
    // 3. Manage Hidden Inputs for POST request
    // Remove existing hidden inputs to avoid duplication
    form.querySelectorAll('input[name="reclamation_ids[]"]').forEach(input => input.remove());
    
    // Add new hidden inputs for selected IDs
    checkboxes.forEach(checkbox => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'reclamation_ids[]'; // This is the name the controller expects
        input.value = checkbox.value;
        form.appendChild(input);
    });
}

// Initial call to ensure the correct view (filter or bulk) is shown on page load
document.addEventListener('DOMContentLoaded', updateSelectedCount);
</script>

<style>
/* --- New Global Styles for Status & Severity Mapping --- */

/* Base Colors */
:root {
    --color-pending-bg: #fffbe6;
    --color-pending-text: #a16207;
    --color-in-progress-bg: #eff6ff;
    --color-in-progress-text: #1d4ed8;
    --color-resolved-bg: #d1fae5;
    --color-resolved-text: #047857;
    --color-closed-bg: #f3f4f6;
    --color-closed-text: #4b5563;
    
    /* Severity Colors */
    --color-high-bg: #fee2e2;
    --color-high-text: #dc2626;
    --color-medium-bg: #fef3c7;
    --color-medium-text: #d97706;
    --color-low-bg: #d1fae5;
    --color-low-text: #059669;
    
    --color-primary: #4f46e5;
    --color-secondary: #6b7280;
    --color-border: #e5e7eb;
}

/* --- Severity Badge Styles --- */
.severity-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    line-height: 1;
    white-space: nowrap;
}
.severity-high { background: var(--color-high-bg); color: var(--color-high-text); }
.severity-medium { background: var(--color-medium-bg); color: var(--color-medium-text); }
.severity-low { background: var(--color-low-bg); color: var(--color-low-text); }

/* --- Utility/Base Styles --- */
.btn-primary {
    background: var(--color-primary);
    color: white;
    padding: 0.65rem 1.25rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.btn-primary:hover {
    background: #3730a3;
}

.btn-outline {
    background: white;
    color: #4b5563;
    padding: 0.65rem 1.25rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
    cursor: pointer;
}
.btn-outline:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
}

/* --- General Admin Topbar (Retained) --- */
.admin-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}
.admin-topbar h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}
.tb-sub {
    font-size: 0.9375rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

/* --- STATS CARD STYLES (Updated for severity) --- */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    display: flex;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #f3f4f6;
    background: white;
}

.stat-card .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
.stat-card .stat-details {
    margin-left: 1rem;
}
.stat-card .stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #1f2937;
}
.stat-card .stat-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    margin-top: 0.25rem;
}

.stat-card.status-pending .stat-icon { background: var(--color-pending-bg); color: var(--color-pending-text); }
.stat-card.status-in_progress .stat-icon { background: var(--color-in-progress-bg); color: var(--color-in-progress-text); }
.stat-card.status-resolved .stat-icon { background: var(--color-resolved-bg); color: var(--color-resolved-text); }
.stat-card.status-closed .stat-icon { background: var(--color-closed-bg); color: var(--color-closed-text); }

/* --- TABLE STATUS BADGE STYLES (Retained) --- */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    line-height: 1;
    white-space: nowrap;
}
.status-pending { background: var(--color-pending-bg); color: var(--color-pending-text); }
.status-in_progress { background: var(--color-in-progress-bg); color: var(--color-in-progress-text); }
.status-resolved { background: var(--color-resolved-bg); color: var(--color-resolved-text); }
.status-closed { background: var(--color-closed-bg); color: var(--color-closed-text); }

/* --- UNIFIED CONTROL BAR STYLES --- */
.filter-bar-container {
    padding: 1rem 1.5rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.filter-bar-view form {
    width: 100%;
}

/* 1. Filter View Specific Styles */
.filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
}

.filter-search-group {
    display: flex;
    align-items: center;
    flex-grow: 1;
    position: relative;
    max-width: 450px;
}

.filter-icon {
    position: absolute;
    left: 1rem;
    color: #9ca3af;
    font-size: 1rem;
}

.a-input.filter-search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 3rem;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    transition: border-color 0.2s;
    font-size: 0.9375rem;
}

.a-input.filter-search-input:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 1px var(--color-primary);
}

.filter-controls {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.a-select.filter-status-select,
.a-select.filter-severity-select {
    padding: 0.75rem 1.25rem;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    background: white url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236B7280' viewBox='0 0 16 16'><path fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/></svg>") no-repeat right 1rem center;
    background-size: 12px;
    appearance: none;
    font-size: 0.9375rem;
    color: #374151;
}

.btn-secondary {
    background: #4b5563;
    color: white;
    padding: 0.65rem 1.25rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}
.btn-secondary:hover {
    background: #374151;
}

.btn-clear {
    background: var(--color-closed-bg);
    color: #4b5563;
    padding: 0.65rem;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}
.btn-clear:hover {
    background: #e5e7eb;
}

/* 2. Bulk Actions View Specific Styles */
.bulk-action-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    width: 100%; 
}

.bulk-action-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
    color: var(--color-primary); 
}

.bulk-icon {
    font-size: 1.25rem;
}

.selected-count {
    font-weight: 600;
    font-size: 1rem;
}

.bulk-action-controls {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    flex-grow: 1;
    justify-content: flex-end;
}

.a-select.bulk-select {
    padding: 0.65rem 1.25rem;
    border-radius: 8px;
    font-size: 0.9375rem;
    color: #374151;
    border: 1px solid var(--color-border);
    background: white url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236B7280' viewBox='0 0 16 16'><path fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/></svg>") no-repeat right 1rem center;
    background-size: 12px;
    appearance: none;
}

.bulk-apply-btn {
    /* Uses .btn-primary */
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .filter-bar {
        flex-direction: column;
        align-items: stretch;
    }
    .filter-search-group {
        max-width: 100%;
    }
    .filter-controls {
        width: 100%;
        flex-direction: column;
        gap: 0.5rem;
    }
    .a-select.filter-status-select,
    .a-select.filter-severity-select,
    .filter-btn {
        width: 100%;
    }
    .filter-clear-btn {
        position: static; 
        width: 100%;
        margin-top: 0.5rem;
    }
    .bulk-action-content {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }
    .bulk-action-controls {
        flex-direction: column;
        gap: 0.5rem;
        justify-content: flex-start;
        width: 100%;
    }
    .bulk-action-controls .btn,
    .a-select.bulk-select {
        width: 100%;
    }
}

/* --- TABLE STYLES --- */
.a-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}
.a-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.a-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.a-table th, .a-table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    text-align: left;
}
.a-table th {
    background: #f9fafb;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    color: #4b5563;
}

/* User Info Styling */
.user-info {
    display: flex;
    align-items: center;
}
.avatar.sm {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e0f2f1;
    color: #0d9488;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    flex-shrink: 0;
}
.action-group {
    display: flex;
    gap: 0.5rem;
}
.icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    transition: background 0.2s;
    font-size: 0.9rem;
    border: none;
    cursor: pointer;
}
.icon-btn.is-blue { background: #eff6ff; color: #3b82f6; }
.icon-btn.is-red { background: #fee2e2; color: #ef4444; }
.icon-btn:hover { opacity: 0.8; }
</style>

@endsection