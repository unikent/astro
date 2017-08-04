<?php

namespace App\Models\Traits;

use App\Models\Page;
use App\Models\Redirect;
use App\Http\Transformers\Api\v1\PageContentTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Gate;

/**
 * Trait ResolvesRoutes
 * Resolves a Route based on a path.
 * @package App\Models\Traits
 * @deprecated
 */
trait ResolvesRoutes
{
    /**
     *
     * @param $site_id
     * @param $path
     * @return $this|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|SymfonyResponse
     */
    public function resolveRoute($host, $path)
    {
        // Attempt to resolve the Route
        $page = Page::findByHostAndPath($host, $path);

        // If the Route is not found, attempt to find a Redirect
//        if(!$page){
//            $redirect = Redirect::findByPathOrFail($path);
//        }
        if($page && $page->draft){
            return fractal($page, new PageTransformer())->parseIncludes(['draft'])->respond();
        }

/*        if(Gate::allows('read', $resolve)){
            if($resolve->published_page){
                return response($resolve->published_page->bake);
            } else {
                return fractal($resolve->page, new PageContentTransformer)->parseIncludes([ 'blocks', 'active_route' ])->respond();
            }
        }
*/
        return (new SymfonyResponse())->setStatusCode(404);

    }
}