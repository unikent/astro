<?php

namespace App\Models;

use Astro\Renderer\API\Exception\APIErrorException;
use Astro\Renderer\API\Data\PageData;
use Astro\Renderer\API\Data\RouteData;

class LocalAPIClient implements \Astro\Renderer\Contracts\APIClient
{
    use \App\Models\Traits\RouteResolver;

    public function getRouteDefinition($path)
    {
        $json = $this->resolveRoute($path);
        $data = json_decode($json->content(),true);
        if(null === $data){
            throw new APIErrorException('Non json data returned from API for path "' . $path . '"',APIErrorException::ERR_INVALID_JSON);
        }
        try {
            $data['data']['canonical'] = $data['data']['active_route']['path'];
            $page = PageData::fromArray( isset($data['data']) ? $data['data'] : []);
            $slug = $this->slugFromPath($path);
            $route = new RouteData(
                $path,
                $slug,
                $page,
                ($path == $page->canonical)
            );
        } catch( \Exception $e ) {
            throw new APIErrorException( 'Invalid data returned from API for path "' . $path . '"', APIErrorException::ERR_INVALID_DATA, $e);
        }
        return $route;
    }

    /**
     * Retrieve the slug from a path.
     * @param string $path The path to retrieve the slug from.
     * @return string The slug (the part of the path after the last /)
     * @throws \InvalidArgumentException if $path does not contain at least one /
     */
    public function slugFromPath($path)
    {
        $last_slash = strrpos($path, '/');
        if( false === $last_slash){
            throw new \InvalidArgumentException($path . ' is not a valid path.');
        }
        return substr($path, $last_slash+1);
    }
}
