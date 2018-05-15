<?php

namespace Tests\Feature\Traits;

use App\Models\LocalAPIClient;
use App\Models\User;
use App\Models\Role;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;

/**
 * Implements the phpunit setup() method which is called before each test
 * to create database fixtures for the feature tests and specify to use a database transaction for each test.
 * @package Tests\Feature
 */
trait CreatesFeatureFixtures
{
	use DatabaseTransactions;

	/**
	 * @var User - user with admin privileges.
	 */
	public $admin = null;
	/**
	 * @var User - user with site owner privileges on the test site.
	 */
	public $owner = null;
	/**
	 * @var User - user with editor privileges on the test site.
	 */
	public $editor = null;
	/**
	 * @var User - user with contributor privileges on the test site.
	 */
	public $contributor = null;
	/**
	 * @var User - user with no privileges on the test site.
	 */
	public $randomer = null;


	/**
	 * Runs before every test and sets up database fixtures including:
	 * - 4 users named 'admin', 'owner', 'editor and 'contributor'
	 * - admin user has administrator privileges
	 * - Creates a single site using a site template from the ../Support/Fixtures/definitions/sites folder.
	 * - Adds the users 'owner', 'editor' and 'contributor' to this site with the role that they are named after.
	 * - Runs the command to setup permissions in the database.
	 */
	public function setup()
	{
		parent::setup();
		$hasher = new BcryptHasher();
		$this->admin = factory(User::class)->create([
			'username' => 'admin',
			'name'=> 'Admin',
			'password'=> $hasher->make('admin'),
			'role' => 'admin',
			'api_token' => 'test'
		]);

		// create some users to test with...
		$this->editor = factory(\App\Models\User::class)->create([
			'username' => 'editor',
			'name' => 'Editor',
			'password' => $hasher->make('editor'),
			'role' => 'user',
			'api_token' => 'editor-test'
		]);

		$this->owner = factory(User::class)->create([
			'username' => 'owner',
			'name' => 'Owner',
			'password' => $hasher->make('owner'),
			'role' => 'user',
			'api_token' => 'owner-test'
		]);

		$this->contributor = factory(User::class)->create([
			'username' => 'contributor',
			'name' => 'Contributor',
			'password' => $hasher->make('contributor'),
			'role' => 'user',
			'api_token' => 'contributor-test'
		]);

		$this->randomer = factory(User::class)->create([
			'username' => 'randomer',
			'name' => 'Random User',
			'password' => $hasher->make('randomer'),
			'role' => 'user',
			'api_token' => 'randomer-test'
		]);

		$this->client = new LocalAPIClient($this->admin);
		$this->site = $this->client->createSite(
			'Test Site', 'example.com', '', ['name'=>'one-page-site','version'=>1]
		);
		$this->client->updateSiteUserRole($this->site->id,'editor', Role::EDITOR);
		$this->client->updateSiteUserRole($this->site->id,'owner', Role::OWNER);
		$this->client->updateSiteUserRole($this->site->id,'contributor', Role::CONTRIBUTOR);

		Artisan::call('astro:permissions', ['action' => 'refresh']);
	}

	/**
	 * Converts an array like [ 'a' , 'b', 'c' ] to [ 'a' => ['a'], 'b' => ['b'], ...] for use with dataProviders
	 * @param array $input Array of string values to pack into another array keyed by the value
	 * @return array
	 */
	public function packArrayForProvider($input)
	{
		$result = [];
		foreach($input as $item) {
			$result[$item] = [$item];
		}
		return $result;
	}


}