<?php

namespace Tests\Unit\Models\APICommands;

use App\Models\Contracts\APICommand;
use App\Models\LocalAPIClient;
use App\Models\PublishingGroup;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

/**
 * Utility methods for testing the API Commands.
 * Includes methods for setting up test fixtures.
 * @package Tests\Unit\Models\APICommands
 */
abstract class APICommandTestCase extends TestCase
{
	// wraps all database calls within a test in a transaction.
	use DatabaseTransactions;

    /**
     * @var array A valid (existing) layout name and version
     */
    protected $test_layout = [
        'name' => 'test-layout',
        'version' => 1
    ];

    /**
	 * Should be implemented to return an array of input data which would be valid for the command
	 * being tested.
	 * Used by @see APICommandTestCase::input() to get some valid default data to use in a test.
     * @return array Valid input data.
     */
    abstract public function getValidData();

    /**
	 * Should return an instance of the command class under test.
     * @return APICommand A new instance of the class to test.
     */
    abstract public function fixture();

	/**
	 * Create a site with an initial draft page hierarchy to use for testing.
	 * @param LocalAPIClient $api Used to create the site, etc.
	 * @param $structure
	 * @param string $name - The name for the site to add.
	 * @return Site - The created Site.
	 */
    public function setupSite(LocalAPIClient $api, $structure, $name)
	{
		$pubgroup = PublishingGroup::create(['name' => 'test']);
		try{
			$site = $api->createSite($pubgroup->id, "Test", "dfgkent.ac.uk", "", [ "name" => "test-layout", "version" => 1]);
		}catch(\Exception $e){
			dd($e);
		}
		$api->addTree($site->homepage->id, null, $structure);
		return $site;
	}

	/**
	 * Loads a site structure definition to use with $api->addTree from a php file.
	 * Assumes that the file returns an array.
	 * @param string $name - The name of the file without the .php extension.
	 * @return array - An array defining the site hierarchy.
	 */
	public function loadSiteStructure($name)
	{
		return require(dirname(__FILE__) . '/../../../Support/Fixtures/site-trees/' . $name . '.php');
	}

    /**
     * Get an API Client to use.
     * @param Authenticatable|null $user Optional Authenticatable. If null, will be auto-provided.
     * @return LocalAPIClient
     */
    public function api($user = null)
    {
        $user = $user ? $user : factory(User::class)->create();
        $api = new LocalAPIClient($user);
        return $api;
    }

    /**
     * Provide default valid input that can be overridden with invalid values.
     * @param array $override key => value pairs for fields to override.
     * @param array $unset Array keys to unset.
     * @return array
     */
    public function input($override, $unset = [])
    {
        if(null == $override){
            $override = [];
        }
        if( is_string($unset)){
            $unset = [$unset];
        }
        $data = array_merge($this->getValidData(), $override);
        foreach($unset as $key){
            unset($data[$key]);
        }
        return $data;
    }

    /**
     * Create and initalize a validator with the rules and messages from this command.
     * @param array $data The input data to validate.
     * @return \Illuminate\Validation\Validator
     */
    public function validator($data, $user = null)
    {
        if(null == $user){
            $user = factory(User::class)->states('admin')->create();
        }
        $command = $this->fixture();
        $data = collect($data);
        $validator = Validator::make(
            $data->toArray(),
            $command->rules($data, $user)
        );
        $validator->setCustomMessages($command->messages($data, $user));
        return $validator;
    }

    /**
     * Creates and executes an API command without any data validation
     * @param string $command Name of the command class
     * @param array $data The parameters to the command
     * @param null|Authenticatable $user
     * @return mixed The result of the command.
     */
    public function execute($command, $data, $user = null)
    {
        if(null == $user){
            $user = factory(User::class)->states('admin')->create();
        }
        $command = new $command;
        $data = collect($data);
        return $command->execute($data, $user);
    }

    public function setup()
    {
        parent::setup();
        Config::set('app.definitions_path', realpath(dirname(__FILE__ ). '/../../../Support/Fixtures/definitions'));
    }

    /**
     * The execute method of every APICommand should thrown an exception in case of DB error.
     * @test
     * @group APICommands
     */
    public function execute_throwsException_onDBError()
    {
        $this->expectException(Exception::class);
        $this->markTestIncomplete();
    }

    /**
     * The execute method of every APICommand should do all DB modifications within a transactions, so if an
     * error occurs the database state should remain unchanged.
     * @test
     * @group APICommands
     */
    public function execute_rollsback_onDBError()
    {
        $this->expectException(Exception::class);
        $this->markTestIncomplete();
    }


}