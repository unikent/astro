<?php

namespace App\Models;

use App\Exceptions\MethodNotSupportedException;
use App\Models\APICommands\ListSites;
use App\Models\APICommands\PublishPage;
use App\Models\APICommands\UnpublishPage;
use App\Models\APICommands\UpdateContent;
use App\Models\APICommands\DeletePage;
use App\Models\APICommands\MovePage;
use App\Models\APICommands\UpdatePage;
use App\Models\APICommands\UpdatePageSlug;
use App\Models\APICommands\UpdateSite;
use App\Models\APICommands\UpdateSiteUserRole;
use App\Models\APICommands\UpdateSiteUsers;
use Astro\Renderer\API\Exception\APIErrorException;
use Astro\Renderer\API\Data\PageData;
use Astro\Renderer\API\Data\RouteData;
use Astro\Renderer\Contracts\APIClient;
use App\Models\Traits\ResolvesRoutes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use App\Models\APICommands\CreateSite;
use App\Models\APICommands\AddPage;
use App\Models\APICommands\CopyPage;
use Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

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
		if (null == $user) {
			$user = Auth::user();
		}
		$this->user = $user;
	}

	public function setUser(User $user)
	{
		$this->user = $user;
	}

	public function getRouteDefinition($host, $path, $version = 'draft')
	{
		$json = $this->resolveRoute($host, $path, $version, 					[
			'site',
			'ancestors',
			'parent',
			'children',
			'siblings',
			'previous',
			'next'
		]);
		$data = $json ? json_decode($json->content(), true) : null;
		if (null === $data) {
			throw new RouteNotFoundException();//APIErrorException('Non json data returned from API for path "' . $path . '"',APIErrorException::ERR_INVALID_JSON);
		}
		try {
			$page = PageData::fromArray(isset($data['data']) ? $data['data'] : []);
			$slug = $this->slugFromPath($path);
		} catch (\Exception $e) {
			throw new APIErrorException('Invalid data returned from API for path "' . $path . '"', APIErrorException::ERR_INVALID_DATA, $e);
		}
		return $page;
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
		if (false === $last_slash) {
			throw new \InvalidArgumentException($path . ' is not a valid path.');
		}
		return substr($path, $last_slash + 1);
	}

	/**
	 * Run an APICommand.
	 * @param string $class The name of the command class.
	 * @param array $data
	 * @return object
	 * @throws ValidationException
	 */
	public function execute($class, array $data)
	{
		$command = new $class();
		$data = collect($data);
		$validator = Validator::make($data->toArray(), $command->rules($data, $this->user));
		$validator->setCustomMessages($command->messages($data, $this->user));
		if ($validator->fails()) {
			throw new ValidationException($validator);
		} else {
			return $command->execute($data, $this->user);
		}
	}

	/**
	 * Get the sites available to the current user.
	 * @return null|Collection
	 */
	public function getSites($version = 'draft')
	{
		return $this->execute(ListSites::class, [
		]);
	}

	/**
	 * Create a new site.
	 * @param string $name Name for the new site.
	 * @param string $host Hostname for the new site.
	 * @param string $path Path for the new site.
	 * @param array $site_definition - The Site template to use for this site. [ 'name' => '...', 'version' => '...']
	 * @param array $options Other options.
	 * @return Site|object
	 */
	public function createSite($name, $host, $path, $site_definition, $options = [], $create_default_pages = true)
	{
		return $this->execute(CreateSite::class, [
			'name' => $name,
			'host' => $host,
			'path' => $path,
			'site_definition' => $site_definition,
			'options' => $options,
			'create_default_pages' => $create_default_pages
		]);
	}

	/**
	 * Adds a subpage to a site.
	 * @param int $parent_id
	 * @param int|null $next_id
	 * @param string $slug
	 * @param array $layout [ 'name' => 'layout-name', 'version' => 'layout-version']
	 * @param $title
	 * @return string json
	 * @internal param array $options
	 */
	public function addPage($parent_id, $next_id, $slug, $layout, $title)
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
		foreach ($tree as $page) {
			$added = $this->addPage(
				$parent_id,
				$next_id,
				$page['slug'],
				$page['layout'],
				$page['title']
			);
			if (!empty($page['children'])) {
				$this->addTree($added->id, null, $page['children']);
			}
		}
		return true;
	}

	/**
	 * @param $draft_id int The id of the draft page content to update.
	 * @param array $regions Array of [region-name] => [block1, block2, etc]
	 * @return object
	 */
	public function updatePageContent($page_id, $regions)
	{
		return $this->execute(UpdateContent::class, [
			'id' => $page_id,
			'blocks' => $regions
		]);
	}

	/**
	 * Update the role held by specified user on this site.
	 * @param int $site_id - The ID of the site to (un)assign user to.
	 * @param string $username - Username of the user to set a role for.
	 * @param string $role - Name of the role to assign or empty to remove the user from this site.
	 * @return object|Site - The updated Site.
	 */
	public function updateSiteUserRole($site_id, $username, $role = null)
	{
		return $this->execute(UpdateSiteUserRole::class, [
			'id' => $site_id,
			'username' => $username,
			'role' => $role
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
	 * @return object
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
	 * @return object|Page
	 */
	public function deletePage($id)
	{
		return $this->execute(DeletePage::class, [
			'id' => $id
		]);
	}

	public function renamePage($page_id, $new_slug)
	{
		return $this->execute(UpdatePageSlug::class, [
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

	public function unpublishPage($id)
	{
		return $this->execute(UnpublishPage::class, [
			'id' => $id
		]);
	}

	public function copyPage($id, $new_title, $new_slug)
	{
		return $this->execute(CopyPage::class, [
			'id' => $id,
			'new_title' => $new_title,
			'new_slug' => $new_slug
		]);
	}

	/**
	 * Update configuration for the given site
	 * @param integer $id - The unique id of the site to update.
	 * @param array $updates - Array of key => value pairs for the site configuration values to be updated. One or more of:
	 * name => string,
	 * host => string,
	 * path => string,
	 * options => array of site options. Keys with null values will be removed from the site options, only keys that are
	 * present will be updated.
	 * @return Site|object The updated site object.
	 * @throws ValidationException if any errors occur.
	 */
	public function updateSite($id, $updates)
	{
		return $this->execute(UpdateSite::class, array_merge($updates, ['id' => $id]));
	}

	public function deleteSite()
	{
		throw new \LogicException('Delete site not yet implemented.');
	}

	/**
	 * Request the json result for the site/{id}?include=$include&version=$version endpoint
	 * @param $id
	 * @param string $version
	 * @param string $include
	 * @return array representation of site data
	 * @throws MethodNotSupportedException
	 */
	public function getSite($id, $version = 'published', $include = '')
	{
		throw new MethodNotSupportedException('getSite is not supported');
	}
}
