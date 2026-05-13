@extends('layouts.admin')
@section('title', 'Roles & Permissions')
@section('content')

@php
  $icons = [
    'sales'=>'bi-receipt','brands'=>'bi-bookmark-star','categories'=>'bi-tags',
    'customers'=>'bi-people','suppliers'=>'bi-truck','purchases'=>'bi-bag',
    'expenses'=>'bi-wallet2','reports'=>'bi-bar-chart-line','users'=>'bi-person-gear',
    'branches'=>'bi-geo-alt','inventory'=>'bi-boxes','payments'=>'bi-cash-stack',
    'products'=>'bi-box-seam','vehicle'=>'bi-car-front',
  ];
@endphp

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h5 class="mb-0 fw-700"><i class="bi bi-shield-lock me-2 text-primary"></i>Roles & Permissions</h5>
    <div class="small text-muted">Check/uncheck permissions per role. Changes save immediately.</div>
  </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show py-2 mb-3"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show py-2 mb-3"><i class="bi bi-x-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- ═════════════════════════════════════════════════════════════ --}}
{{--  SECTION 1: Role–Permission Matrix                           --}}
{{-- ═════════════════════════════════════════════════════════════ --}}
<div class="card mb-4">
  <div class="card-header d-flex align-items-center justify-content-between py-2">
    <span class="fw-700"><i class="bi bi-grid3x3 me-2 text-primary"></i>Permission Matrix</span>
    <div class="d-flex gap-2">
      {{-- Add Role --}}
      <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRoleModal">
        <i class="bi bi-plus-circle me-1"></i>New Role
      </button>
      {{-- Add Permission --}}
      <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#addPermModal">
        <i class="bi bi-key me-1"></i>New Permission
      </button>
    </div>
  </div>

  <div class="table-responsive">
  <table class="table table-bordered table-sm align-middle mb-0" id="permMatrix">
    <thead class="table-dark">
      <tr>
        <th class="ps-3" style="min-width:180px">Resource / Permission</th>
        @foreach($roles as $role)
        <th class="text-center" style="min-width:110px">
          <div class="fw-700">{{ ucfirst($role->name) }}</div>
          <div class="small opacity-75">{{ $role->permissions->count() }} perms</div>
        </th>
        @endforeach
      </tr>
    </thead>
    <tbody>
    @foreach($grouped as $resource => $perms)
      {{-- Resource group header --}}
      <tr class="table-light">
        <td colspan="{{ $roles->count() + 1 }}" class="ps-3 py-1">
          <span class="fw-700 small text-uppercase tracking-wide">
            <i class="{{ $icons[$resource] ?? 'bi-gear' }} me-2 text-primary"></i>{{ ucfirst($resource) }}
          </span>
          {{-- Select all for this resource --}}
          <span class="ms-2 text-muted small">(
            <a href="#" class="text-primary text-decoration-none select-all-resource" data-resource="{{ $resource }}">all</a> /
            <a href="#" class="text-danger text-decoration-none deselect-all-resource" data-resource="{{ $resource }}">none</a>
          )</span>
        </td>
      </tr>
      {{-- Permission rows --}}
      @foreach($perms->sortBy('name') as $perm)
      @php $action = explode('.', $perm->name)[1] ?? $perm->name; @endphp
      <tr>
        <td class="ps-4 py-1 small">
          <code class="small">{{ $perm->name }}</code>
          <span class="badge bg-light text-secondary ms-1" style="font-size:10px">{{ $action }}</span>
        </td>
        @foreach($roles as $role)
        <td class="text-center py-1">
          <input type="checkbox"
            class="form-check-input perm-checkbox"
            id="perm_{{ $role->id }}_{{ $perm->id }}"
            data-role="{{ $role->id }}"
            data-permission="{{ $perm->name }}"
            data-resource="{{ $resource }}"
            {{ $role->permissions->contains('id', $perm->id) ? 'checked' : '' }}
            style="width:16px;height:16px;cursor:pointer">
        </td>
        @endforeach
      </tr>
      @endforeach
    @endforeach
    </tbody>
  </table>
  </div>

  <div class="card-footer py-2 d-flex align-items-center gap-3 flex-wrap">
    <span class="small text-muted">{{ $permissions->count() }} permissions across {{ $roles->count() }} roles</span>
    <div class="ms-auto d-flex gap-2">
      <button class="btn btn-primary btn-sm fw-600" id="saveAllPerms">
        <i class="bi bi-check-circle me-1"></i>Save All Changes
        <span class="badge bg-white text-primary ms-1" id="changedCount" style="display:none">0</span>
      </button>
    </div>
  </div>
