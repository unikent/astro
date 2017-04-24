<?php
namespace Tests\Unit\Http\Requests;

use Mockery;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\Unit\Http\HttpTestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

abstract class RequestTestCase extends HttpTestCase
{

    protected function makeRequest($method = 'POST', $parameters = [], $cookies = [], $files = [], $server = [], $content = null, $uri = '/'){
        // This is the same approach used in ->call, present in base Test Case via MakesHttpRequests trait,
        // see: https://github.com/laravel/framework/blob/5.4/src/Illuminate/Foundation/Testing/Concerns/MakesHttpRequests.php#L222
        $symfonyRequest = SymfonyRequest::create(
            $this->prepareUrlForRequest($uri), $method, $parameters,
            $cookies, $files, array_replace($this->serverVariables, $server), $content
        );

        // Convert to a Laravel Request
        $request = static::$requestClass::createFromBase($symfonyRequest);

        // Ensure that the container is set appropriately
        $request->setContainer($this->app);

        return $request;
    }

    protected function mockRequest($method = 'POST', $parameters = [], $cookies = [], $files = [], $server = [], $content = null, $uri = '/'){
        $request = $this->makeRequest($method, $parameters, $cookies, $files, $server, $content, $uri);
        return Mockery::mock($request)->makePartial();
    }

    protected function mockRoute(Request $request, $parameters = []){
        $route = Mockery::mock(Route::class)->makePartial();
        $route->parameters = $parameters;

        $request->setRouteResolver(function() use($route) {
            return $route;
        });

        return $route;
    }

}
