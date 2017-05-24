<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
	protected $fillable = [
		'path',
		'page_id',
	];

	public function page()
	{
		return $this->belongsTo(Page::class, 'page_id');
	}
}
