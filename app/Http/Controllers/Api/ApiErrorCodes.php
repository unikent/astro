<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api;

use Illuminate\Http\Response;

class ApiErrorCodes
{

	const
		PRETTY_INFO = [
			400 => 'ASTRO_WRONG_PARAMS',  // Status::HTTP_BAD_REQUEST,
			401 => 'ASTRO_UNAUTHORIZED',  // Status::HTTP_UNAUTHORIZED,
			403 => 'ASTRO_FORBIDDEN',     // Status::HTTP_FORBIDDEN,
			404 => 'ASTRO_NOT_FOUND',     // Status::HTTP_NOT_FOUND,
			405 => 'ASTRO_WRONG_METHOD',  // Status::HTTP_METHOD_NOT_ALLOWED,
			500 => 'ASTRO_INTERNAL_ERROR' // Status::HTTP_INTERNAL_SERVER_ERROR
		],

		ERROR_URL = 'https://github.com/unikent/astro-server/docs/errors';

	// const
	// 	ASTRO_WRONG_ARGS     = 1, // Invalid request, supplied wrong arguments to endpoint
	// 	ASTRO_NOT_FOUND      = 2, // resource, endpoint, thing does not exist
	// 	ASTRO_FORBIDDEN      = 3, // authenticated but has no access
	// 	ASTRO_UNAUTHORIZED   = 4, // not authenticated but should be
	// 	ASTRO_NOT_ALLOWED    = 5, // HTTP method not allowed here
	// 	ASTRO_INTERNAL_ERROR = 6, // some internal/unknown error

	// HTTP_OK = 200;
	// HTTP_CREATED = 201;
	// HTTP_ACCEPTED = 202; // image resize?

	// HTTP_MOVED_PERMANENTLY = 301;
	// HTTP_FOUND = 302;
	// HTTP_SEE_OTHER = 303;
	// HTTP_NOT_MODIFIED = 304;
	// HTTP_TEMPORARY_REDIRECT = 307;
	// HTTP_PERMANENTLY_REDIRECT = 308;

	// HTTP_BAD_REQUEST = 400;
	// HTTP_UNAUTHORIZED = 401;
	// HTTP_FORBIDDEN = 403;
	// HTTP_NOT_FOUND = 404;
	// HTTP_METHOD_NOT_ALLOWED = 405;
	// HTTP_NOT_ACCEPTABLE = 406;

	// HTTP_GONE = 410;

	// HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
	// HTTP_REQUEST_URI_TOO_LONG = 414;
	// HTTP_UNSUPPORTED_MEDIA_TYPE = 415;

	// HTTP_UNPROCESSABLE_ENTITY = 422;

	// HTTP_TOO_MANY_REQUESTS = 429;
	// HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;

	// HTTP_INTERNAL_SERVER_ERROR = 500;
	// HTTP_BAD_GATEWAY = 502;
	// HTTP_SERVICE_UNAVAILABLE = 503;
	// HTTP_GATEWAY_TIMEOUT = 504;
	// HTTP_INSUFFICIENT_STORAGE = 507;
}