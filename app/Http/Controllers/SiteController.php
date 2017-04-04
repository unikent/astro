<?php
namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Page;
use App\Models\Block;
use Illuminate\Http\Request;

class SiteController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		// get all the sites
		return Page::sites()->get();
	}

	public function show(Page $site)
	{
		// Get site details
		$route = $site->route;
		$pagesInSite = $route->descendants()->get();

		$site->path = $route->path;

		return view('sites.show')->with(['route'=>$route, 'site'=> $site, 'pages'=> $pagesInSite]);
	}

	public function edit(Page $site)
	{
		if(!$site->is_site) die("no access"); // TODO: Move to Middleware?

		// Get site details
		$route = $site->route;
		$pagesInSite = $route->descendants()->get();

		if(!isset($page)){
			$page = $site;
		}

		return view('sites.edit')
			->with(['route'=>$route, 'site'=> $site, 'page'=> $page, 'pages'=> $pagesInSite, 'blocks'=>null]);
	}

	public function structure($id) {

		if($page = Page::find($id))
		{
			$pages = $page->descendantsAndSelf();
		}
		else
		{
			$pages = [];
		}
		return $pages;
	}
}
