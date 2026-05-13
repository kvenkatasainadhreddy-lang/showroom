@extends('layouts.admin')
@section('title','Users')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0 fw-600">Users</h5>
<a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add User</a></div>
<div class="card">
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead><tr><th class="px-3">Name</th><th>Email</th><th>Role</th><th>Joined</th><th></th></tr></thead>
        <tbody>
        @forelse($users as $u)
        <tr>
            <td class="px-3"><div class="d-flex align-items-center gap-2"><div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:.8rem">{{ strtoupper(substr($u->name,0,1)) }}</div><span class="fw-500">{{ $u->name }}</span></div></td>
            <td class="small">{{ $u->email }}</td>
            <td><span class="badge bg-primary-subtle text-primary">{{ $u->getRoleNames()->first() ?? 'No Role' }}</span></td>
            <td class="small text-muted">{{ $u->created_at->format('d M Y') }}</td>
            <td>
                <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-xs btn-light"><i class="bi bi-pencil"></i></a>
                @if($u->id !== auth()->id())
                <form class="d-inline" method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Delete user?')">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center py-4 text-muted">No users</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
