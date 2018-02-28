<?php

namespace App\Models\Traits;

use App\Events\FilterResponseData;
use App\Http\Transformers\Api\v1\PageTransformer;
use App\Models\Definitions\Block;
use App\Models\Page;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Trait ResolvesRoutes
 * Resolves a Route based on a path.
 * @package App\Models\Traits
 */
trait ResolvesRoutes
{
	/**
	 *
	 * @param $site_id
	 * @param string $host - The domain name for the page to return.
	 * @param string $path - The path to the page.
	 * @param string $version - The version of the page to retrieve (draft, published)
	 * @return $this|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|SymfonyResponse
	 */
	public function resolveRoute($host, $path, $version = Page::STATE_DRAFT, $includes = [])
	{
		// Attempt to resolve the Route
		$page = Page::findByHostAndPath($host, $path, $version);
		// Attempt to resolve potential dynamic route
		if(!$page) {
			$page = $this->resolveDynamicRoute($host, $path, $version, $includes);
		}
		if ($page) {
			$response = fractal($page, new PageTransformer(true))
				->parseIncludes($includes)->respond();
			return $response;
		}
		throw new ModelNotFoundException();
	}

	/**
	 * Search recursively up the url for the first page and see if it includes a dynamic block which can handle the route.
	 * @param $host
	 * @param $path
	 * @param $version
	 * @param $includes
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function resolveDynamicRoute($host, $path, $version, $includes)
	{
		$original_path = $path;
		// remove trailing slash
		$path = rtrim($path, '/');
		// if we are already at the root then there won't be any ancestral pages to check
		while($path) {
			$path = substr($path,0, strrpos($path, '/'));
			$page = Page::findByHostAndPath($host,$path,$version);
			if($page) {
				$blocks = $page->revision->blocks;
				foreach($blocks as $region => &$sections){
					foreach($sections as &$section) {
						foreach( $section['blocks'] as &$block) {
							$definition = Block::fromDefinitionFile(Block::locateDefinition(Block::idFromNameAndVersion($block['definition_name'], $block['definition_version'])));
							$block_path = substr($original_path, strlen($path));
							$dynamic_page = $definition->route($block_path,$block,$page, $this);
							if($dynamic_page) {
								return $dynamic_page;
							}
						}
					}
				}
				return null;
			}
		}
		return null;

	}
}