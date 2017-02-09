<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function __construct()
	{
		if(!request()->wantsJson())
		{
			$this->middleware('auth');
		}
	}

	public function returnError($code, $message)
	{
		return response()->json([
			'success'   => 'false',
			'message'   => $message,
			'data'      => []
		], $code);
	}
	public function returnSuccess($data = [])
	{
		return response()->json([
			'success'   => 'true',
			'message'   => '',
			'data'      => $data
		], '200');
	}

	public function layout($view, $data = array(), $layout = 'layouts.layout')
	{
		return view($layout, $data)->nest('content', $view, $data);
	}
}
