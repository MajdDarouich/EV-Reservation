<?php

use App\Http\Middleware\EnsureIsAdminStaff;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->redirectGuestsTo('/auth/login-basic');
    $middleware->alias([
      'role' => RoleMiddleware::class,
      'permission' => PermissionMiddleware::class,
      'role_or_permission' => RoleOrPermissionMiddleware::class,
      'AdminOrStaff' => EnsureIsAdminStaff::class,
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {


    $exceptions->render(function (Throwable $e, Request $request) {
      if ($e instanceof ValidationException) {
          return response()->json([
              'message' => $e->getMessage(),
              'errors' => $e->errors(),
          ], 422);
        }
    });

    $exceptions->render(function (Throwable $e, Request $request) {
      if ($e instanceof AuthorizationException) {
        return response()->json([
              'message' => 'Unauthorized.',
          ], 403);
      }
    });

    $exceptions->render(function (Throwable $e, Request $request){
      if ($e instanceof AuthenticationException) {
        if ($request->expectsJson() || $request->is('api/*')) {
          return response()->json([
            'message' => 'Login required.',
            'error' => 'Unauthenticated.',
            'success' => false,
          ], 401);
        }

        return redirect()->guest(route('login', absolute: false))->withErrors([
          'error' => 'You must be logged in to access this page.',
        ]);
      }
    });


  })->create();