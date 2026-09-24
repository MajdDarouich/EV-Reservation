@extends('layouts/contentNavbarLayout')

@section('title', 'Roles & Permissions')

@section('content')

    {{-- =========================================================
        Flash Messages
    ========================================================== --}}

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">

            <i class="bx bx-check-circle me-2"></i>

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close">
            </button>

        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">

            <i class="bx bx-error-circle me-2"></i>

            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close">
            </button>

        </div>
    @endif


    {{-- =========================================================
        Page Header
    ========================================================== --}}

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Administration /</span>
                Roles & Permissions
            </h4>

            <p class="text-muted mb-0">
                Manage roles and their assigned permissions.
            </p>

        </div>

        <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addRoleModal">

            <i class="bx bx-plus me-1"></i>

            Add New Role

        </button>

    </div>


    {{-- =========================================================
        Statistics
    ========================================================== --}}

    <div class="row mb-4">

        {{-- Total Roles --}}
        <div class="col-lg-4 col-md-6 mb-4">

            <div class="card h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="avatar flex-shrink-0">

                            <span class="avatar-initial rounded bg-label-primary">

                                <i class="bx bx-group"></i>

                            </span>

                        </div>

                        <div class="ms-3">

                            <span class="fw-semibold d-block mb-1">
                                Total Roles
                            </span>

                            <h3 class="card-title mb-0">
                                {{ $roles->count() }}
                            </h3>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Total Permissions --}}
        <div class="col-lg-4 col-md-6 mb-4">

            <div class="card h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="avatar flex-shrink-0">

                            <span class="avatar-initial rounded bg-label-success">

                                <i class="bx bx-key"></i>

                            </span>

                        </div>

                        <div class="ms-3">

                            <span class="fw-semibold d-block mb-1">
                                Total Permissions
                            </span>

                            <h3 class="card-title mb-0">
                                {{ $permissions->count() }}
                            </h3>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Permission Groups --}}
        <div class="col-lg-4 col-md-6 mb-4">

            <div class="card h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="avatar flex-shrink-0">

                            <span class="avatar-initial rounded bg-label-info">

                                <i class="bx bx-folder"></i>

                            </span>

                        </div>

                        <div class="ms-3">

                            <span class="fw-semibold d-block mb-1">
                                Resources
                            </span>

                            <h3 class="card-title mb-0">
                                {{ $groupedPermissions->count() }}
                            </h3>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
        Roles
    ========================================================== --}}

    <div class="card mb-4">

        <div class="card-header">

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <h5 class="mb-1">
                        Roles
                    </h5>

                    <p class="text-muted mb-0">
                        Manage roles and the permissions assigned to them.
                    </p>

                </div>

                <button
                    type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addRoleModal">

                    <i class="bx bx-plus me-1"></i>

                    Add Role

                </button>

            </div>

        </div>


        <div class="table-responsive text-nowrap">

            <table class="table">

                <thead>

                    <tr>

                        <th>
                            Role
                        </th>

                        <th>
                            Guard
                        </th>

                        <th>
                            Permissions
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody class="table-border-bottom-0">

                    @forelse ($roles as $role)

                        <tr>

                            {{-- =================================================
                                Role Name
                            ================================================== --}}

                            <td>

                                <div class="d-flex align-items-center">

                                    <div class="avatar avatar-sm me-3">

                                        <span class="avatar-initial rounded-circle bg-label-primary">

                                            {{ strtoupper(substr($role->name, 0, 1)) }}

                                        </span>

                                    </div>

                                    <div>

                                        <span class="fw-semibold">
                                            {{ $role->name }}
                                        </span>

                                        @if ($role->name === 'Super Admin')

                                            <span class="badge bg-label-danger ms-2">
                                                Protected
                                            </span>

                                        @endif

                                    </div>

                                </div>

                            </td>


                            {{-- =================================================
                                Guard
                            ================================================== --}}

                            <td>

                                <span class="badge bg-label-secondary">

                                    {{ $role->guard_name }}

                                </span>

                            </td>


                            {{-- =================================================
                                Permissions
                            ================================================== --}}

                            <td>

                                @if ($role->permissions->isEmpty())

                                    <span class="text-muted">
                                        No permissions
                                    </span>

                                @else

                                    <div class="d-flex flex-wrap gap-1">

                                        @foreach ($role->permissions->take(4) as $permission)

                                            @php

                                                $parts = explode('.', $permission->name, 2);

                                                $resource = $parts[0] ?? $permission->name;

                                                $action = $parts[1] ?? '';

                                            @endphp

                                            <span class="badge bg-label-info">

                                                {{ ucfirst($resource) }}.
                                                {{ ucfirst($action) }}

                                            </span>

                                        @endforeach


                                        @if ($role->permissions->count() > 4)

                                            <span class="badge bg-label-secondary">

                                                +{{ $role->permissions->count() - 4 }}

                                                more

                                            </span>

                                        @endif

                                    </div>

                                @endif

                            </td>


                            {{-- =================================================
                                Actions
                            ================================================== --}}

                            <td>

                                <div class="dropdown">

                                    <button
                                        type="button"
                                        class="btn p-0 dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">

                                        <i class="bx bx-dots-vertical-rounded"></i>

                                    </button>


                                    <div class="dropdown-menu dropdown-menu-end">

                                        @if ($role->name !== 'Super Admin')

                                            {{-- Edit --}}
                                            <a
                                                href="javascript:void(0);"
                                                class="dropdown-item"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editRoleModal{{ $role->id }}">

                                                <i class="bx bx-edit-alt me-1"></i>

                                                Edit

                                            </a>


                                            {{-- Delete --}}
                                            <a
                                                href="javascript:void(0);"
                                                class="dropdown-item text-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteRoleModal{{ $role->id }}">

                                                <i class="bx bx-trash me-1"></i>

                                                Delete

                                            </a>

                                        @else

                                            <span class="dropdown-item text-muted">

                                                <i class="bx bx-lock-alt me-1"></i>

                                                Protected

                                            </span>

                                        @endif

                                    </div>

                                </div>

                            </td>

                        </tr>


                        {{-- =====================================================
                            EDIT ROLE MODAL
                        ====================================================== --}}

                        @if ($role->name !== 'Super Admin')

                            <div
                                class="modal fade"
                                id="editRoleModal{{ $role->id }}"
                                tabindex="-1"
                                aria-hidden="true">

                                <div class="modal-dialog modal-lg modal-dialog-centered">

                                    <div class="modal-content">

                                        <form
                                            method="POST"
                                            action="{{ route('role-permission.update', $role) }}">

                                            @csrf

                                            @method('PUT')


                                            {{-- Modal Header --}}
                                            <div class="modal-header">

                                                <h5 class="modal-title">
                                                    Edit Role
                                                </h5>

                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                    aria-label="Close">
                                                </button>

                                            </div>


                                            {{-- Modal Body --}}
                                            <div class="modal-body">


                                                {{-- Role Name --}}
                                                <div class="mb-4">

                                                    <label class="form-label">
                                                        Role Name
                                                    </label>

                                                    <input
                                                        type="text"
                                                        name="name"
                                                        class="form-control"
                                                        value="{{ $role->name }}"
                                                        required>

                                                </div>


                                                {{-- =================================================
                                                    Grouped Permissions
                                                ================================================== --}}

                                                <div>

                                                    <label class="form-label">
                                                        Permissions
                                                    </label>

                                                    <p class="text-muted small mb-4">
                                                        Select the permissions that this role should have.
                                                    </p>


                                                    @forelse ($groupedPermissions as $resource => $resourcePermissions)


                                                        {{-- Resource Card --}}
                                                        <div class="card border shadow-none mb-3">


                                                            {{-- Resource Header --}}
                                                            <div class="card-header py-3">

                                                                <div class="d-flex align-items-center">

                                                                    <div class="avatar avatar-sm me-3">

                                                                        <span class="avatar-initial rounded bg-label-primary">

                                                                            <i class="bx bx-folder"></i>

                                                                        </span>

                                                                    </div>

                                                                    <div>

                                                                        <h6 class="mb-0 text-capitalize">
                                                                            {{ $resource }}
                                                                        </h6>

                                                                        <small class="text-muted">

                                                                            {{ $resourcePermissions->count() }}

                                                                            {{ $resourcePermissions->count() === 1 ? 'permission' : 'permissions' }}

                                                                        </small>

                                                                    </div>

                                                                </div>

                                                            </div>


                                                            {{-- Resource Permissions --}}
                                                            <div class="card-body">

                                                                <div class="row">

                                                                    @foreach ($resourcePermissions as $permission)

                                                                        @php

                                                                            $parts = explode('.', $permission->name, 2);

                                                                            $action = $parts[1] ?? $permission->name;

                                                                        @endphp


                                                                        <div class="col-md-6 col-lg-3 mb-3">

                                                                            <div class="form-check">

                                                                                <input
                                                                                    type="checkbox"
                                                                                    class="form-check-input"
                                                                                    name="permissions[]"
                                                                                    value="{{ $permission->id }}"
                                                                                    id="permission_edit_{{ $role->id }}_{{ $permission->id }}"
                                                                                    {{ $role->permissions->contains('id', $permission->id) ? 'checked' : '' }}>


                                                                                <label
                                                                                    class="form-check-label"
                                                                                    for="permission_edit_{{ $role->id }}_{{ $permission->id }}">

                                                                                    {{ ucfirst($action) }}

                                                                                </label>

                                                                            </div>

                                                                        </div>

                                                                    @endforeach

                                                                </div>

                                                            </div>

                                                        </div>

                                                    @empty

                                                        <div class="alert alert-warning">

                                                            <i class="bx bx-info-circle me-1"></i>

                                                            No permissions are available.

                                                        </div>

                                                    @endforelse

                                                </div>

                                            </div>


                                            {{-- Modal Footer --}}
                                            <div class="modal-footer">

                                                <button
                                                    type="button"
                                                    class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">

                                                    Close

                                                </button>


                                                <button
                                                    type="submit"
                                                    class="btn btn-primary">

                                                    <i class="bx bx-save me-1"></i>

                                                    Save Changes

                                                </button>

                                            </div>

                                        </form>

                                    </div>

                                </div>

                            </div>


                            {{-- =====================================================
                                DELETE ROLE MODAL
                            ====================================================== --}}

                            <div
                                class="modal fade"
                                id="deleteRoleModal{{ $role->id }}"
                                tabindex="-1"
                                aria-hidden="true">

                                <div class="modal-dialog modal-dialog-centered">

                                    <div class="modal-content">

                                        <form
                                            method="POST"
                                            action="{{ route('role-permission.destroy', $role) }}">

                                            @csrf

                                            @method('DELETE')


                                            {{-- Header --}}
                                            <div class="modal-header">

                                                <h5 class="modal-title">
                                                    Delete Role
                                                </h5>

                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                    aria-label="Close">
                                                </button>

                                            </div>


                                            {{-- Body --}}
                                            <div class="modal-body">

                                                <div class="text-center">

                                                    <div class="avatar avatar-xl mx-auto mb-3">

                                                        <span class="avatar-initial rounded bg-label-danger">

                                                            <i class="bx bx-trash fs-3"></i>

                                                        </span>

                                                    </div>


                                                    <h5>
                                                        Delete "{{ $role->name }}"?
                                                    </h5>


                                                    <p class="text-muted mb-0">

                                                        This role will be permanently deleted.<br>

                                                        Its permission assignments will also be removed.

                                                    </p>

                                                </div>

                                            </div>


                                            {{-- Footer --}}
                                            <div class="modal-footer">

                                                <button
                                                    type="button"
                                                    class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">

                                                    Cancel

                                                </button>


                                                <button
                                                    type="submit"
                                                    class="btn btn-danger">

                                                    <i class="bx bx-trash me-1"></i>

                                                    Delete Role

                                                </button>

                                            </div>

                                        </form>

                                    </div>

                                </div>

                            </div>

                        @endif

                    @empty

                        <tr>

                            <td colspan="4">

                                <div class="text-center py-5">

                                    <i class="bx bx-group fs-1 text-muted"></i>

                                    <h6 class="mt-3">
                                        No roles found
                                    </h6>

                                    <p class="text-muted mb-0">
                                        Create a role to get started.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- =========================================================
        ALL PERMISSIONS
    ========================================================== --}}

    <div class="card">

        <div class="card-header">

            <h5 class="mb-1">
                Permissions
            </h5>

            <p class="text-muted mb-0">
                All permissions registered with Spatie.
            </p>

        </div>


        <div class="table-responsive text-nowrap">

            <table class="table">

                <thead>

                    <tr>

                        <th>
                            Resource
                        </th>

                        <th>
                            Action
                        </th>

                        <th>
                            Permission
                        </th>

                        <th>
                            Guard
                        </th>

                    </tr>

                </thead>


                <tbody class="table-border-bottom-0">

                    @forelse ($permissions as $permission)

                        @php

                            $parts = explode('.', $permission->name, 2);

                            $resource = $parts[0] ?? $permission->name;

                            $action = $parts[1] ?? '';

                        @endphp


                        <tr>

                            {{-- Resource --}}
                            <td>

                                <div class="d-flex align-items-center">

                                    <div class="avatar avatar-sm me-3">

                                        <span class="avatar-initial rounded bg-label-primary">

                                            <i class="bx bx-folder"></i>

                                        </span>

                                    </div>


                                    <span class="fw-semibold text-capitalize">

                                        {{ $resource }}

                                    </span>

                                </div>

                            </td>


                            {{-- Action --}}
                            <td>

                                <span class="badge bg-label-success">

                                    {{ ucfirst($action) }}

                                </span>

                            </td>


                            {{-- Full Permission Name --}}
                            <td>

                                <code>
                                    {{ $permission->name }}
                                </code>

                            </td>


                            {{-- Guard --}}
                            <td>

                                <span class="badge bg-label-secondary">

                                    {{ $permission->guard_name }}

                                </span>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="4">

                                <div class="text-center py-5">

                                    <i class="bx bx-key fs-1 text-muted"></i>

                                    <h6 class="mt-3">
                                        No permissions found
                                    </h6>

                                    <p class="text-muted mb-0">
                                        No Spatie permissions have been created yet.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- =========================================================
        ADD ROLE MODAL
    ========================================================== --}}

    <div
        class="modal fade"
        id="addRoleModal"
        tabindex="-1"
        aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-centered">

            <div class="modal-content">

                <form
                    method="POST"
                    action="{{ route('role-permission.store') }}">

                    @csrf


                    {{-- Header --}}
                    <div class="modal-header">

                        <h5 class="modal-title">
                            Add New Role
                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                        </button>

                    </div>


                    {{-- Body --}}
                    <div class="modal-body">


                        {{-- Role Name --}}
                        <div class="mb-4">

                            <label class="form-label">
                                Role Name
                            </label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                placeholder="Example: Reservation Manager"
                                value="{{ old('name') }}"
                                required>


                            @error('name')

                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>


                        {{-- =================================================
                            Grouped Permissions
                        ================================================== --}}

                        <div>

                            <label class="form-label">
                                Permissions
                            </label>

                            <p class="text-muted small mb-4">
                                Select the permissions that this role should have.
                            </p>


                            @forelse ($groupedPermissions as $resource => $resourcePermissions)


                                {{-- Resource Card --}}
                                <div class="card border shadow-none mb-3">


                                    {{-- Resource Header --}}
                                    <div class="card-header py-3">

                                        <div class="d-flex align-items-center">

                                            <div class="avatar avatar-sm me-3">

                                                <span class="avatar-initial rounded bg-label-primary">

                                                    <i class="bx bx-folder"></i>

                                                </span>

                                            </div>


                                            <div>

                                                <h6 class="mb-0 text-capitalize">

                                                    {{ $resource }}

                                                </h6>


                                                <small class="text-muted">

                                                    {{ $resourcePermissions->count() }}

                                                    {{ $resourcePermissions->count() === 1 ? 'permission' : 'permissions' }}

                                                </small>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- Permissions --}}
                                    <div class="card-body">

                                        <div class="row">

                                            @foreach ($resourcePermissions as $permission)

                                                @php

                                                    $parts = explode('.', $permission->name, 2);

                                                    $action = $parts[1] ?? $permission->name;

                                                @endphp


                                                <div class="col-md-6 col-lg-3 mb-3">

                                                    <div class="form-check">

                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input"
                                                            name="permissions[]"
                                                            value="{{ $permission->id }}"
                                                            id="permission_add_{{ $permission->id }}"
                                                            {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>


                                                        <label
                                                            class="form-check-label"
                                                            for="permission_add_{{ $permission->id }}">

                                                            {{ ucfirst($action) }}

                                                        </label>

                                                    </div>

                                                </div>

                                            @endforeach

                                        </div>

                                    </div>

                                </div>

                            @empty

                                <div class="alert alert-warning">

                                    <i class="bx bx-info-circle me-1"></i>

                                    No permissions are available.

                                </div>

                            @endforelse


                            @error('permissions')

                                <div class="text-danger mt-2">

                                    {{ $message }}

                                </div>

                            @enderror

                        </div>

                    </div>


                    {{-- Footer --}}
                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">

                            Close

                        </button>


                        <button
                            type="submit"
                            class="btn btn-primary">

                            <i class="bx bx-plus me-1"></i>

                            Create Role

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection