<?php declare(strict_types=1);

namespace App\Ship\Tests\Unit;

use App\Ship\Middleware\AuthenticateBearerFromQuery;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthenticateBearerFromQueryTest extends TestCase
{
    public function test_query_token_middleware_runs_before_authenticate_on_play_route(): void
    {
        $request = Request::create('/api/v1/music/tracks/1/play', 'GET');
        $route = Route::getRoutes()->match($request);
        $middleware = app('router')->gatherRouteMiddleware($route);

        $queryIndex = $this->middlewareIndex($middleware, AuthenticateBearerFromQuery::class);
        $authIndex = $this->middlewareIndex($middleware, Authenticate::class);

        $this->assertNotNull($queryIndex);
        $this->assertNotNull($authIndex);
        $this->assertLessThan($authIndex, $queryIndex);
    }

    public function test_it_copies_access_token_query_into_authorization_header(): void
    {
        $request = Request::create('/v1/music/tracks/1/play', 'GET', [
            'access_token' => 'query-token',
        ]);

        $response = (new AuthenticateBearerFromQuery())->handle($request, function (Request $next) {
            $this->assertSame('Bearer query-token', $next->headers->get('Authorization'));

            return new Response('ok');
        });

        $this->assertSame('ok', $response->getContent());
    }

    public function test_it_does_not_override_existing_bearer_token(): void
    {
        $request = Request::create('/v1/music/tracks/1/play', 'GET', [
            'access_token' => 'query-token',
        ]);
        $request->headers->set('Authorization', 'Bearer header-token');

        (new AuthenticateBearerFromQuery())->handle($request, function (Request $next) {
            $this->assertSame('Bearer header-token', $next->headers->get('Authorization'));

            return new Response('ok');
        });
    }

    /**
     * @param  array<int, string>  $middleware
     */
    private function middlewareIndex(array $middleware, string $class): ?int
    {
        foreach ($middleware as $index => $entry) {
            $name = explode(':', $entry)[0];

            if ($name === $class) {
                return $index;
            }
        }

        return null;
    }
}
