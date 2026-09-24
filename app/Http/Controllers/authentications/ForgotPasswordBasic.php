<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Services\ForgotPasswordService;
use Illuminate\Http\Request;

class ForgotPasswordBasic extends Controller
{
  
  public function __construct(protected ForgotPasswordService $service)
  {
    //
  }

  public function index()
  {
    return view('content.authentications.auth-forgot-password-basic');
  }

  public function sendResetLink(Request $request)
  {
    $data = $request->validate([
      'email' => 'required|email',
    ]);

    return $this->service->sendResetLink($data);
  }
}
