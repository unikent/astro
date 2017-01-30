<?php

namespace App\Http\Controllers;


use App\Models\Page;
use App\Models\Block;
use App\Models\Route;
use Illuminate\Http\Request;

class PageController extends Controller
{

	public function index() {
		if(!isset($_GET['path']))
		{
			return ['error' => 'no path supplied'];
		}

		return Page::findByPath($_GET['path']);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create(Page $site)
	{
		if(!$site->key_page) die("no access");

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
	public function store(Request $request, Page $site)
	{
		// validate
		$this->validate($request, [
			'title' => 'bail|required|max:255'
		]);

		if(!$site->key_page) die("no access");

		if(!isset($page))
		{
			$page = $site;
		}

		// store
		$page = new Page;
		$page->title = $request->input('title');
		$page->options = $request->input('options');
		$page->key_page = 0;
		$page->published = 1;

		$page->save();

		$page_route = new Route;

		// set the slug or generate one if the user hasn't
		$page_route->slug = $request->input('path') == '' ? str_slug($page->title) : $request->input('path');
		$page_route->page_id = $page->id;

		$page_route->parent = $site->route;
		$page_route->save();


		// $page->path = $page->route->path;

		// success message
		$request->session()->flash('alert-success', 'Page was successfully created');

		// Get route details
		$route = $site->route;
		$pagesInSite = $route->descendants()->get();

		return view(
		'sites.edit', ['route'=>$route, 'site'=> $site, 'page'=> $page, 'pages'=> $pagesInSite, 'blocks'=>null]
		);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $page_id
	 * @return \Illuminate\Http\Response
	 */
	public function show($page_id)
	{
		return Page::find($page_id)->getBlockStructure();
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Page $site, Page $page)
	{
		if(!$site->key_page) die("no access");

		// Get site details
		$route = $site->route;
		$pagesInSite = $route->descendants()->get();

		if(!isset($page)){
		$page = $site;
		}
		$page->path = $page->route->path;
		$site->path = $route->path;

		return view('sites.edit')->with(['route'=>$route, 'site'=> $site, 'page'=> $page, 'pages'=> $pagesInSite, 'blocks'=>$site->blocks]);
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
