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
	 * @var User - user account with "viewer" level permissions (can read everything)
	 */
	public $viewer = null;

	/**
	 * @var array - array of the accounts available here.
	 */
	public $allUsers = [
		'admin',
		'viewer',
		'randomer',
		'contributor',
		'owner',
		'editor',
	];

	/**
	 * @var array - array of usernames of all users who are authorized to perform whatever command is being tested.
	 * Override this in your test classes if more than just the admin user can do something, and use
	 * $this->unauthorizedUsers() when testing that authorization fails for ALL users not in the authorizedUsers list.
	 */
	public $authorizedUsers = [
		'admin',
	];

	/**
	 * Runs before every test and sets up database fixtures including:
	 * - Users named 'admin', 'owner', 'editor, 'contributor' and 'viewer'
	 * - Another user called 'randomer' who has no privileges
	 * - admin user has administrator privileges
	 * - viewer user has viewer privileges
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
			'api_token' => 'admin-test'
		]);

		$this->viewer = factory(User::class)->create([
			'username' => 'viewer',
			'name'=> 'Viewer',
			'password'=> $hasher->make('viewer'),
			'role' => 'viewer',
			'api_token' => 'viewer-test'
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
	 * Gets all the user accounts who are unauthorized by removing any users declared in $this->authorizedUsers from
	 * the full list of $this->users.
	 * @return array - array of [username => username] where each entry is a user who is not authorized to perform the
	 * command being tested.
	 */
	public function unauthorizedUsers()
	{
		$no = array_diff($this->allUsers, $this->authorizedUsers);
		return array_combine($no,$no);
	}

	/**
	 * Get array of usernames authorized to perform the command being tested.
	 * @return array - array of [username => username]
	 */
	public function authorizedUsers()
	{
		return array_combine($this->authorizedUsers,$this->authorizedUsers);
	}

	/**
	 * Gets an array of authorized users in the correct format for a data provider
	 * @return array - array of [username => [username]] - the right format for a data provider
	 */
	public function authorizedUsersProvider()
	{
		return $this->packArrayForProvider($this->authorizedUsers);
	}

	/**
	 * Gets an array of unauthorized users in the correct format for a data provider
	 * @return array - array of [username => [username]] - the right format for a data provider
	 */
	public function unauthorizedUsersProvider()
	{
		return $this->packArrayForProvider($this->unauthorizedUsers());
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

	/**
	 * Create all permutations of one or more arrays each of which should contain
	 * them in a format suitable for returning from a phpunit dataprovider.
	 * - Each entry in the result will be an array of n values, where n is the number of arrays passed into the method.
	 * - The result will contain an array for every combination of the input data.
	 * - The results will be keyed by combining the keys of each item in each array that makes up that result:
	 * eg,
	 * $this->>combineForProvider(['admin', 'owner'], [ 'createSite_Valid_1' => $json1, 'createSite_valid_2' => $json2 ] )
	 * $params[0] = [ 'admin', 'owner' ]
	 * $params[1] = [ 'createSite_Valid_1' => $json1, 'createSite_valid_2' => $json2 ]
	 * =>
	 * [
	 *   'admin_createsite_valid_1' => [ 'admin', $json1 ],
	 *   'admin_createsite_valid_2' => [ 'admin', $json2 ],
	 *   'owner_createsite_valid_1' => [ 'owner', $json1 ],
	 *   'owner_createsite_valid_2' => [ 'owner', $json2 ]
	 * ]
	 * @param array ...$params
	 * @return array  [ [$params[0][0], $params[1][0] ], [$params[0][0], $params[1][1]], [$params[
	 */
	public function combineForProvider(...$params)
	{
		$results = ['' => []]; // need this so our loop below works for $params[0] iteration
		while($current = array_shift($params)) {
			$new_results = [];
			foreach($results as $key => $so_far) {
				foreach($current as $current_key => $item) {
					$new_key = $key !== '' ? "{$key}_{$current_key}" : $current_key;
					$new_results[$new_key] = array_merge($so_far, [$item]);
				}
			}
			$results = $new_results;
		}
		return $results;
	}
}