</div>

{{-- ═════════════════════════════════════════════════════════════ --}}
{{--  SECTION 2: Delete Roles                                     --}}
{{-- ═════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header py-2 fw-700"><i class="bi bi-shield me-2 text-primary"></i>Roles</div>
      <table class="table table-sm align-middle mb-0">
        <thead class="table-light"><tr><th class="px-3">Role</th><th class="text-center">Users</th><th class="text-center">Perms</th><th></th></tr></thead>
        <tbody>
        @foreach($roles as $role)
        <tr>
          <td class="px-3 fw-600">{{ ucfirst($role->name) }}</td>
          <td class="text-center">
            <span class="badge bg-primary-subtle text-primary-emphasis">{{ $users->filter(fn($u) => $u->hasRole($role->name))->count() }}</span>
          </td>
          <td class="text-center">
            <span class="badge bg-success-subtle text-success-emphasis">{{ $role->permissions->count() }}</span>
          </td>
          <td class="text-end pe-3">
            @if(!in_array($role->name, ['admin','salesperson','accountant']))
            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Delete role {{ $role->name }}?')">
              @csrf @method('DELETE')
              <button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
            @else
            <span class="text-muted small"><i class="bi bi-lock"></i> built-in</span>
            @endif
          </td>
        </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════════ --}}
  {{--  SECTION 3: Assign roles to users                          --}}
  {{-- ═══════════════════════════════════════════════════════════ --}}
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header py-2 fw-700"><i class="bi bi-person-badge me-2 text-success"></i>User Role Assignments</div>

      {{-- Assign form --}}
      <div class="card-body border-bottom pb-3">
        <form method="POST" action="{{ route('admin.roles.assign') }}" class="row g-2 align-items-end">
          @csrf
          <div class="col-sm-5">
            <label class="form-label small fw-600 mb-1">User</label>
            <select name="user_id" class="form-select form-select-sm" required>
              <option value="">— Select User —</option>
              @foreach($users as $u)
              <option value="{{ $u->id }}">{{ $u->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-5">
            <label class="form-label small fw-600 mb-1">Role</label>
            <select name="role" class="form-select form-select-sm" required>
              <option value="">— Select Role —</option>
              @foreach($roles as $r)
              <option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-sm-2">
            <button class="btn btn-success btn-sm w-100">Assign</button>
          </div>
        </form>
      </div>

      {{-- Users list --}}
      <div class="table-responsive" style="max-height:300px;overflow-y:auto">
      <table class="table table-sm align-middle mb-0">
        <thead class="table-light sticky-top"><tr><th class="px-3">User</th><th>Email</th><th>Roles</th></tr></thead>
        <tbody>
        @foreach($users as $u)
        <tr>
          <td class="px-3 small fw-500">{{ $u->name }}</td>
          <td class="small text-muted">{{ $u->email }}</td>
          <td>
            @forelse($u->roles as $r)
            <span class="badge bg-primary me-1">{{ ucfirst($r->name) }}</span>
            @empty
            <span class="badge bg-light text-muted">No role</span>
            @endforelse
          </td>
        </tr>
        @endforeach
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>

{{-- ── MODALS ── --}}
{{-- Add Role Modal --}}
<div class="modal fade" id="addRoleModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.roles.store') }}">
        @csrf
        <div class="modal-header py-2"><h6 class="modal-title fw-700"><i class="bi bi-shield-plus me-2"></i>New Role</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <label class="form-label small fw-600">Role Name</label>
          <input type="text" name="name" class="form-control" placeholder="e.g. cashier" required>
          <div class="form-text">Lowercase, no spaces. Will be created with no permissions.</div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary btn-sm">Create Role</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Add Permission Modal --}}
<div class="modal fade" id="addPermModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.permissions.store') }}">
        @csrf
        <div class="modal-header py-2"><h6 class="modal-title fw-700"><i class="bi bi-key me-2"></i>New Permission</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <label class="form-label small fw-600">Permission Name</label>
          <input type="text" name="name" class="form-control" placeholder="e.g. reports.export" required>
          <div class="form-text">Format: <code>resource.action</code> — e.g. <code>vehicle.delete</code></div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-secondary btn-sm">Create Permission</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Per-role save forms (hidden, submitted by JS) --}}
@foreach($roles as $role)
<form method="POST" action="{{ route('admin.roles.update', $role) }}" id="roleForm_{{ $role->id }}" style="display:none">
  @csrf @method('PUT')
</form>
@endforeach

@endsection

@push('scripts')
<script>
const pendingChanges = {}; // roleId -> Set<permissionName>

function getChangedCount() {
  return Object.values(pendingChanges).reduce((t, s) => t + s.size, 0);
}

// Track checkbox changes
document.querySelectorAll('.perm-checkbox').forEach(cb => {
  cb.dataset.original = cb.checked ? '1' : '0';

  cb.addEventListener('change', function () {
    const roleId = this.dataset.role;
    const perm   = this.dataset.permission;
    const orig   = this.dataset.original === '1';

    if (!pendingChanges[roleId]) pendingChanges[roleId] = new Set();

    if (this.checked !== orig) {
      pendingChanges[roleId].add(perm);
    } else {
      pendingChanges[roleId].delete(perm);
    }

    const cnt = getChangedCount();
    const badge = document.getElementById('changedCount');
    badge.textContent = cnt;
    badge.style.display = cnt > 0 ? '' : 'none';
  });
});

// Select / Deselect all for a resource row
document.querySelectorAll('.select-all-resource').forEach(a => {
  a.addEventListener('click', e => {
    e.preventDefault();
    document.querySelectorAll(`.perm-checkbox[data-resource="${a.dataset.resource}"]`)
      .forEach(cb => { if (!cb.checked) { cb.checked = true; cb.dispatchEvent(new Event('change')); } });
  });
});
document.querySelectorAll('.deselect-all-resource').forEach(a => {
  a.addEventListener('click', e => {
    e.preventDefault();
    document.querySelectorAll(`.perm-checkbox[data-resource="${a.dataset.resource}"]`)
      .forEach(cb => { if (cb.checked) { cb.checked = false; cb.dispatchEvent(new Event('change')); } });
  });
});

// Save All — submit one form per changed role
document.getElementById('saveAllPerms').addEventListener('click', function () {
  if (getChangedCount() === 0) { alert('No changes to save.'); return; }

  const roleIds = Object.keys(pendingChanges).filter(id => pendingChanges[id]?.size > 0);

  roleIds.forEach(roleId => {
    const form = document.getElementById(`roleForm_${roleId}`);
    // Remove old permission inputs
    form.querySelectorAll('input[name="permissions[]"]').forEach(el => el.remove());

    // Add all currently-checked permissions for this role
    document.querySelectorAll(`.perm-checkbox[data-role="${roleId}"]:checked`).forEach(cb => {
      const inp = document.createElement('input');
      inp.type = 'hidden';
      inp.name = 'permissions[]';
      inp.value = cb.dataset.permission;
      form.appendChild(inp);
    });
  });

  // Submit forms sequentially (only one form needed if we merge)
  // We'll submit all via individual requests using fetch
  let promises = roleIds.map(roleId => {
    const form = document.getElementById(`roleForm_${roleId}`);
    return fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
    });
  });

  this.disabled = true;
  this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

  Promise.all(promises).then(() => {
    window.location.reload();
  }).catch(() => {
    alert('An error occurred while saving. Please try again.');
    this.disabled = false;
    this.innerHTML = '<i class="bi bi-check-circle me-1"></i>Save All Changes';
  });
});

window.addEventListener('beforeunload', e => {
  if (getChangedCount() > 0) { e.preventDefault(); e.returnValue = ''; }
});
</script>
@endpush
