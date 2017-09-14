<?php
namespace Tests\Unit\Models;

use App\Models\PublishingGroup;
use Tests\TestCase;
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

    /**
     * @test
     */
	public function user_creation_creates_a_publishing_group_with_users_username_and_puts_user_in_it()
    {
        $user = factory(User::class)->create();
        $this->assertInstanceOf(PublishingGroup::class, PublishingGroup::where('name', '=', $user->username)->first());
    }
}
