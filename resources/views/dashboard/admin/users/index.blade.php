@extends('layouts.dashboard')

@section('dashboard-title')
    <div style="margin-bottom: var(--space-2xl);">
        <h1 style="font-size: 2rem; margin-bottom: var(--space-xs);">User Management</h1>
        <p style="color: var(--text-secondary);">Review and manage system users and applications.</p>
    </div>
@endsection

@section('dashboard-content')
    @include('dashboard.admin.partials._tabs')

    <x-ui.table>
        <x-slot name="thead">
            <th>User</th>
            <th class="text-center">Role</th>
            <th>Profession</th>
            <th>Joined</th>
            <th class="text-center">Status</th>
            <th class="text-center">Actions</th>
        </x-slot>

        @forelse($users as $user)
            <tr>
                <td>
                    <div style="display: flex; flex-direction: column;">
                        <strong>{{ $user->name }}</strong>
                        <span class="text-muted">{{ $user->email }}</span>
                    </div>
                </td>
                <td class="text-center">
                    <x-ui.badge variant="{{ $user->role === 'Manager' ? 'info' : 'secondary' }}">
                        {{ $user->role }}
                    </x-ui.badge>
                </td>
                <td>{{ $user->profession }}</td>
                <td>
                    <span class="text-muted">
                        {{ $user->created_at->format('M d, Y H:i:s') }}
                    </span>
                </td>
                <td class="text-center">
                    <x-ui.badge variant="{{ $user->is_active ? 'success' : 'error' }}">
                        {{ $user->is_active ? 'Active' : 'Disabled' }}
                    </x-ui.badge>
                </td>
                <td class="text-center">
                    <div style="display: inline-flex; gap: var(--space-xs);">
                        <!-- Role Toggle -->
                        <form action="{{ route('dashboard.admin.users.role', $user) }}" method="POST" onsubmit="return confirm('Change this user\'s role to {{ $user->role === 'User' ? 'Manager' : 'User' }}?')">
                            @csrf
                            <input type="hidden" name="role" value="{{ $user->role === 'User' ? 'Manager' : 'User' }}">
                            @if($user->role === 'User')
                                <button type="submit" class="btn btn-sm btn-secondary" title="Promote to Manager">
                                    <i class="fas fa-arrow-up"></i>
                                </button>
                            @elseif($user->role === 'Manager')
                                <button type="submit" class="btn btn-sm btn-secondary" title="Demote to User">
                                    <i class="fas fa-arrow-down"></i>
                                </button>
                            @endif
                        </form>
                        
                        <!-- Status Toggle -->
                        <form action="{{ route('dashboard.admin.users.status', $user) }}" method="POST" onsubmit="return confirm('{{ $user->is_active ? 'Disable' : 'Enable' }} this user account?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-secondary" title="{{ $user->is_active ? 'Disable Account' : 'Enable Account' }}">
                                <i class="fas fa-{{ $user->is_active ? 'user-slash' : 'user-check' }}"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: var(--space-2xl);">
                    <p style="color: var(--text-muted);">No users found.</p>
                </td>
            </tr>
        @endforelse

        @if($users->hasPages())
            <x-slot name="tfoot">
                <td colspan="5" style="padding: var(--space-lg) var(--space-xl);">
                    <div class="catalog-pagination">
                        {{ $users->links('vendor.pagination.custom') }}
                    </div>
                </td>
            </x-slot>
        @endif
    </x-ui.table>
@endsection
