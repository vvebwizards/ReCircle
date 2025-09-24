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
                                <select name="role" class="role-select" data-user="{{ $user->name }}" data-current="{{ $user->role }}" data-prev="{{ $user->role }}">
                                    @foreach (['generator', 'maker', 'buyer', 'courier'] as $role)
                                        <option value="{{ $role }}" {{ $user->role === $role ? 'selected' : '' }}>
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
                                <!-- Block/Unblock template (no functionality wired) -->
                                <form method="POST" action="#" class="toggle-block-form">
                                    @csrf
                                    <input type="hidden" name="confirm" value="1">
                                    <button type="button"
                                            class="icon-btn block {{ $user->blocked_at ? 'is-green' : 'is-red' }}"
                                            data-action="{{ $user->blocked_at ? 'unblock' : 'block' }}"
                                            data-user="{{ $user->name }}"
                                            title="{{ $user->blocked_at ? 'Unblock user' : 'Block user' }}"
                                            data-tooltip="{{ $user->blocked_at ? 'Unblock user' : 'Block user' }}">
                                        <i class="fa-solid {{ $user->blocked_at ? 'fa-lock-open' : 'fa-user-lock' }}"></i>
                                        <span class="sr-only">{{ $user->blocked_at ? 'Unblock' : 'Block' }}</span>
                                    </button>
                                </form>
                                <!-- Security template (view-only link placeholder) -->
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
