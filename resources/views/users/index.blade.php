@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')
@section('page-sub', 'Manage access to the admin dashboard')

@section('content')

<div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
    <button onclick="document.getElementById('addUserModal').showModal()" style="display:flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--radius);border:1px solid var(--border);background:var(--surface);font-size:13px;font-weight:500;color:var(--text-1);cursor:pointer;">
        <i class="ti ti-plus" style="font-size:15px;"></i> Add User
    </button>
</div>

<!-- Add User Modal -->
<dialog id="addUserModal" style="border:none;border-radius:var(--radius);padding:0;max-width:400px;">
    <div style="padding:24px;background:var(--surface);">
        <h3 style="margin:0 0 16px 0;font-size:16px;font-weight:600;color:var(--text-1);">Add New User</h3>
        <form method="POST" action="{{ route('users.store') }}" style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            <div>
                <label style="display:block;font-size:12px;font-weight:500;color:var(--text-2);margin-bottom:6px;">Name</label>
                <input type="text" name="name" required style="width:100%;padding:8px 12px;border-radius:var(--radius);border:1px solid var(--border);background:var(--background);font-size:13px;color:var(--text-1);">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:500;color:var(--text-2);margin-bottom:6px;">Email</label>
                <input type="email" name="email" required style="width:100%;padding:8px 12px;border-radius:var(--radius);border:1px solid var(--border);background:var(--background);font-size:13px;color:var(--text-1);">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:500;color:var(--text-2);margin-bottom:6px;">Password</label>
                <input type="password" name="password" required minlength="8" style="width:100%;padding:8px 12px;border-radius:var(--radius);border:1px solid var(--border);background:var(--background);font-size:13px;color:var(--text-1);">
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" name="is_admin" id="is_admin" style="width:16px;height:16px;">
                <label for="is_admin" style="font-size:13px;color:var(--text-1);">Admin User</label>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px;">
                <button type="button" onclick="document.getElementById('addUserModal').close()" style="padding:8px 16px;border-radius:var(--radius);border:1px solid var(--border);background:var(--surface);font-size:13px;font-weight:500;color:var(--text-1);cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:8px 16px;border-radius:var(--radius);border:none;background:#5C35C9;font-size:13px;font-weight:500;color:white;cursor:pointer;">Create User</button>
            </div>
        </form>
    </div>
</dialog>

<div class="panel" style="padding:0;overflow:hidden;">
    <table>
        <thead>
            <tr>
                <th style="padding:12px 16px;">User</th>
                <th style="padding:12px 16px;">Email</th>
                <th style="padding:12px 16px;">Role</th>
                <th style="padding:12px 16px;">Joined</th>
                <th style="padding:12px 16px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td style="padding:12px 16px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="avatar" style="width:36px;height:36px;font-size:13px;">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <div style="font-weight:600;font-size:13px;">{{ $user->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:12px 16px;color:var(--text-2);font-size:13px;">{{ $user->email }}</td>
                    <td style="padding:12px 16px;">
                        @if($user->is_admin)
                            <span class="badge badge-ok">Admin</span>
                        @else
                            <span class="badge" style="background:var(--background);color:var(--text-2);">User</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px;font-size:12px;color:var(--text-3);font-family:monospace;">
                        {{ $user->created_at->format('Y-m-d') }}
                    </td>
                    <td style="padding:12px 16px;">
                        <div style="display:flex;gap:6px;">
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn" title="Delete" style="color:var(--red-text);cursor:pointer;background:none;border:none;padding:0;"><i class="ti ti-trash" style="font-size:15px;"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:32px;color:var(--text-3);">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
