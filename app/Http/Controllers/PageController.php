<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;

use App\Models\Page;
use App\Models\Block;
use App\Models\Route;
use Illuminate\Http\Request;

class PageController extends ApiController
{

	public function index() {
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create(Page $site)
	{
		if(!$site->is_site) die("no access");

		// Get site details
		$route = $site->route;
		$pagesInSite = $route->descendants()->get();

		return view('sites.create')->with(['route'=>$route, 'site'=> $site, 'pages'=> $pagesInSite]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{

		$page = new Page;

		$page->fill($request->all());

		if($request->parent)
		{
			$page->parent = $request->parent;
		}
		else
		{
			$page->root = true;
		}

		$page->options = '{}';

		try
		{
			$page->save();
		}
		catch(Exception $e)
		{
			return $this->returnError(500, 'Could not save page');
		}

		return $this->success($page);
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



	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Page $site, Page $page)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update($page_id, Request $request)
	{
		$page = Page::find($page_id);
		// update
		$page->saveBlocks(json_decode($request->getContent(), true));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		//
	}
}
