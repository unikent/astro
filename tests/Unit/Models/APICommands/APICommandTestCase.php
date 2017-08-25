<?php

namespace Tests\Unit\Models\APICommands;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use \App\Models\User;

abstract class APICommandTestCase extends TestCase
{

    /**
     * @return array Valid input data.
     */
    abstract public function getValidData();

    /**
     * @return A new instance of the class to test.
     */
    abstract public function fixture();

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


}