<?php

namespace Astro\API\Models\Traits;

use Astro\API\Transformers\Api\v1\PageTransformer;
use Astro\API\Models\Page;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Trait ResolvesRoutes
 * Resolves a Route based on a path.
 * @package Astro\API\Models\Traits
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

		if ($page) {
			return fractal($page, new PageTransformer(true))
				->parseIncludes($includes)->respond();
		}
		return null;

	}
}