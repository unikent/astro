<?php
namespace App\Http\Controllers\Api\v1;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Api\ApiController as ApiControllerBase;

class ApiController extends ApiControllerBase
{
	use AuthorizesRequests, DispatchesJobs;
}
