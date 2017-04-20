<?php
namespace Tests\Unit\Http\Controllers\Api;

use Gate;
use App\Models\User;
use Tests\Unit\Http\HttpTestCase;
use Illuminate\Auth\Access\AuthorizationException;

abstract class ApiControllerTestCase extends HttpTestCase {

    public function authenticated($user = null){
        $user = $user ?: factory(User::class)->make();
        $this->be($user, 'api');

        return $user;
    }


    public function authorized(){
        Gate::shouldReceive('authorize');
        Gate::shouldReceive('allows')->andReturn(true);
    }


    public function unauthorized(){
        Gate::shouldReceive('allows')->andThrow(AuthorizationException::class);
        Gate::shouldReceive('authorize')->andThrow(AuthorizationException::class);
    }


    public function authenticatedAndAuthorized($user = null){
        $this->authenticated($user);
        $this->authorized();
    }


    public function authenticatedAndUnauthorized($user = null){
        $this->authenticated($user);
        $this->unauthorized();
    }


    public function action($method, $action, $wildcards = [], $parameters = [], $cookies = [], $files = [], $server = [], $content = null){
        $action = str_replace('App\Http\Controllers\\', '', $action);
        $uri = $this->app['url']->action($action, $wildcards, true);
        return $this->response = $this->call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

}
