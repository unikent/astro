<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Site extends Model
{

    public $fillable = [
        'name',
        'host',
        'path',
        'created_by',
        'updated_by',
		'site_definition_name',
		'site_definition_version',
        'options'
    ];

    protected $casts = [
        'options' => 'json'
    ];

    protected $definition = null;

    /************************************************************
     * Relationships
     ************************************************************/

	/**
	 * The draft version of the homepage of this site.
	 * This should always exist.
	 * @return HasOne
	 */
    public function draftHomepage()
	{
		return $this->homepage(Page::STATE_DRAFT);
	}

	/**
	 * The published version of the homepage of this site.
	 * May not exist if the homepage has not been published.
	 * @return HasOne
	 */
    public function publishedHomepage()
	{
		return $this->homepage(Page::STATE_PUBLISHED);
	}

    /**
     * All the draft pages for this site.
     * @return mixed
     */
    public function draftPages()
    {
        return $this->pages(Page::STATE_DRAFT);
    }

    /**
     * All the published pages for this site.
     * @return mixed
     */
    public function publishedPages()
    {
        return $this->pages(Page::STATE_PUBLISHED);
    }

	/**
	 * Get the UserSiteRole for the currently authenticated user for this site.
	 * @return mixed
	 */
	public function currentUserRole()
	{
		$user_id = Auth::user() ? Auth::user()->id : 0;
		return $this->hasOne(UserSiteRole::class, 'site_id')
					->where('user_id', '=', $user_id);
	}

	/**
	 * Get the UserSiteRoles for this Site.
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function usersRoles()
	{
		return $this->hasMany(UserSiteRole::class);
	}

	/*
	 * Relationship utility methods.
	 */

	/**
	 * All the pages of a version of this site.
	 * @param string $version The version of the site to get the homepage for. Defaults to Page::STATE_DRAFT
	 * @return HasMany
	 */
	public function pages($version = Page::STATE_DRAFT)
	{
		return $this->hasMany(Page::class, 'site_id')
					->where('version', $version);
	}

	/**
	 * Utility to get the homepage for a version of this site.
	 * @param string $version The version of the site to get the homepage for. Defaults to Page::STATE_DRAFT
	 * @return HasOne
	 */
	public function homepage($version)
	{
		return $this->hasOne(Page::class, 'site_id')
			->whereNull('parent_id')
			->where('version', $version);
	}

	/**
	 * Sets (and saves) the value of a site option
	 * @param string $name - The option name
	 * @param mixed $value - The option value.
	 */
	public function setOption($name, $value)
	{
		$options = $this->options;
		if(null === $value) {
			unset($options[$name]);
		}
		else {
			$options[$name] = $value;
		}
		$this->options = $options;
		$this->save();
	}

	/**
	 * Media attached to this site
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function media()
	{
		return $this->belongsToMany(Media::class, 'media_sites');
	}

}
