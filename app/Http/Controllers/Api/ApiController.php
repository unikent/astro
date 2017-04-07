<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use League\Fractal\TransformerAbstract;
use Symfony\Component\HttpFoundation\Response as Status;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	protected
		/**
		 * @var int
		 */
		$statusCode = Status::HTTP_OK,

		/**
		 * @var string
		 */
		$statusText,

		/**
		 * @var bool
		 */
		$debug,

		$fractal;

	/**
	 * Set whether debug mode is enabled
	 */
	public function __construct(Manager $fractal)
	{
		$this->debug = \Config::get('app.debug');

		// 	$this->fractal->setRequestedScopes(explode(',', Input::get('embed')));

		// $this->fractal
		// 	->parseIncludes($includes)
		// 	->setSerializer(new JsonApiSerializer());

		// $query->with($this->fractal->getRequestedIncludes());
	}

	/**
	 * {@inheritdoc}
	 */
	protected function formatValidationErrors(Validator $validator)
	{
		$validation = $validator->errors()->toArray();
		$failedRules = $validator->failed();

		$errors = [];

		foreach($validation as $field => $errs) {
			$rule = array_keys($failedRules[$field]);

			if($uploadedFile = request()->file($field))
			{
				if(is_array($uploadedFile) && count($uploadedFile))
				{
					$uploadedFile = $uploadedFile[0];
				}

				$filename = $uploadedFile->getClientOriginalName();
			}

			foreach($errs as $idx => $error) {
				$code = 'ERR-VALIDATION-' . strtoupper($rule[$idx]);
				$message = isset($filename) ?
					str_replace($field, ' file "' . $filename . '"', $error) :
					$error;

				$errors[] = [
					'type'    => 'Invalid input given',
					'code'    => $code,
					'field'   => $field,
					'message' => $message,
					'href'    => '/validation/#' . $code
				];
			}
		}

		return [
			'errors' => $errors
		];
	}

	public function getStatusCode()
	{
		return $this->statusCode;
	}

	public function setStatusCode($statusCode)
	{
		$this->statusCode = $statusCode;
		return $this;
	}

	protected function success($data = [], $message = '')
	{
		if(empty($data)) {
			return;
		}

		return $this->respondWithArray([
			'data' => $data
		]);
	}

	protected function respondWithItem($item, TransformerAbstract $transformer)
	{
		$resource = new Item($item, $transformer);

		$rootScope = $this->fractal->createData($resource);

		return $this->respondWithArray($rootScope->toArray());
	}

	protected function respondWithCollection($collection, TransformerAbstract $transformer)
	{
		$resource = new Collection($collection, $transformer);

		$rootScope = $this->fractal->createData($resource);

		return $this->respondWithArray($rootScope->toArray());
	}

	protected function respondWithArray(array $array, array $headers = [])
	{
		$mimeType = \current(request()->getAcceptableContentTypes());

		switch($mimeType)
		{
			case 'application/json':
				$contentType = 'application/json';
				$content = json_encode($array);
				break;

			default:
				$contentType = 'application/json';
				$status = Status::HTTP_UNSUPPORTED_MEDIA_TYPE;

				$content = [
					'error' => [
						'code'      => $status,
						'http_code' => $status,
						'message'   => Status::$statusTexts[$status],
					]
				];
		}

		return new Response(
			$content,
			$this->statusCode,
			array_merge($headers, ['Content-Type' => $contentType])
		);
	}

	protected function error($message, $errorCode)
	{
		if($this->statusCode === Status::HTTP_OK)
		{
			trigger_error(
				'Status code should not be set to 200 when an error occurs.',
				E_USER_WARNING
			);
		}

		return $this->respondWithArray([
			'error' => [
				'code' => $errorCode,
				'http_code' => $this->statusCode,
				'message' => $message,
			]
		]);
	}

	public function errorWithCode(
		$code = Status::HTTP_INTERNAL_SERVER_ERROR,
		$message = ''
	)
	{
		return $this
			->setStatusCode($code)
			->error(
				empty($message) ? Status::$statusTexts[$code] : $message,
				$code
			);
	}

	public function errorForbidden($message = 'Forbidden')
	{
		return $this->errorWithCode(
			Status::HTTP_FORBIDDEN, $message
		);
	}

	public function errorInternal($message = '')
	{
		return $this->errorWithCode(
			Status::HTTP_INTERNAL_SERVER_ERROR, $message
		);
	}

	public function errorNotFound($message = '')
	{
		return $this->errorWithCode(
			Status::HTTP_NOT_FOUND, $message
		);
	}

	public function errorUnauthorized($message = '')
	{
		return $this->errorWithCode(
			Status::HTTP_UNAUTHORIZED, $message
		);
	}

	public function errorWrongArgs($message = '')
	{
		return $this->errorWithCode(
			Status::HTTP_BAD_REQUEST, $message
		);
	}
}
