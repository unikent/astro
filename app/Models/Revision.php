<?php

namespace App\Models;

use App\Models\Traits\Tracked;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

/**
 * Revisions track the state of a Page, including its content, options, title, etc at a point in
 * time.
 * @package App\Models
 */
class Revision extends Model
{
	use Tracked;

	const TYPE_DRAFT = 'draft';
	const TYPE_PUBLISHED = 'published';
	const TYPE_DELETED = 'deleted';
	const TYPE_AUTOSAVE = 'autosave';

	protected $casts = [
		'blocks' => 'json',
		'options' => 'json'
	];

	protected $fillable = [
		'site_id',
		'title',
		'created_by',
		'updated_by',
		'layout_name',
		'layout_version',
		'revision_set_id',
		'bake',
		'valid'
	];

	protected $dates = [
		'created_at',
		'updated_at',
		'published_at'
	];

	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function boot()
	{
		parent::boot();

		static::saving(function ($revision) {
			// bake cannot be null
			$revision->blocks = !empty($revision->blocks) ? $revision->blocks : [];
			$revision->options = !empty($revision->options) ? $revision->options : [];
			if (!$revision->revision_set_id) {
				throw new ValidationException("Revision must be part of a RevisionSet");
			}
		});
	}

	/**
	 * Mark this revision as published.
	 */
	public function setPublished()
	{
		$this->published_at = $this->published_at ? $this->published_at : Carbon::now();
	}

	/**
	 * Relations
	 */

	/**
	 * The RevisionSet this Revision belongs to.
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function set()
	{
		return $this->belongsTo(RevisionSet::class, 'revision_set_id');
	}

	/**
	 * Get the draft page that uses this revision.
	 * @return HasOne
	 */
	public function draftPage()
	{
		return $this->hasOne(Page::class, 'revision_id')->where('version', Page::STATE_DRAFT);
	}

	/**
	 * Get the published page that uses this revision.
	 * @return HasOne
	 */
	public function publishedPage()
	{
		return $this->hasOne(Page::class, 'revision_id')->where('version', Page::STATE_PUBLISHED);
	}

	/**
	 * Get all previous revisions of this revision.
	 * @return mixed
	 */
	public function history()
	{
		return $this->hasManyThrough(Revision::class, RevisionSet::class, 'id', 'revision_set_id', 'revision_set_id')
			->where(
				function ($query) {
					return $query->where('created_at', '<', $this->created_at);
				});
	}

}
