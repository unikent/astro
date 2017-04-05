<?php
namespace App\Models\Definitions;

use Illuminate\Support\Collection;

class Region extends BaseDefinition
{

	public function __construct(){
		$this->blocks = new Collection;
	}

}
