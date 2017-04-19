<?php
namespace App\Models;

use App\Models\Traits\Tracked;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
	use Tracked;

	public $fillable = [
		'definition_name',
		'definition_version',
		'region_name',
		'fields',
		'order',
	];

	protected $casts = [
        'fields' => 'json',
	];


    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct($attributes = []){
        parent::__construct($attributes);

        $this->fields = $this->fields ?: [];
    }

	/**
	 * Deletes all blocks for a given Page and Region.
	 *
	 * @param  Page|int $page_or_id
	 * @param  string $region
	 * @return void
	 */
	public static function deleteForPageRegion($page_or_id, $region)
	{
		$page_id = is_numeric($page_or_id) ? $page_or_id : $page_or_id->getKey();
		static::where('page_id', '=', $page_id)->where('region_name', '=', $region)->delete();
	}

}
