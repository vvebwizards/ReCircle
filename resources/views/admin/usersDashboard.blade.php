@extends('layouts.admin')

@section('title', 'Manage Users')

@section('admin-content')
<div class="admin-topbar">
    <div>
        <h1>Manage Users</h1>
        <div class="tb-sub">View, edit, or manage all users</div>
    </div>
    <div class="tb-right">
        <form method="GET" action="{{ route('admin.users') }}">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" 
                   name="search" 
                   placeholder="Search users..." 
                   value="{{ request('search') }}"
                   class="search-input" />
        </form>
    </div>
</div>

<section class="admin-grid">
    <div class="a-card wide">
        <div class="a-title"><i class="fa-solid fa-users"></i> All Users</div>
        <table class="a-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr data-user-id="{{ $user->id }}">
                        <td>
                            <div class="user-info">
                                <span class="avatar sm" style="--h: {{ $user->id % 360 }};">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </span>
                                {{ $user->name }}
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.users.updateRole', $user) }}" class="role-change-form">
                                @csrf
<select name="role" class="role-select" data-user="{{ $user->name }}" data-current="{{ $user->role->value }}" data-prev="{{ $user->role->value }}">
    @foreach (['generator', 'maker', 'buyer', 'courier','admin'] as $role)
        <option value="{{ $role }}" {{ $user->role->value === $role ? 'selected' : '' }}>
            {{ ucfirst($role) }}
        </option>
    @endforeach
</select>
                                <input type="hidden" name="confirm" value="1">
                            </form>
                        </td>
                        <td>
                            <span class="badge {{ $user->email_verified_at ? 'active' : 'disabled' }}" title="{{ $user->email_verified_at ? 'Email verified' : 'Email not verified' }}">
                                {{ $user->email_verified_at ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
<td class="actions">
    <div class="action-group">
        <button class="icon-btn edit is-blue" data-action="edit" title="Edit user" data-tooltip="Edit user">
            <i class="fa-solid fa-pen"></i>
            <span class="sr-only">Edit</span>
        </button>
        
        <form method="POST" action="{{ route('admin.users.toggleStatus', $user) }}" class="toggle-status-form">
            @csrf
            <input type="hidden" name="confirm" value="1">
            <button type="button"
                    class="icon-btn toggle {{ $user->email_verified_at ? 'is-amber' : 'is-green' }}"
                    data-action="{{ $user->email_verified_at ? 'unverify' : 'verify' }}"
                    data-user="{{ $user->name }}"
                    title="{{ $user->email_verified_at ? 'Unverify user' : 'Verify user' }}"
                    data-tooltip="{{ $user->email_verified_at ? 'Unverify user' : 'Verify user' }}">
                <i class="fa-solid {{ $user->email_verified_at ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                <span class="sr-only">{{ $user->email_verified_at ? 'Unverify' : 'Verify' }}</span>
            </button>
        </form>
        @if($user->isBlocked())
            <form method="POST" action="{{ route('admin.users.unblock', $user) }}" class="toggle-block-form">
                @csrf
                <input type="hidden" name="confirm" value="1">
                <button type="submit"
                        class="icon-btn block is-green"
                        data-action="unblock"
                        data-user="{{ $user->name }}"
                        title="Unblock user"
                        data-tooltip="Unblock user">
                    <i class="fa-solid fa-lock-open"></i>
                    <span class="sr-only">Unblock</span>
                </button>
            </form>
        @else
            <button type="button"
                    class="icon-btn block is-red"
                    data-action="block"
                    data-user="{{ $user->name }}"
                    data-user-id="{{ $user->id }}"
                    title="Block user"
                    data-tooltip="Block user"
                    onclick="openBlockModal({{ $user->id }}, '{{ $user->name }}')">
                <i class="fa-solid fa-user-lock"></i>
                <span class="sr-only">Block</span>
            </button>
        @endif
        
        <a href="#" class="icon-btn security" title="Security" data-tooltip="Security">
            <i class="fa-solid fa-shield-halved"></i>
            <span class="sr-only">Security</span>
        </a>
        
        <button class="icon-btn delete is-red" data-action="delete" title="Delete user" data-tooltip="Delete user">
            <i class="fa-solid fa-trash"></i>
            <span class="sr-only">Delete</span>
        </button>
    </div>
</td>
{{-- Add block modal at the bottom of the file --}}
<div id="blockModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Block User</h3>
            <button type="button" class="modal-close" onclick="closeBlockModal()">&times;</button>
        </div>
        <form id="blockUserForm" method="POST">
            @csrf
            <div class="modal-body">
                <p>You are about to block <strong id="blockUserName"></strong>.</p>
                <div class="form-group">
                    <label for="block_reason" class="form-label">Reason for blocking *</label>
                    <textarea name="block_reason" 
                              id="block_reason"
                              class="form-input"
                              placeholder="Please provide a reason for blocking this user..."
                              rows="3"
                              required></textarea>
                </div>
                <div class="form-group">
                    <label class="flex items-center">
                        <input type="checkbox" name="confirm" value="1" required class="mr-2">
                        <span>I confirm I want to block this user</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeBlockModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Block User</button>
            </div>
        </form>
    </div>
</div>

<script>
function openBlockModal(userId, userName) {
    document.getElementById('blockUserName').textContent = userName;
    document.getElementById('blockUserForm').action = `/admin/users/${userId}/block`;
    document.getElementById('blockModal').style.display = 'block';
}

function closeBlockModal() {
    document.getElementById('blockModal').style.display = 'none';
    document.getElementById('block_reason').value = '';
}

// Close modal when clicking outside
document.getElementById('blockModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBlockModal();
    }
});
</script>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 500px;
    max-width: 90%;
    max-height: 90%;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: between;
    align-items: center;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6b7280;
}

.modal-close:hover {
    color: #374151;
}
</style>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="admin-pagination mt-4">
            {{ $users->onEachSide(1)->links('pagination::simple-tailwind') }}
        </div>
    </div>
</section>
@include('components.confirm-modal')
@endsection
