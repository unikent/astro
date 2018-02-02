<?php

namespace Tests;

use Config;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	use CreatesApplication, DatabaseTransactions;

    public function setUp(){
        parent::setUp();
        Config::set('app.definitions_path', base_path('tests/Support/Fixtures/definitions'));
    }

	public function createUser(){
		$user = factory(User::class)->create();
		return $user;
	}
}
