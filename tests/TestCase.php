<?php

namespace Tests;

use Config;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

use Tests\Concerns\HandlesExceptions;

abstract class TestCase extends BaseTestCase
{
	use CreatesApplication, DatabaseTransactions, HandlesExceptions;

	public function setUp(){
		parent::setUp();
		Config::set('app.definitions_path', base_path('tests/Support/Fixtures/definitions'));
	}

	public function createUser(){
		$user = factory(User::class)->create();
		return $user;
	}
}
