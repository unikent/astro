<?php

namespace App\Http\Controllers;

use App\Models\Definitions\Layout;
use App\Models\LocalAPIClient;
use App\Models\Page;
use Astro\Renderer\AstroRenderer;
use Astro\Renderer\Base\SingleDefinitionsFolderLocator;
use Astro\Renderer\Contracts\Locator;
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
		parent::__construct();
		$this->middleware('auth');
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
		return $this->renderRoute($host, $path, Page::STATE_DRAFT, [
			[$this, 'addPreviewBar'],
			[$this, 'replaceLinks']
		]);
	}

	/**
	 * Prepend a notification bar at the top of the page.
	 * @param string $input - Rendered HTML page
	 * @param \Astro\Renderer\Contracts\Layout $layout - The Layout object within the renderer.
	 * @return string - The page HTML with a preview notification bar prepended
	 */
	public function addPreviewBar($input, $layout)
	{
		$preview_bar = file_get_contents(resource_path('views/components/preview-bar.html'));
		return preg_replace('/(<body[^>]*>)/is', '$1' . $preview_bar, $input, 1);
	}

	/**
	 * Replace links to live site with links to the draft preview site.
	 * Uses the env setting APP_PREVIEW_URL_PATTERN to determine what the replacements should look like.
	 * @param string $input - The HTML version of the current page.
	 * @param \Astro\Renderer\Contracts\Layout $layout - Layout object used by renderer to render the page.
	 * @return string - The page HTML with any links to the live site replaced with links to the draft site.
	 */
	public function replaceLinks($input, $layout)
	{
		$host = $layout->page->site->host;
		$path = $layout->page->site->path;
		$link = getenv('APP_PREVIEW_URL_PATTERN');
		$regex = '#(?P<html_to_keep><a\s[^>]*?href=")http[s]?://' . $host . $path . '(?P<page_path>[^"]+)"#i';
		return preg_replace_callback($regex, function($matches) use($host, $path, $link) {
				return $matches['html_to_keep'] . str_replace(['{domain}', '{path}'], [$host, $path . $matches['page_path']], $link) . '"';
			},
			$input
		);
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
	 * @param array $filters - Array of optional callables to post-filter the rendered layout.
	 * @return string - The rendered HTML output.
	 */
	public function renderRoute($host, $path, $version, $filters = [])
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
		return $astro->renderRoute($host, $path, $api, $engine, $locator, $version, $filters);
	}
}
