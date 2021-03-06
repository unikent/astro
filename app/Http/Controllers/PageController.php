<?php

namespace App\Http\Controllers;

use App\Models\LocalAPIClient;
use App\Models\Page;
use Astro\Renderer\AstroRenderer;
use Astro\Renderer\Base\SingleDefinitionsFolderLocator;
use Astro\Renderer\Engines\TwigEngine;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * Renders pages in draft of published mode for previewing in the editor.
 * @package App\Http\Controllers
 */
class PageController extends Controller
{
	use AuthorizesRequests, DispatchesJobs;

	/**
	 * Render a draft page.
	 * GET /path/to/editor/draft/{host}/{path}
	 * @param string $host - The "real" domain name for the site
	 * @param string $path - The full path to the current page.
	 * @return string - Rendered page HTML with "preview bar" injected.
	 */
	public function draft($host, $path = '', Request $request)
	{
		return $this->renderRoute($host, $path, $request->query(),Page::STATE_DRAFT, [
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
	 * Uses the env setting APP_PREVIEW_URL_PATTERN / config/definitions 'app_preview_url_pattern'
	 * to determine what the replacements should look like.
	 * @param string $input - The HTML version of the current page.
	 * @param \Astro\Renderer\Contracts\Layout $layout - Layout object used by renderer to render the page.
	 * @return string - The page HTML with any links to the live site replaced with links to the draft site.
	 */
	public function replaceLinks($input, $layout)
	{
		$host = $layout->page->site->host;
		$path = $layout->page->site->path;
		$link = config('definitions.app_preview_url_pattern');
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
	public function published($host, $path = '', Request $request)
	{
		return $this->renderRoute($host, $path, $request->query(), Page::STATE_PUBLISHED);
	}

	/**
	 * Use the Renderer to render the Page at a url for a version (draft, published) of a site.
	 * @param string $host - The "real" domain name for the site
	 * @param string $path - The full path to the current page.
	 * @param string $version -  The version of the site to render ('draft', 'published')
	 * @param array $filters - Array of optional callables to post-filter the rendered layout.
	 * @return string - The rendered HTML output.
	 */
	public function renderRoute($host, $path, $params, $version, $filters = [])
	{
		$path = '/' . $path . $this->paramsToQueryString($params);
        $locator = new SingleDefinitionsFolderLocator(
			Config::get('app.definitions_path') ,
			'Astro\Renderer\Base\Block',
			'Astro\Renderer\Base\Layout'
		);
		$api = new LocalAPIClient();
		$engine = new TwigEngine(Config::get('app.definitions_path'), ['debug' => Config::get('app.debug')]);
		// set the global twig variables
		$config = config('definitions');
		// if we are draft, then pass this url replacement pattern defined in our config
		$config['url_replacement_pattern'] = Page::STATE_DRAFT === $version
			? $config['app_preview_url_pattern']
			: $config['app_live_url_pattern'];
		$engine->addGlobal('config',$config);
		// controller
		$astro = new AstroRenderer();
		return $astro->renderRoute($host, $path, $api, $engine, $locator, $version, $filters);
	}

    /**
     * Build a query string from the array of parameters
     * @param array $params - Array of query string parameters and values
     * @return String - Query string preceded by '?' if not empty, otherwise empty string
     */
    public function paramsToQueryString($params)
    {
        $params = http_build_query($params);
        return $params ? ('?' . $params ) : '';
    }
}
