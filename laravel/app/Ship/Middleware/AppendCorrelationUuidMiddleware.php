<?php declare(strict_types=1);

namespace App\Ship\Middleware;

use App\Ship\Helpers\Correlation;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppendCorrelationUuidMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$response instanceof JsonResponse) {
            return $response;
        }

        $data = $response->getData(true);

        if (!is_array($data)) {
            return $response;
        }

        $data['meta'] = array_merge($data['meta'] ?? [], [
            'correlation_uuid' => Correlation::getUuid(),
        ]);

        $response->setData($data);

        return $response;
    }
}
