<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Handler extends ExceptionHandler
{
	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		\Illuminate\Auth\AuthenticationException::class,
		\Illuminate\Auth\Access\AuthorizationException::class,
		\Symfony\Component\HttpKernel\Exception\HttpException::class,
		// \Illuminate\Database\Eloquent\ModelNotFoundException::class,
		\Illuminate\Session\TokenMismatchException::class,
		\Illuminate\Validation\ValidationException::class,
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $exception
	 * @return void
	 */
	public function report(Exception $exception)
	{
		parent::report($exception);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $exception
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $exception)
	{
		if($request->route()){
			$action = $request->route()->getAction();
			$prefix = $action['prefix'];
		} else {
			$prefix = null;
		}

		switch($prefix){
			case 'api/v1':
				$classname = substr(strrchr(get_class($exception), '\\'), 1);;

				switch(get_class($exception)){
					case 'App\Exceptions\DefinitionNotFoundException':
					case 'Illuminate\Database\Eloquent\ModelNotFoundException':
						return response()->json([ 'errors' => [[ 'message' => 'Not found', 'reason' => $classname ]] ], 404);
						break;

					default:
						return response()->json([ 'errors' => [[
							'message' => $classname,
							'reason' => $classname,
							'trace' => $exception->getTraceAsString(),
						]] ], 500);
						break;
				}

				break;

			default:
				return parent::render($request, $exception);
				break;
		}

	}

	/**
	 * Convert an authentication exception into an unauthenticated response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Illuminate\Auth\AuthenticationException  $exception
	 * @return \Illuminate\Http\Response
	 */
	protected function unauthenticated($request, AuthenticationException $exception)
	{
		if($request->expectsJson())
		{
			// TODO: update this to use fractal and match general API output
			return response()->json([
				'errors' => 'Unauthenticated.'
			], 401);
		}

		return redirect()->guest('auth/login');
	}
}
