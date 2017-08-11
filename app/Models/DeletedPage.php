<?php

namespace App\Models;

use App\Http\Transformers\Api\v1\PageContentTransformer;
use App\Models\Traits\Tracked;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Records the path and revision where a page was deleted.
 * @package App\Models
 */
class DeletedPage extends Model
{
	use Tracked;

	protected $fillable = [
        'revision_id',
        'path',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
	];

}
