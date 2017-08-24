<?php

namespace App\Models;

use App\Models\APICommands\ListSites;
use App\Models\APICommands\PublishPage;
use App\Models\APICommands\UpdateContent;
use App\Models\APICommands\DeletePage;
use App\Models\APICommands\MovePage;
use App\Models\APICommands\UpdatePage;
use Astro\Renderer\API\Exception\APIErrorException;
use Astro\Renderer\API\Data\PageData;
use Astro\Renderer\API\Data\RouteData;
use Astro\Renderer\Contracts\APIClient;
use App\Models\Traits\ResolvesRoutes;
use Illuminate\Support\Collection;
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

class LocalAPIClient implements APIClient
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

    public function getRouteDefinition($host, $path)
    {
        $json = $this->resolveRoute($host, $path);
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
     * Get the sites available to the current user.
     * @return Validator|Collection
     */
    public function getSites()
    {
        return $this->execute( ListSites::class, [
        ]);
    }

    /**
     * Create a new site.
     * @param int $publishing_group_id ID of the publishing group for the new site.
     * @param string $name Name for the new site.
     * @param string $host Hostname for the new site.
     * @param string $path Path for the new site.
     * @param array $homepage_layout layout to use for the homepage for this site. [ 'name' => '...', 'version' => '...']
     * @param array $options Other options.
     * @return Site|Validator
     */
    public function createSite($publishing_group_id, $name, $host, $path, $homepage_layout, $options = [])
    {
        return $this->execute(CreateSite::class, [
            'publishing_group_id' => $publishing_group_id,
            'name' => $name,
            'host' => $host,
            'path' => $path,
            'homepage_layout' => $homepage_layout,
            'options' => $options
        ]);
    }

    /**
     * Adds a subpage to a site.
     * @param int $parent_id
     * @param int|null $next_id
     * @param string $slug
     * @param array $layout [ 'name' => 'layout-name', 'version' => 'layout-version']
     * @param array $options
     * @return string json
     * @throws
     */
    public function addPage( $parent_id, $next_id, $slug, $layout, $title)
    {
        return $this->execute(AddPage::class, [
            'parent_id' => $parent_id,
            'next_id' => $next_id,
            'slug' => $slug,
            'layout' => $layout,
            'title' => $title
        ]);
    }

    /**
     * Add a hierarchy of pages to a site.
     * @param int $parent_id
     * @param int $next_id
     * @param array $tree array of pages attributes, each of which may have a children array containing subpage definitions.
     * Required attributes are:
     * - slug
     * - title
     * - layout['name']
     * - layout['version']
     * @return bool True if successful.
     */
    public function addTree($parent_id, $next_id, $tree)
    {
        foreach( $tree as $page ) {
            $added = $this->addPage(
                $parent_id,
                $next_id,
                $page['slug'],
                $page['layout'],
                $page['title']
            );
            if(!empty($page['children'])){
                $this->addTree( $added->id, null, $page['children']);
            }
        }
        return true;
    }

    /**
     * @param $draft_id int The id of the draft page content to update.
     * @param array $regions Array of [region-name] => [block1, block2, etc]
     */
    public function updatePageContent($page_id,$regions)
    {
        return $this->execute(UpdateContent::class, [
           'id' => $page_id,
            'blocks' => $regions
        ]);
    }

    /**
     * Update a page's options
     * If $new_title is null, then the page title will not be updated.
     * Any page settings / options will only be modified if they exist in the options array.
     * Any option set to null may be removed / unset.
     * @param int $page_id The Page ID
     * @param null|string $new_title Updated page title or null to not update.
     * @param null|array $options Optional page settings / options to update if present.
     */
    public function updatePage($page_id, $new_title = null, $options = null)
    {
        return $this->execute(UpdatePage::class, [
            'id' => $page_id,
            'title' => $new_title,
            'options' => $options
        ]);
    }

    /**
     * Deletes the specified Page.
     * @param int $id The ID of the page to delete.
     * @return Validator
     */
    public function deletePage($id)
    {
        return $this->execute( DeletePage::class, [
            'id' => $id
        ]);
    }

    public function renamePage($page_id, $new_slug)
    {
        return $this->execute(RenamePage::class, [
            'id' => $page_id,
            'slug' => $new_slug,
        ]);
    }


    public function movePage($id, $new_parent_id, $next_id = null)
    {
        return $this->execute(MovePage::class, [
            'id' => $id,
            'parent_id' => $new_parent_id,
            'next_id' => $next_id
        ]);
    }

    public function publishPage($id)
    {
        return $this->execute(PublishPage::class, [
           'id' => $id
        ]);
    }

    public function unpublishPage()
    {
        throw new \LogicException('Unpublish Page not yet implemented.');
    }

    public function copyPage()
    {
        throw new \LogicException('Copy Page not yet implemented.');
    }

    public function updateSite()
    {
        throw new \LogicException('Update Site not yet implemented.');
    }

    public function deleteSite()
    {
        throw new \LogicException('Delete site not yet implemented.');
    }

}
