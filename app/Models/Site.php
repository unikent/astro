<?php
namespace App\Models;

use App\Models\Page;
use App\Models\PublishingGroup;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{

	public $fillable = [
		'name',
		'publishing_group_id',
        'host',
        'path',
        'options'
	];

	protected $casts = [
        'options' => 'json'
    ];

	protected $definition = null;

	public function activeRoute()
	{
		return $this->hasOne(Page::class, 'site_id')->whereNull('parent_id');
	}

	public function homepage()
    {
        return $this->hasOne(Page::class, 'site_id')->whereNull('parent_id');
    }

    public function draftPages()
    {
        return $this->hasMany(Page::class, 'site_id')->drafts();
    }

    public function publishedPages()
    {
        return $this->hasMany(Page::class, 'site_id')->whereNotNull('published_id');
    }

	public function pages()
	{
		return $this->hasMany(Page::class, 'site_id');
	}

	public function publishing_group()
	{
		return $this->belongsTo(PublishingGroup::class, 'publishing_group_id');
	}

    public function createHomePage($title, $layout, $user)
    {
        $page = Page::create([
            'site_id' => $this->id,
            'parent_id' => null,
            'version' => Page::DRAFT,
            'slug' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            ''
        ]);
        $revision = new Revision([
            'site_id' => $this->id,
            'title' => 'Home Page',
            'created_by' => $user->getAuthIdentifier(),
            'updated_by' => $user->getAuthIdentifier(),
            'options' => [],
            'layout_name' => $layout['name'],
            'layout_version' => $layout['version']
        ]);
    }

}
