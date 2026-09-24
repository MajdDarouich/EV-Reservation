@extends('layouts/blankLayout')

@section('title', 'Reset Password Basic - Pages')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
      <!-- Reset Password -->
      <div class="card px-sm-6 px-0">
        <div class="card-body">
          <!-- Logo -->
          <div class="app-brand justify-content-center mb-6">
            <a href="{{ url('/') }}" class="app-brand-link gap-2">
              <span class="app-brand-logo demo">@include('_partials.macros')</span>
              <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span>
            </a>
          </div>
          <!-- /Logo -->
          <h4 class="mb-1">Reset Password 🔒</h4>
          <p class="mb-6">Enter your new password below</p>

          <form id="formAuthentication" class="mb-6" action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-6">
              <label for="email" class="form-label">Email</label>
              <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                placeholder="Enter your email"
                value="{{ old('email', $email) }}"
                autofocus
                required
              />
              @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-6 form-password-toggle">
              <label class="form-label" for="password">New Password</label>
              <div class="input-group input-group-merge">
                <input
                  type="password"
                  id="password"
                  class="form-control @error('password') is-invalid @enderror"
                  name="password"
                  placeholder="············"
                  required
                />
                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
              </div>
              @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-6 form-password-toggle">
              <label class="form-label" for="password_confirmation">Confirm Password</label>
              <div class="input-group input-group-merge">
                <input
                  type="password"
                  id="password_confirmation"
                  class="form-control"
                  name="password_confirmation"
                  placeholder="············"
                  required
                />
                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
              </div>
            </div>

            <button class="btn btn-primary d-grid w-100">Reset Password</button>
          </form>
          <div class="text-center">
            <a href="{{ url('auth/login-basic') }}" class="d-flex justify-content-center">
              <i class="icon-base bx bx-chevron-left me-1"></i>
              Back to login
            </a>
          </div>
        </div>
      </div>
      <!-- /Reset Password -->
    </div>
  </div>
</div>
@endsection