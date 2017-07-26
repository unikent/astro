<?php

namespace App\Models\Traits;

use App\Models\Page;
use App\Models\Redirect;
use App\Http\Transformers\Api\v1\PageContentTransformer;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Gate;

/**
 * Trait ResolvesRoutes
 * Resolves a Route based on a path.
 * @package App\Models\Traits
 */
trait ResolvesRoutes
{
    public function resolveRoute($path)
    {
        // Attempt to resolve the Route
        $resolve = Page::findByPath($path);

        // If the Route is not found, attempt to find a Redirect
        if(!$resolve){
            $resolve = Redirect::findByPathOrFail($path);
        }

        if(Gate::allows('read', $resolve)){
            if($resolve->published_page){
                return response($resolve->published_page->bake);
            } else {
                return fractal($resolve->page, new PageContentTransformer)->parseIncludes([ 'blocks', 'active_route' ])->respond();
            }
        }

        return (new SymfonyResponse())->setStatusCode(404);

    }
}