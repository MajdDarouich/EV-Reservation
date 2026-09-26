@extends('layouts/blankLayout')

@section('title', 'Login Basic - Pages')

@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        <!-- Register -->
        <div class="card px-sm-6 px-0">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center">
              
                <span class="app-brand-logo demo">@include('_partials.macros')</span>
                <span class="app-brand-text demo text-heading fw-bold">EV Reservation</span>
              
            </div>
            <!-- /Logo -->
            <h4 class="mb-1">Welcome to EV Reservation! 👋</h4>
            <p class="mb-6">Please sign-in to your account and start the adventure</p>

            <form id="formAuthentication" class="mb-6" action="{{ route('login.submit') }}" method="POST">
              @csrf
              @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                  {{ $errors->first() }}
                </div>
              @endif
              <div class="mb-6">
                <label for="email" class="form-label">Email or Username</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email"
                  value="{{ old('email') }}" autofocus />
              </div>
              <div class="mb-6 form-password-toggle">
                <label class="form-label" for="password">Password</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="password" class="form-control" name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                </div>
              </div>
              <div class="mb-8">
                <div class="d-flex justify-content-between">
                  <a href="{{ url('auth/forgot-password') }}">
                    <span>Forgot Password?</span>
                  </a>
                </div>
              </div>
              <div class="mb-6">
                <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
              </div>
            </form>

            
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
