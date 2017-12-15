<?php
namespace Astro\API\Http\Controllers\Api\v1;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Astro\API\Http\Controllers\Api\ApiController as ApiControllerBase;

class ApiController extends ApiControllerBase
{
	use AuthorizesRequests, DispatchesJobs;
}
