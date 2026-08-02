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
        $incomingUuid = $request->header(Correlation::HEADER_NAME)
            ?? $request->input(Correlation::BODY_KEY);

        try {
            Correlation::resolve($incomingUuid);
        } catch (\InvalidArgumentException $exception) {
            abort(422, $exception->getMessage());
        }

        return $next($request);
    }
}
