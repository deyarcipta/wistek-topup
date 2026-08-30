<?php

use App\Http\Middleware\EnsureUserIsMember;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin*') ? '/admin/login' : '/login');

        $middleware->validateCsrfTokens(except: [
            '/callback/duitku',
            '/callback/digiflazz',
        ]);

        $middleware->alias([
            'member' => EnsureUserIsMember::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 403) {
                $user = auth()->user() ?? auth()->guard('web')->user();
                if ($user && $user->isMember() && ($request->is('admin') || $request->is('admin/*'))) {
                    return redirect('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman admin.');
                }
            }
        });
    })->create();
