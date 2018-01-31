<?php

namespace App\Events;

class FilterResponseData
{
	public $data = [];

	public function __construct($data)
	{
		$this->data = $data;
	}
}