<?php

use App\Models\LocalAPIClient;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * @var array
	 */
	private $tables = [
	    'revisions',
	    'page_content',
	    'pages',
        'sites',
        'publishing_groups',
        'publishing_groups_users',
		'users',
	];

    public $testTree = [
        [
            'slug' => 'undergraduate',
            'title' => 'Undergraduates',
            'layout_name' => 'test-layout',
            'layout_version' => 1,
            'children' => [
                [
                    'slug' => '2017',
                    'title' => '2017 Entry',
                    'layout_name' => 'test-layout',
                    'layout_version' => 1
                ],
                [
                    'slug' => '2018',
                    'title' => '2018 Entry',
                    'layout_name' => 'test-layout',
                    'layout_version' => 1
                ],
            ]
        ],
        [
            'slug' => 'postgraduate',
            'title' => 'Postgraduates',
            'layout_name' => 'test-layout',
            'layout_version' => 1,
            'children' => [
                [
                    'slug' => '2017',
                    'title' => '2017 Entry',
                    'layout_name' => 'test-layout',
                    'layout_version' => 1
                ],
                [
                    'slug' => '2018',
                    'title' => '2018 Entry',
                    'layout_name' => 'test-layout',
                    'layout_version' => 1
                ],
            ]
        ]
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


        $client = new LocalAPIClient($user);
        $site = $client->createSite(
            1, 'Test Site', 'example.com', '', 'test-layout', 1
        );
        $client->addTree($site->id, $site->homePage->id, null, $this->testTree);

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
