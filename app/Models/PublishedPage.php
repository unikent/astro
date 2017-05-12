<?php

namespace App\Models;

use App\Models\Traits\Tracked;
use Illuminate\Database\Eloquent\Model;

class PublishedPage extends Model
{
	use Tracked;

	protected $fillable = [
		'page_id',
		'bake',
	];

}
