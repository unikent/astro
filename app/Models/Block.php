<?php
namespace App\Models;

use App\Models\Traits\Tracked;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
	use Tracked;

	public $fillable = ['fields', 'parent_block', 'section', 'order', 'type_guid'];

	public function getFieldsAttribute()
	{
		return !empty($this->attributes['fields']) ? json_decode($this->attributes['fields'], true) : null;
	}

	public function setFieldsAttribute($json)
	{
		$this->attributes['fields'] = json_encode($json);
	}
}
