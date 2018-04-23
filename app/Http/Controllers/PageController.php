<?php

namespace App\Http\Controllers;

use App\Models\LocalAPIClient;
use App\Models\Page;
use Astro\Renderer\AstroRenderer;
use Astro\Renderer\Base\SingleDefinitionsFolderLocator;
use Astro\Renderer\Engines\TwigEngine;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Config;

/**
 * Renders pages in draft of published mode for previewing in the editor.
 * @package App\Http\Controllers
 */
class PageController extends Controller
{
	use AuthorizesRequests, DispatchesJobs;

	public function __construct()
	{

	}

	/**
	 * Render a draft page.
	 * GET /path/to/editor/draft/{host}/{path}
	 * @param string $host - The "real" domain name for the site
	 * @param string $path - The full path to the current page.
	 * @return string - Rendered page HTML with "preview bar" injected.
	 */
	public function draft($host, $path = '')
	{
		$original_html = $this->renderRoute($host, $path, Page::STATE_DRAFT);
		$preview_bar = file_get_contents(resource_path('views/components/preview-bar.html'));
		return preg_replace('/(<body[^>]*>)/is', '$1' . $preview_bar, $original_html, 1);
	}

	/**
	 * Render a published page.
	 * GET /path/to/editor/published/{host}/{path}
	 * @param string $host - The "real" domain name for the site
	 * @param string $path - The full path to the current page.
	 * @return string - Rendered page HTML
	 */
	public function published($host, $path = '')
	{
		return $this->renderRoute($host, $path, Page::STATE_PUBLISHED);
	}

	/**
	 * Use the Renderer to render the Page at a url for a version (draft, published) of a site.
	 * @param string $host - The "real" domain name for the site
	 * @param string $path - The full path to the current page.
	 * @param string $version -  The version of the site to render ('draft', 'published')
	 * @return string - The rendered HTML output.
	 */
	public function renderRoute($host, $path, $version)
	{
		$path = '/' . $path;
		$locator = new SingleDefinitionsFolderLocator(
			Config::get('app.definitions_path') ,
			'Astro\Renderer\Base\Block',
			'Astro\Renderer\Base\Layout'
		);
		$api = new LocalAPIClient();
		$engine = new TwigEngine(Config::get('app.definitions_path'));

		// controller
		$astro = new AstroRenderer();
		return $astro->renderRoute($host, $path, $api, $engine, $locator, $version);
	}
}
