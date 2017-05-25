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
				$classname = substr(strrchr(get_class($exception), '\\'), 1);

				switch(get_class($exception)){
					case 'App\Exceptions\DefinitionNotFoundException':
					case 'Illuminate\Database\Eloquent\ModelNotFoundException':
						return $this->formatErrors(
							'Not Found', $classname, 404, $exception
						);
						break;

					case 'Illuminate\Auth\AuthenticationException':
						return $this->formatErrors(
							'Not Authenticated', $classname, 401, $exception
						);
						break;

					case 'Illuminate\Auth\Access\AuthorizationException':
						return $this->formatErrors(
							'Not Authorized', $classname, 403, $exception
						);
						break;

					case 'Illuminate\Validation\ValidationException':
						return $this->formatErrors(
							'Invalid input given',
							$exception->validator->errors()->getMessages(),
							422,
							$exception
						);
						break;

					default:
						return $this->formatErrors(
							$classname, $classname, 500, $exception
						);
						break;
				}

				break;

			default:
				return parent::render($request, $exception);
				break;
		}

	}

	// TODO: replace with fractal?
	protected function formatErrors($message = '', $reason = 'Unknown', $code = 500, $e = null)
	{
		$errors = [
			'errors' => [
				[
					'message' => $message,
					'reason'  => $reason
				]
			]
		];

		// Only include stack trace in debug mode (as could reveal secrets)
		if($code === 500 && config('app.debug') && isset($e))
		{
			$errors['errors'][0]['trace'] = $e;
		}

		return response()->json($errors, $code);
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
			return $this->formatErrors(
				'Unauthenticated.', 'AuthenticationException', 401, $exception
			);
		}

		return redirect()->guest('auth/login');
	}
}
