<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$a = factory('App\Models\User')->create([ 'username' => 'admin', 'name'=> 'Admin']);

		$p = factory('App\Models\Page')->create([ 'title' => 'Test Site', 'key_page'=> 1]);
		$r = factory('App\Models\Route')->make([ 'page_id' => $p->id, 'slug' => '']);
		$r->root = true;
		$r->save();

		for($i = 0; $i < 5; $i++)
		{
			if($i == 0)
			{
				factory('App\Models\Block')
					->create([ 'page_id' => $p->id, 'order' => $i, 'type_guid' => "97a2e1b5-4804-46dc-9857-4235bf76a058", 'fields' => ['image' => 'http://lorempixel.com/1200/700/cats/', 'block_heading'=> "Title block", 'block_description' => 'Sub title', 'block_link' => '', 'image_alignment'=>'top']]);
			}
			else
			{
				factory('App\Models\Block')->create([ 'page_id' => $p->id, 'order' => $i]);
			}
		}

		for($i = 0; $i < 5; $i++)
		{
			$p2 = factory('App\Models\Page')->create([ 'title' => 'Test Page '. $i]);
			$r2 = factory('App\Models\Route')->make([ 'page_id' => $p2->id ]);
			$r2->parent = $r;
			$r2->save();

			for($x = 0; $x < 5; $x++)
			{
				if($x == 0)
				{
					factory('App\Models\Block')
						->create([ 'page_id' => $p2->id, 'order' => $x, 'type_guid' => "97a2e1b5-4804-46dc-9857-4235bf76a058", 'fields' => ['image' => 'http://lorempixel.com/1200/700/cats/', 'block_heading'=> "Title block", 'block_description' => 'Sub title', 'block_link' => '', 'image_alignment'=>'top']]);
				}
				else
				{
					factory('App\Models\Block')->create([ 'page_id' => $p2->id, 'order' => $x]);
				}
			}
		}

		$p3 = factory('App\Models\Page')->create([ 'title' => 'Nested Page']);
		$r = factory('App\Models\Route')->make([ 'page_id' => $p3->id, 'slug' => 'nested-page']);
		$r->parent = $r2;
		$r->save();
	}
}
