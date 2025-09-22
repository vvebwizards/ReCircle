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
                    <tr>
                        <td>
                            <div class="user-info">
                                <span class="avatar sm">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </span>
                                {{ $user->name }}
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                      <td>
                            <select class="role-select" data-current="{{ $user->role }}">
                                @foreach (['generator', 'maker', 'buyer', 'courier'] as $role)
                                    <option value="{{ $role }}" {{ $user->role === $role ? 'selected' : '' }}>
                                        {{ ucfirst($role) }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <span class="badge {{ $user->email_verified_at ? 'active' : 'disabled' }}">
                                {{ $user->email_verified_at ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="actions">
                            <button class="icon-btn edit" data-action="edit"><i class="fa-solid fa-pen"></i></button>
                            <button class="icon-btn toggle" data-action="{{ $user->status === 'active' ? 'disable' : 'enable' }}">
                                <i class="fa-solid {{ $user->status === 'active' ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                            </button>
                            <button class="icon-btn delete" data-action="delete"><i class="fa-solid fa-trash"></i></button>
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
