<?php

namespace App\Models;

use App\Exceptions\UnpublishedParentException;
use App\Models\Definitions\Region;
use DB;
use Doctrine\DBAL\Version;
use Exception;
use App\Models\Definitions\Layout as LayoutDefinition;
use Baum\Node as BaumNode;
use Illuminate\Database\Eloquent\Collection;
use League\Fractal\TransformerAbstract;
use App\Models\Definitions\Layout;
/**
 * A Page represents a path in a hierarchical site structure.
 * Each page has a current revision, and each "tree" of pages is scoped by its site_id and version (draft, published, etc).
 *
 * @package App\Models
 */
class Page extends BaumNode
{
	public $table = 'pages';

	public $timestamps = false;

	protected $fillable = [
		'slug',
		'draft_id',
		'parent_id',
		'site_id',
		'version',
		'path',
		'revision_id',
		'created_by',
		'updated_by',
	];

	protected $hidden = [
		'lft',
		'rgt'
	];

	// nested set implementation has a tree for each site_id+version combination.
	protected $scoped = ['site_id', 'version'];

	// The draft state of this page.
	const STATE_NEW = 'new'; // not published
	const STATE_DRAFT = 'draft'; // modified since last published
	const STATE_DELETED = 'deleted'; // deleted since last published
	const STATE_MOVED = 'moved'; // moved since last published
	const STATE_PUBLISHED = 'published'; // not modified since last published
	const STATE_EMPTY = 'empty'; // no draft or published state.


	/**
	 * Create a new Eloquent model instance.
	 *
	 * @param  array $attributes
	 * @return void
	 */
	public function __construct($attributes = [])
	{
		parent::__construct($attributes);

		$this->parent_id = $this->parent_id ?: null;
	}


	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function boot()
	{
		parent::boot();

		static::saving(function ($node) {
			$node->path = $node->generatePath();
		});
	}

	/**
	 * Generate the blocks array for this page.
	 * @return array
	 */
	public function bake()
	{
		$data = [];
		$blocksByRegion = $this->blocks()
			->with('media')
			->orderBy('order')
			->get()
			->groupBy('region_name');
		$this->load('blocks.media');
		foreach ($blocksByRegion as $region_id => $blocks) {
			// need the region definition to get sections in the correct order
			$parts = Region::idToNameAndVersion($region_id);
			$regionDef = Region::fromDefinitionFile(Region::locateDefinition($parts['name'], $parts['version']));
			$sections = $blocks->groupBy('section_name');
			$data[$region_id] = [];
			foreach($regionDef->sections as $section_def){
				$section = ['name' => $section_def['name'], 'blocks' => []];
				if(!empty($sections[$section_def['name']])){
					foreach($sections[$section_def['name']] as $block){
						$block->embedMedia();
						$section['blocks'][] = [
							'id' => $block->id,
							'definition_name' => $block->definition_name,
							'definition_version' => $block->definition_version,
							'region_name' => $block->region_name,
							'section_name' => $block->section_name,
							'fields' => $block->fields,
							'errors' => $block->errors
						];
					}
				}
				$data[$region_id][] = $section;
			}
		}
		return $data;
	}

	/**
	 * Get the published state of the page.
	 * @return string One of 'new', 'draft', 'published'
	 */
	public function getStatusAttribute()
	{
		if(Page::STATE_DRAFT == $this->version){
			$compare_to = $this->publishedVersion();
		}
		else {
			$compare_to = $this->draftVersion();
		}
		if($compare_to){
			if($compare_to->revision_id == $this->revision_id){
				return Page::STATE_PUBLISHED;
			}
			else{
				return Page::STATE_DRAFT;
			}
		}
		else{
			return Page::STATE_NEW;
		}
	}

	/************************************************************************
	 * Relations
	 ************************************************************************/

