<?php

namespace App\Models;

use App\Models\Traits\Tracked;
use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
	use Tracked;

	protected $fillable = [
		'page_content_id',
		'bake',
	];

	public function pagecontent()
	{
		return $this->belongsTo(PageContent::class, 'page_content_id');
	}

}
