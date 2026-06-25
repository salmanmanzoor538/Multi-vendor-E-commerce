<?php

use App\Http\Middleware\AssignRequestContext;
use App\Http\Middleware\EnsureApprovedVendor;
use App\Http\Middleware\EnsureCartAccess;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\EnsureVendorHasActiveSubscription;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $trustedProxies = env('TRUSTED_PROXIES', '*');
        if (is_string($trustedProxies) && $trustedProxies !== '*') {
            $trustedProxies = array_values(array_filter(array_map('trim', explode(',', $trustedProxies))));
        }

        $middleware->trustProxies(at: $trustedProxies);

        $middleware->validateCsrfTokens(except: [
            'webhooks/stripe',
            'csp/report',
        ]);

        $middleware->prepend(AssignRequestContext::class);
        $middleware->append(SecurityHeaders::class);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'approved.vendor' => EnsureApprovedVendor::class,
            'active.subscription' => EnsureVendorHasActiveSubscription::class,
            'cart.access' => EnsureCartAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport([
            ValidationException::class,
            AuthenticationException::class,
            AuthorizationException::class,
            ThrottleRequestsException::class,
            TokenMismatchException::class,
            ModelNotFoundException::class,
            NotFoundHttpException::class,
        ]);

        $exceptions->context(function (): array {
            $request = request();
            $user = $request?->user();

            return [
                'request_id' => $request?->attributes->get('request_id'),
                'user_id' => $user?->id,
                'vendor_id' => $user?->vendor_id,
                'ip' => $request?->ip(),
            ];
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! ($request->expectsJson() || $request->ajax() || $request->is('api/*'))) {
                return null;
            }

            $status = SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR;
            $code = 'SERVER_ERROR';
            $message = $exception->getMessage();

            if ($exception instanceof ValidationException) {
                $status = SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY;
                $code = 'VALIDATION_ERROR';
                $message = 'The given data was invalid.';
            } elseif ($exception instanceof AuthenticationException) {
                $status = SymfonyResponse::HTTP_UNAUTHORIZED;
                $code = 'UNAUTHENTICATED';
                $message = 'Authentication is required.';
            } elseif ($exception instanceof AuthorizationException) {
                $status = SymfonyResponse::HTTP_FORBIDDEN;
                $code = 'FORBIDDEN';
                $message = 'You are not authorized to perform this action.';
            } elseif ($exception instanceof TokenMismatchException) {
                $status = 419;
                $code = 'SESSION_EXPIRED';
                $message = 'Your session expired. Please refresh the page and try again.';
            } elseif ($exception instanceof ThrottleRequestsException) {
                $status = SymfonyResponse::HTTP_TOO_MANY_REQUESTS;
                $code = 'TOO_MANY_REQUESTS';
                $message = 'Too many requests. Please try again shortly.';
            } elseif ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
                $status = SymfonyResponse::HTTP_NOT_FOUND;
                $code = 'NOT_FOUND';
                $message = 'The requested resource was not found.';
            } elseif ($exception instanceof HttpExceptionInterface) {
                $status = $exception->getStatusCode();
                $code = 'HTTP_'.$status;
            }

            if (! config('app.debug') && $status >= SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR) {
                $message = 'Something went wrong. Please try again later.';
            } elseif ($message === '') {
                $message = SymfonyResponse::$statusTexts[$status] ?? 'Error';
            }

            $payload = [
                'message' => $message,
                'code' => $code,
                'request_id' => (string) $request->attributes->get('request_id'),
            ];

            if ($exception instanceof ValidationException) {
                $payload['errors'] = $exception->errors();
            }

            return response()->json($payload, $status);
        });
    })->create();
