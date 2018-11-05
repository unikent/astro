<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function __construct(Request $request)
	{
		if(!request()->wantsJson() && !$this->requestIsPreview($request))
		{
			$this->middleware('auth');
		}
	}

	/**
	 * Is this request a draft / published page view (so shouldn't require auth)
	 * @param Request $request
	 * @return bool
	 */
	public function requestIsPreview(Request $request)
	{
		return $this instanceof PageController;
	}

	public function layout($view, $data = array(), $layout = 'layouts.layout')
	{
		return view($layout, $data)->nest('content', $view, $data);
	}
}
