<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * @var array
	 */
	private $tables = [
		'users',
		'pages',
		'routes',
		'blocks',
		'media'
	];

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		if(App::environment() === 'production')
		{
			exit('Oh no you ditten\'! The seeder should only be run in dev mode.');
		}

		$this->cleanDatabase();

		// TODO: separate out seeders and clean up code left from prototype

		$a = factory('App\Models\User')->create([ 'username' => 'admin', 'name'=> 'Admin']);
		$p = factory('App\Models\Page')->create([ 'title' => 'Test Site', 'is_site'=> 1]);
		$r = factory('App\Models\Route')->states('isRoot')->create([ 'page_id' => $p->id ]);

		for($i = 0; $i < 5; $i++)
		{
			if($i == 0)
			{
				factory('App\Models\Block')
					->create([
						'page_id' => $p->id,
						'order'   => $i,
						'fields'  => [
							'image' => 'http://lorempixel.com/1200/700/cats/',
							'block_heading'=> 'Title block',
							'block_description' => 'Sub title',
							'block_link' => '',
							'image_alignment'=>'top'
						]
					]);
			}
			else
			{
				factory('App\Models\Block')->create([ 'page_id' => $p->id, 'order' => $i]);
			}
		}

		for($i = 0; $i < 5; $i++)
		{
			$p2 = factory('App\Models\Page')->create([ 'title' => 'Test Page '. $i]);
			$r2 = factory('App\Models\Route')->create([
				'parent_id' => $r->id,
				'page_id' => $p2->id
			]);

			for($x = 0; $x < 5; $x++)
			{
				if($x == 0)
				{
					factory('App\Models\Block')
						->create([
							'page_id' => $p2->id,
							'order'   => $x,
							'fields'  => [
								'image'             => 'http://lorempixel.com/1200/700/cats/',
								'block_heading'     => 'Title block',
								'block_description' => 'Sub title',
								'block_link'        => '',
								'image_alignment'   =>'top'
							]
						]);
				}
				else
				{
					factory('App\Models\Block')->create([ 'page_id' => $p2->id, 'order' => $x]);
				}
			}
		}

		$p3 = factory('App\Models\Page')->create([ 'title' => 'Nested Page']);
		$r = factory('App\Models\Route')->create([
			'page_id' => $p3->id,
			'parent_id' => $r2->id,
			'slug' => 'nested-page'
		]);

		$this->call(MediaSeeder::class);
	}

	/**
	 * Empty the database
	 *
	 * @return void
	 */
	private function cleanDatabase()
	{
		// allow mass assignment
		Eloquent::unguard();

		DB::statement('SET FOREIGN_KEY_CHECKS=0;');

		foreach($this->tables as $table)
		{
			DB::table($table)->truncate();
		}

		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}
