<?php

namespace App\Models;

use Astro\Renderer\API\Exception\APIErrorException;
use Astro\Renderer\API\Data\PageData;
use Astro\Renderer\API\Data\RouteData;
use App\Models\Traits\ResolvesRoutes;
use Illuminate\Support\Facades\Validator;
use App\Models\APICommands\CreateSite;
use App\Models\APICommands\AddPage;
use Auth;

/**
 * Prototyping
 * To preview draft / live pages in the editor, we want a version of the renderer without caching, but which
 * has the extra ability to view pages / routes in various states.
 * This is an implementation of the api client that accesses the data using the same laravel code as the API
 * avoiding extra http requests, etc.
 * @package App\Models
 */

class LocalAPIClient implements \Astro\Renderer\Contracts\APIClient
{
    use ResolvesRoutes;

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

    /**
     * Run a App\Models\Contracts\APICommand.
     * @param string $class The name of the command class.
     * @param array $data
     * @return \Illuminate\Validation\Validator
     */
    public function execute($class, array $data)
    {
        $command = new $class();
        $data = collect($data);
        $validator = Validator::make($data->toArray(), $command->rules($data, Auth::user()));
        $validator->setCustomMessages($command->messages($data,Auth::user()));
        if($validator->fails()){
            return $validator;
        }else{
            return $command->execute($data,Auth::user());
        }
    }

    /**
     * Create a new site.
     * @param int $publishing_group_id ID of the publishing group for the new site.
     * @param $name Name for the new site.
     * @param $host Hostname for the new site.
     * @param $path Path for the new site.
     * @param $layout_name Default layout name to use for this site.
     * @param $layout_version Version of the layout to use for this site.
     * @param array $options Other options.
     */
    public function createSite($publishing_group_id, $name, $host, $path, $layout_name, $layout_version, $options = [])
    {
        return $this->execute(CreateSite::class, [
            'publishing_group_id' => $publishing_group_id,
            'name' => $name,
            'host' => $host,
            'path' => $path,
            'layout_name' => $layout_name,
            'layout_version' => $layout_version,
            'options' => $options
        ]);
    }

    /**
     * Adds a subpage to a site.
     * @param int $site_id
     * @param int $parent_id
     * @param int|null $after_id
     * @param string $slug
     * @param string $layout_name
     * @param int $layout_version
     * @param array $options
     * @return string json
     * @throws
     */
    public function addPage($site_id, $parent_id, $after_id, $slug, $layout_name, $layout_version, $title)
    {
        return $this->execute(AddPage::class, [
            'parent_id' => $parent_id,
            'after_id' => $after_id,
            'slug' => $slug,
            'layout_name' => $layout_name,
            'layout_version' => $layout_version,
            'title' => $title
        ]);
    }

    public function renamePage($page_id, $new_slug)
    {
        return $this->execute(RenamePage::class, [
            'page_id' => $page_id,
            'slug' => $new_slug,
        ]);
    }

    public function deletePage($id)
    {
        return $this->execute( DeletePage::class, [
            'page_id' => $id
        ]);
    }

    public function updatePageContent()
    {

    }

    public function movePage()
    {

    }

    public function publishPage()
    {

    }

    public function unpublishPage()
    {

    }

    public function copyPage()
    {

    }

    public function updateSite()
    {

    }

    public function deleteSite()
    {

    }

    public function updatePage()
    {

    }
}
