<?php

namespace App\Models\Traits;

use App\Http\Transformers\Api\v1\PageTransformer;
use App\Models\Page;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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
     * @param $path
     * @return $this|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|SymfonyResponse
     */
    public function resolveRoute($host, $path, $version = 'published', $includes = [])
    {
        // Attempt to resolve the Route
        $page = Page::findByHostAndPath($host, $path, $version);

        if($page){
            return fractal($page, new PageTransformer(true))
				->parseIncludes($includes)->respond();
        }
        return null;

    }
}