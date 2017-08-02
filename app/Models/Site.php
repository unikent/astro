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

	public function homePage()
    {
        return $this->hasOne(Page::class, 'site_id')->whereNull('parent_id');
    }

    public function draftPages()
    {
        return $this->hasMany(Page::class, 'site_id')->whereNotNull('draft_id');
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

}