	/**
	 * The Site that this Page belongs to.
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function site()
	{
		return $this->belongsTo(Site::class, 'site_id');
	}

	/**
	 * The current Revision attached to this Page.
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function revision()
	{
		return $this->belongsTo(Revision::class, 'revision_id');
	}

	/**
	 * The Blocks linked to this Page.
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function blocks()
	{
		return $this->hasMany(Block::class, 'page_id');
	}

	/************************************************************************
	 * Query Scopes
	 ************************************************************************/

	/**
	 * Restrict query to draft version of the site.
	 * @param $query
	 * @return mixed
	 */
	public function scopeDraft($query)
	{
		return $this->scopeVersion($query, self::STATE_DRAFT);
	}

	/**
	 * Restrict query to published version of the site.
	 * @param $query
	 * @return mixed
	 */
	public function scopePublished($query)
	{
		return $this->scopeVersion($query, self::STATE_PUBLISHED);
	}

	/**
	 * Restrict query to specific version of the site.
	 * @param $query
	 * @return mixed
	 */
	public function scopeVersion($query, $version)
	{
		return $query->where('version', $version);
	}

	/**
	 * Restrict query to specific site.
	 * @param $query
	 * @param $site_id
	 * @return mixed
	 */
	public function scopeforSite($query, $site_id)
	{
		return $query->where('site_id', $site_id);
	}

	/**
	 * Restrict query to page on site with specific path.
	 * @param $query
	 * @param $site_id
	 * @param $path
	 * @return mixed
	 */
	public static function scopeForSiteAndPath($query, $site_id, $path)
	{
		return $query->where('site_id', $site_id)
			->where('path', $path);
	}


	/**************************************************************************
	 * Utility Methods
	 */

	/**
	 * Get the published version of this page.
	 * @return Page The published version of this page if it exists (which may be this Page)
	 */
	public function publishedVersion()
	{
		if (Page::STATE_PUBLISHED == $this->version) {
			return $this;
		}
		return Page::published()
			->forSiteAndPath($this->site_id, $this->path)
			->first();
	}

	/**
	 * Get the draft version of this page.
	 * @return Page The draft version of this page if it exists (which may be this Page)
	 */
	public function draftVersion()
	{
		if (Page::STATE_DRAFT == $this->version) {
			return $this;
		}
		return Page::draft()
			->forSiteAndPath($this->site_id, $this->path)
			->first();
	}

	/**
	 * Get the next sibling page to this one.
	 * @return Page|null
	 */
	public function nextPage()
	{
		return $this->siblingsAndSelf()->get()->first(
			function ($item) {
				return $item->lft == $this->rgt + 1;
			}
		);
	}

	/**
	 * Get the previous sibling page to this one.
	 * @return Page|null
	 */
	public function previousPage()
	{
		return $this->siblingsAndSelf()->get()->first(
			function ($item) {
				return $item->rgt == $this->lft - 1;
			}
		);
	}


	/**
	 * Find a Page by site id and path.
	 * @param $site_id
	 * @param $path
	 * @param string $version - The page version (draft, published)
	 * @return mixed
	 */
	public static function findBySiteAndPath($site_id, $path, $version = Page::STATE_DRAFT)
	{
		return Page::forSiteAndPath($site_id, $path)
			->version($version)
			->first();
	}

	/**
	 * Find a Page based on host (domain name) and path.
	 * @param $host
	 * @param $path
	 * @return $this
	 */
	public static function findByHostAndPath($host, $path, $version = Page::STATE_PUBLISHED)
	{
		$query = Page::version($version)
			->join('sites', 'site_id', '=', 'sites.id')
			->where('sites.host', $host)
			->where(function ($query) use ($path) {
				$query->whereRaw("concat(sites.path, pages.path) = ?", [$path])
					->orWhereRaw("concat(sites.path, pages.path) = ?", [$path . '/']);
			})->select('pages.*'); // required to stop sites.path overriding pages.path in model
		return $query->first();
	}

