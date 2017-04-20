<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;

use App\Models\Page;
use App\Models\Block;
use App\Models\Route;
use Illuminate\Http\Request;

class PageController extends ApiController
{

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create(Page $site)
	{
		if(!$site->is_site) die("no access"); // TODO: Move to Middleware?

		// Get site details
		$route = $site->route;
		$pagesInSite = $route->descendants()->get();

		return view('sites.create')->with(['route'=>$route, 'site'=> $site, 'pages'=> $pagesInSite]);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($page_id)
	{
		return $this->success(Page::find($page_id));
	}

}
