<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-login-basic');
  }

  public function Login(LoginRequest $request)
  {
    try {

      if (Auth::attempt($request->validated())) {
          $request->session()->regenerate();
          return redirect()->intended();
      }

      return back()->withErrors([
          'email' => 'The provided credentials do not match our records.',
      ]);
    } catch (\Exception $e) {
      return back()->withErrors([
        'error' => 'An error occurred during login. Please try again later.',
    ]);
    }
    
  }

  public function Logout(Request $request)
  {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/auth/login');
  }

  public function currentProfile()
  {
    return view('content.pages.profile', [
      'user' => Auth::user(),
    ]);
  }
}
