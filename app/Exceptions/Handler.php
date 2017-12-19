<?php

namespace App\Exceptions;

use Astro\API\Exceptions\DefinitionNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\PostTooLargeException;

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
        $classname = get_class($exception);


		if($request->route())
		{
			$action = $request->route()->getAction();
			$prefix = $action['prefix'];
		}

		if(isset($prefix) && starts_with($prefix, 'api'))
		{
			if($exception instanceof AuthorizationException)
			{
				return $this->formatErrors(
					'Not Authorized', $classname, 403, $exception
				);
			}
			else if($exception instanceof AuthenticationException)
			{
				return $this->formatErrors(
					'Not Authenticated', $classname, 401, $exception
				);
			}
			else if($exception instanceof ValidationException)
			{
				return $this->formatErrors(
					'Invalid input given',
					$exception->validator->errors()->getMessages(),
					422,
					$exception
				);
			}
			else if(
				$exception instanceof DefinitionNotFoundException ||
				$exception instanceof ModelNotFoundException
			)
			{
				return $this->formatErrors(
					'Not Found', $classname, 404, $exception
				);
			}
			else if($exception instanceof PostTooLargeException)
			{
				return $this->formatErrors(
					'This content is too large to upload',
					$classname,
					422,
					$exception
				);
			}

			return $this->formatErrors(
				$exception->getMessage(),
				$classname,
				500,
				$exception
			);
		}

        return parent::render($request, $exception);
    }

	// TODO: replace with fractal?
	protected function formatErrors($message = '', $details = 'Unknown', $code = 500, $e = null)
	{
		$errors = [
			'errors' => [
				[
					'message' => $message,
					'details'  => $details
				]
			]
		];

        // Only include stack trace in debug mode (as could reveal secrets)
        if(in_array($code, [500,404]) && config('app.debug') && isset($e))
        {
            $errors['errors'][0]['trace'] = $e->getTrace();
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