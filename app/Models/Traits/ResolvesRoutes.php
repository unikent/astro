<?php

namespace App\Models\Traits;

use App\Http\Transformers\Api\v1\PageTransformer;
use App\Models\Definitions\Block;
use App\Models\Page;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
		$original_path = $path;
		while(!$page && $path) {
			$path = preg_replace('/\/?[a-z0-9_-]*\/?$/i', '', $path);
			$page = Page::findByHostAndPath($host,$path,$version);
			if($page) {
				$blocks = $page->revision->blocks;
				foreach($blocks as $region => &$sections){
					foreach($sections as &$section) {
						foreach( $section['blocks'] as &$block) {
							$definition = Block::fromDefinitionFile(Block::locateDefinition(Block::idFromNameAndVersion($block['definition_name'], $block['definition_version'])));
							$block_path = substr($original_path, strlen($path));
							if($definition->reroute($block_path,$block,$page)) {
								$page->revision->blocks = $blocks;
								return $page;
							}
						}
					}
				}
				$page = null;
			}
		}
		if ($page) {
			return fractal($page, new PageTransformer(true))
				->parseIncludes($includes)->respond();
		}
		throw new ModelNotFoundException();
	}
}