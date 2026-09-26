@extends('layouts/contentNavbarLayout')

@section('title', 'My Profile')

@section('content')
  <div class="row">
    <div class="col-md-12">
      <div class="card mb-6">
        <div class="card-body">
          <div class="d-flex align-items-center gap-4">
            <div class="avatar avatar-xl">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="icon-base bx bx-user icon-lg"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-1">{{ $user->full_name }}</h4>
              <p class="mb-2 text-body-secondary">{{ $user->email }}</p>
              <span class="badge bg-label-{{ $user->status->value === 'Active' ? 'success' : 'warning' }}">
                {{ $user->status->value }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Personal Information</h5>
        </div>
        <div class="card-body">
          <div class="row g-6">
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <div class="form-control bg-lighter">{{ $user->full_name }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email Address</label>
              <div class="form-control bg-lighter">{{ $user->email ?: 'Not provided' }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Number</label>
              <div class="form-control bg-lighter">{{ $user->phone_number ?: 'Not provided' }}</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email Verification</label>
              <div class="form-control bg-lighter">
                {{ $user->email_verified_at?->format('M d, Y') ?: 'Not verified' }}
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Verification</label>
              <div class="form-control bg-lighter">
                {{ $user->phone_verified_at?->format('M d, Y') ?: 'Not verified' }}
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Member Since</label>
              <div class="form-control bg-lighter">{{ $user->created_at?->format('M d, Y') }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
