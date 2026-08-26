<?php declare(strict_types=1);

namespace App\Ship\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateBearerFromQuery
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken()) {
            return $next($request);
        }

        $token = $request->query('access_token') ?? $request->query('token');
        if (is_string($token) && $token !== '') {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}
