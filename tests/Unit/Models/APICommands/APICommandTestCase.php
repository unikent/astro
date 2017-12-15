<?php

namespace Tests\Unit\Models\APICommands;

use Astro\API\Models\Contracts\APICommand;
use Astro\API\Models\LocalAPIClient;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

abstract class APICommandTestCase extends TestCase
{
    /**
     * @var array A valid (existing) layout name and version
     */
    protected $test_layout = [
        'name' => 'test-layout',
        'version' => 1
    ];

    /**
     * @return array Valid input data.
     */
    abstract public function getValidData();

    /**
     * @return APICommand A new instance of the class to test.
     */
    abstract public function command();

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
        $command = $this->command();
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