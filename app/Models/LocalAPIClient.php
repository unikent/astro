<?php

namespace App\Models;

use App\Models\APICommands\UpdateContent;
use Astro\Renderer\API\Exception\APIErrorException;
use Astro\Renderer\API\Data\PageData;
use Astro\Renderer\API\Data\RouteData;
use App\Models\Traits\ResolvesRoutes;
use Illuminate\Support\Facades\Validator;
use App\Models\APICommands\CreateSite;
use App\Models\APICommands\AddPage;
use Auth;
use Illuminate\Validation\ValidationException;

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

    private $user = null;

    public function __construct(User $user = null)
    {
        if(null == $user){
            $user = Auth::user();
        }
        $this->user = $user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

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
     * Run an APICommand.
     * @param string $class The name of the command class.
     * @param array $data
     * @return Validator
     * @throws ValidationException
     */
    public function execute($class, array $data)
    {
        $command = new $class();
        $data = collect($data);
        $validator = Validator::make($data->toArray(), $command->rules($data, $this->user));
        $validator->setCustomMessages($command->messages($data,$this->user));
        if($validator->fails()){
            throw new ValidationException($validator);
        }else{
            return $command->execute($data,$this->user);
        }
    }

    /**
     * Create a new site.
     * @param int $publishing_group_id ID of the publishing group for the new site.
     * @param string $name Name for the new site.
     * @param string $host Hostname for the new site.
     * @param string $path Path for the new site.
     * @param string $layout_name Default layout name to use for this site.
     * @param int $layout_version Version of the layout to use for this site.
     * @param array $options Other options.
     */
    public function createSite($publishing_group_id, $name, $host, $path, $default_layout_name, $default_layout_version, $options = [])
    {
        return $this->execute(CreateSite::class, [
            'publishing_group_id' => $publishing_group_id,
            'name' => $name,
            'host' => $host,
            'path' => $path,
            'default_layout_name' => $default_layout_name,
            'default_layout_version' => $default_layout_version,
            'options' => $options
        ]);
    }

    /**
     * Adds a subpage to a site.
     * @param int $site_id
     * @param int $parent_id
     * @param int|null $before_id
     * @param string $slug
     * @param string $layout_name
     * @param int $layout_version
     * @param array $options
     * @return string json
     * @throws
     */
    public function addPage($site_id, $parent_id, $before_id, $slug, $layout_name, $layout_version, $title)
    {
        return $this->execute(AddPage::class, [
            'parent_id' => $parent_id,
            'before_id' => $before_id,
            'slug' => $slug,
            'layout_name' => $layout_name,
            'layout_version' => $layout_version,
            'title' => $title
        ]);
    }

    /**
     * Add a hierarchy of pages to a site.
     * @param int $site_id
     * @param int $parent_id
     * @param int $before_id
     * @param array $tree array of pages attributes, each of which may have a children array containing subpage definitions.
     * Required attributes are:
     * - slug
     * - title
     * - layout_name
     * - layout_version
     * @return bool True if successful.
     */
    public function addTree($site_id, $parent_id, $before_id, $tree)
    {
        foreach( $tree as $page ) {
            $added = $this->addPage(
                $site_id,
                $parent_id,
                $before_id,
                $page['slug'],
                $page['layout_name'],
                $page['layout_version'],
                $page['title']
            );
            if(!empty($page['children'])){
                $this->addTree($site_id, $added->id, null, $page['children']);
            }
        }
        return true;
    }

    /**
     * @param $draft_id int The id of the draft page content to update.
     * @param array $regions Array of [region-name] => [block1, block2, etc]
     */
    public function updatePageContent($draft_id,$regions)
    {
        return $this->execute(UpdateContent::class, [
           'draft_id' => $draft_id,
            'blocks' => $regions
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
