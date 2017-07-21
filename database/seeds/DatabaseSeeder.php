<?php

use App\Models\User;
use App\Models\Block;
use App\Models\Route;
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
		'blocks'
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

		factory(User::class)->create([
			'username' => 'admin',
			'name'=> 'Admin',
			'password'=> Hash::make('admin'),
			'role' => 'admin',
            'api_token' => 'test'
		]);

		$routes = [];

		$routes[] = factory(Route::class)->states('withPage', 'withSite')->create([ 'slug' => null ]);
		$routes[] = factory(Route::class)->states('withPage')->create([ 'parent_id' => $routes[0]->getKey() ]);
		$routes[] = factory(Route::class)->states('withPage')->create([ 'parent_id' => $routes[1]->getKey() ]);

		foreach($routes as $route){
			$route->page->layout_name = 'astro17';
			$route->page->save();

			//$route->makeActive();
		}

		factory(Block::class)->create([
			'page_id' => $routes[1]->page->getKey(),
			'definition_name' => 'block-quote',
			'definition_version' => 1,
			'fields' => [
				'quote' => 'This is a quote, <strong>with formatting</strong>.',
				'cite_text' => 'Winston Churchill',
			],
		]);
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
