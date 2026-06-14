<?php declare(strict_types=1);

namespace App\Ship\Middleware;

use App\Ship\Helpers\Correlation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitCorrelationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        Correlation::init();

        return $next($request);
    }
}
