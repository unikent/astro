<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * @var array
	 */
	private $tables = [
		'users'
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

		DB::table('publishing_groups')->insert([
		    'id' => 1,
		    'name' => 'Test Group'
        ]);

		$user = factory(User::class)->create([
			'username' => 'admin',
			'name'=> 'Admin',
			'password'=> Hash::make('admin'),
			'role' => 'admin',
            'api_token' => 'test'
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
