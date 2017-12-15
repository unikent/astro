<?php

namespace Astro\API\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A RevisionSet is used to link revisions of the same Page together, even if that Page is deleted.
 * @package Astro\API\Models
 */
class RevisionSet extends Model
{

	protected $table = 'revision_sets';

	protected $fillable = [
        'site_id'
	];

    /**
     * The Revisions linked to this RevisionSet
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
	public function revisions()
    {
        return $this->hasMany(Revision::class, 'revision_set_id');
    }

}
