@extends('layouts/contentNavbarLayout')

@section('title', 'Users')

@section('content')
<div class="row">
  <div class="col-md-12">

    {{-- Flash messages --}}
    @if (session('success'))
      <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="card mb-6">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-4">
        <div>
          <h5 class="mb-0">Users</h5>
          <small class="text-body">Manage user accounts and roles</small>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
          <i class="icon-base bx bx-plus me-1"></i> Add New User
        </button>
      </div>

      <div class="card-body border-bottom">
        <form method="GET" action="{{ route('user-management.index') }}" class="row g-4 align-items-center">
          <div class="col-md-4">
            <div class="input-group input-group-merge">
              <span class="input-group-text" id="userSearchAddon"><i class="icon-base bx bx-search"></i></span>
              <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Search by name, email or phone..."
                aria-label="Search..."
                aria-describedby="userSearchAddon"
                value="{{ request('search') }}"
              />
            </div>
          </div>
          <div class="col-md-auto">
            <button type="submit" class="btn btn-primary">Search</button>
            @if (request('search'))
              <a href="{{ route('user-management.index') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
          </div>
        </form>
      </div>

      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead>
            <tr>
              <th>Full Name</th>
              <th>Email</th>
              <th>Phone Number</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse ($users as $user)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-3">
                      <span class="avatar-initial rounded-circle bg-label-primary">
                        {{ strtoupper(substr($user->full_name, 0, 1)) }}
                      </span>
                    </div>
                    <span class="fw-medium">{{ $user->full_name }}</span>
                  </div>
                </td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->phone_number }}</td>
                <td>
                  @if ($user->roles->isNotEmpty())
                    <span class="badge bg-label-primary me-1">{{ ucfirst($user->roles->first()->name) }}</span>
                  @else
                    <span class="badge bg-label-secondary me-1">No Role</span>
                  @endif
                </td>
                <td>
                  <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                      <i class="icon-base bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">
                      <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                        <i class="icon-base bx bx-edit-alt me-1"></i> Edit
                      </a>
                      @unless ($user->hasRole('Super Admin'))
                        <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="document.getElementById('deleteUserForm{{ $user->id }}').submit();">
                          <i class="icon-base bx bx-trash me-1"></i> Delete
                        </a>
                      @endunless
                    </div>
                  </div>

                  {{-- Hidden delete form (super-admin has no delete trigger above, so this never fires for that account) --}}
                  @unless ($user->hasRole('Super Admin'))
                    <form id="deleteUserForm{{ $user->id }}" action="{{ route('user-management.destroy', $user->id) }}" method="POST" class="d-none" onsubmit="return confirm('Delete {{ $user->full_name }}? This cannot be undone.');">
                      @csrf
                      @method('DELETE')
                    </form>
                  @endunless
                </td>
              </tr>

              {{-- Edit User Modal --}}
              <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <form method="POST" action="{{ route('user-management.update', $user->id) }}">
                      @csrf
                      @method('PUT')
                      <div class="modal-header">
                        <h5 class="modal-title">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div class="mb-6">
                          <label for="fullName{{ $user->id }}" class="form-label">Full Name</label>
                          <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="icon-base bx bx-user"></i></span>
                            <input type="text" id="fullName{{ $user->id }}" name="full_name" class="form-control" value="{{ $user->full_name }}" />
                          </div>
                        </div>
                        <div class="mb-6">
                          <label for="email{{ $user->id }}" class="form-label">Email</label>
                          <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="icon-base bx bx-envelope"></i></span>
                            <input type="email" id="email{{ $user->id }}" name="email" class="form-control" value="{{ $user->email }}" />
                          </div>
                        </div>
                        <div class="mb-6">
                          <div class="form-password-toggle">
                            <label class="form-label" for="password{{ $user->id }}">New Password <span class="text-body">(leave blank to keep current)</span></label>
                            <div class="input-group input-group-merge">
                              <input type="password" id="password{{ $user->id }}" name="password" class="form-control" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                              <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                            </div>
                          </div>
                        </div>
                        <div class="mb-6">
                          <label for="phoneNumber{{ $user->id }}" class="form-label">Phone Number</label>
                          <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="icon-base bx bx-phone"></i></span>
                            <input type="text" id="phoneNumber{{ $user->id }}" name="phone_number" class="form-control phone-mask" value="{{ $user->phone_number }}" />
                          </div>
                        </div>
                        <div class="mb-0">
                          <label for="role{{ $user->id }}" class="form-label">Role</label>
                          @if ($user->hasRole('Super Admin'))
                            {{-- Super Admin's role is locked: shown but not editable, and not submitted --}}
                            <select id="role{{ $user->id }}" class="form-select" disabled>
                              <option selected>Super Admin</option>
                            </select>
                            <div class="form-text">The Super Admin role cannot be changed.</div>
                          @else
                            <select id="role{{ $user->id }}" name="role" class="form-select">
                              <option value="">Select role</option>
                              @foreach ($roles as $role)
                                @continue($role->name === 'Super Admin')
                                <option value="{{ $role->name }}" {{ $user->roles->contains('name', $role->name) ? 'selected' : '' }}>
                                  {{ ucfirst($role->name) }}
                                </option>
                              @endforeach
                            </select>
                          @endif
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              {{-- /Edit User Modal --}}

            @empty
              <tr>
                <td colspan="5" class="text-center py-6">
                  @if (request('search'))
                    No users match "{{ request('search') }}".
                  @else
                    No users found.
                  @endif
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if (method_exists($users, 'links'))
        <div class="card-body">
          {{ $users->appends(request()->query())->links() }}
        </div>
      @endif
    </div>
  </div>
</div>

{{-- Add User Modal --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form method="POST" action="{{ route('user-management.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Add New User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-6">
            <label for="fullName" class="form-label">Full Name</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="icon-base bx bx-user"></i></span>
              <input type="text" id="fullName" name="full_name" class="form-control @error('full_name') is-invalid @enderror" placeholder="John Doe" value="{{ old('full_name') }}" />
            </div>
            @error('full_name')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-6">
            <label for="email" class="form-label">Email</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="icon-base bx bx-envelope"></i></span>
              <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="john.doe@example.com" value="{{ old('email') }}" />
            </div>
            @error('email')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-6">
            <div class="form-password-toggle">
              <label class="form-label" for="password">Password</label>
              <div class="input-group input-group-merge">
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
              </div>
            </div>
            @error('password')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-6">
            <label for="phoneNumber" class="form-label">Phone Number</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="icon-base bx bx-phone"></i></span>
              <input type="text" id="phoneNumber" name="phone_number" class="form-control phone-mask @error('phone_number') is-invalid @enderror" placeholder="09********" value="{{ old('phone_number') }}" />
            </div>
            @error('phone_number')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-0">
            <label for="role" class="form-label">Role</label>
            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror">
              <option value="">Select role</option>
              @foreach ($roles as $role)
                @continue($role->name === 'Super Admin')
                <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                  {{ ucfirst($role->name) }}
                </option>
              @endforeach
            </select>
            @error('role')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Add User</button>
        </div>
      </form>
    </div>
  </div>
</div>
{{-- /Add User Modal --}}
@endsection