	/**
	 * Assembles a path using the ancestor slugs within the Route tree
	 * @return string
	 */
	public function generatePath()
	{
		if (!$this->parent_id && $this->slug) {
			throw new Exception('A root Page cannot have a slug.');
		}

		$path = '/';

		$chain = $this->parent_id ? $this->parent->ancestorsAndSelf(['slug'])->get() : [];

		foreach ($chain as $ancestor) {
			if (empty($ancestor->slug)) continue; // If there are any ancestors without a path, skip.
			$path .= $ancestor->slug . '/';
		}

		return $path . $this->slug;
	}


	/**
	 * Set the revision for this page.
	 * @param null|Revision $revision
	 */
	public function setRevision($revision)
	{
		$this->revision_id = $revision ? $revision->id : null;
		if ($revision && !$revision->blocks) {
			$revision->blocks = $this->bake();
			$revision->save();
		}
		if ($this->isPublishedVersion()) {
			$revision->setPublished();
		}
		$this->save();
		return $this;
	}

	/**
	 * Is this a published version of a page?
	 * @return bool
	 */
	public function isPublishedVersion()
	{
		return Page::STATE_PUBLISHED == $this->version;
	}

	/**
	 * Loads the Layout definition, optionally including Regions
	 *
	 * @param boolean $includeRegions
	 * @return void
	 */
	public function loadLayoutDefinition($includeRegions = false)
	{
		$file = LayoutDefinition::locateDefinition($this->revision->layout_name, $this->revision->layout_version);
		$definition = LayoutDefinition::fromDefinitionFile($file);

		if ($includeRegions) $definition->loadRegionDefinitions();

		$this->layoutDefinition = $definition;
	}

	/**
	 * Returns the layoutDefinitions Collection, loading from disk if necessary,
	 * optionally including Regions.
	 *
	 * @param boolean $includeRegions
	 * @return LayoutDefinition
	 */
	public function getLayoutDefinition($includeRegions = false)
	{
		if (!$this->layoutDefinition) {
			$this->loadLayoutDefinition($includeRegions);

		} elseif ($includeRegions) {
			// If using a previously-loaded $layoutDefinition, region definitions may not be present.
			// By calling getRegionDefinitions rather than loadRegionDefinitions, RegionDefinitions get loaded,
			// but only if they are not already present. A call to laodRegionDefinitions would force a new load
			// operation regardless.
			$this->layoutDefinition->getRegionDefinitions();
		}

		return $this->layoutDefinition;
	}


	/**
	 * Deletes all blocks in the given Region
	 *
	 * @param  string $region
	 * @return void
	 */
	public function clearRegion($region)
	{
		Block::deleteForPageRegion($this, $region);
	}

	/**
	 * Create and save all the default blocks / regions / sections for a Page based on a layout.
	 * @param Page $page - The Page object to create blocks for.
	 * @param string $layout_name - The name of the layout
	 * @param integer $layout_version - The version of the layout
	 */
	public function createDefaultBlocks($layout_name, $layout_version)
	{
		$layout_definition = Layout::fromDefinitionFile(Layout::locateDefinition($layout_name,$layout_version));
		if($layout_definition){
			$data = $layout_definition->getDefaultPageContent();
			$this->saveBlocks($this->id, $data);
		}
	}

	/**
	 * Saves the default content for the page defined in blocks to the database.
	 *
	 * @param array $data ['region-name' => [['name' => 'section-1-name', 'blocks' => [ ... [block def 1], [block def 2]...]],...]]
	 */
	public function saveBlocks($page_id,$data)
	{
		foreach($data as $region_name => $sections){
			foreach($sections as $section){
				foreach($section['blocks'] as $i => $block_data) {
					$block = new Block;
					$block->fill($block_data);
					$block->page_id = $page_id;
					$block->order = $i;
					$block->region_name = $region_name;
					$block->section_name = $section['name'];
					$block->save();
				}
			}
		}
	}
}
