<?php
namespace Astro\API\Tests\Unit\Models;

use Astro\API\Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{

	/**
	 * @test
	 */
	public function isAdmin_WhenUserHasRoleUser_ReturnsFalse()
	{
		$user = factory(User::class)->make([ 'role' => 'user' ]);
		$this->assertFalse($user->isAdmin());
	}

	/**
	 * @test
	 */
	public function isAdmin_WhenUserHasRoleAdmin_ReturnsTrue()
	{
		$user = factory(User::class)->make([ 'role' => 'admin' ]);
		$this->assertTrue($user->isAdmin());
	}

}
