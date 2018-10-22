<?php

namespace Tests\Feature;

use Tests\Feature\Traits\MakesAssertionsAboutPages;
use Tests\Feature\Traits\CreatesFeatureFixtures;
use Tests\Feature\Traits\ExtractsPageAttributesFromPageJson;
use Tests\TestCase;

/**
 * Feature tests for the add page api endpoint.
 * See the traits used for additional information about how these tests work.
 * @package Tests\Feature
 */
class AddPageTest extends TestCase
{
	use CreatesFeatureFixtures,
		ExtractsPageAttributesFromPageJson,
		MakesAssertionsAboutPages;

    /**
     * Requests must be authorised
     * @test
	 * @group api
     */
    public function requestsRequireAuth()
    {
    	$response = $this->json('POST', '/api/v1/pages', []);
        $response->assertStatus(401);
    }

	/**
	 * Provide names of properties for users who should be able to add pages.
	 * @return array
	 */
    public function usersWhoCanAddPagesProvider()
	{
		return $this->packArrayForProvider(['admin']);
	}

	/**
	 * Provide names of user objects who should not be able to add pages.
	 * @return array
	 */
	public function usersWhoCannotAddPagesProvider()
	{
		return $this->packArrayForProvider(['contributor', 'randomer', 'owner', 'editor', 'viewer']);
	}

	/**
	 * @test
	 * @group api
	 * @dataProvider usersWhoCanAddPagesProvider
	 */
    public function addPageToParentAddsAtEndOfSiblings($user)
	{
		$response = $this->json('POST', '/api/v1/pages', [
				'title' => 'Add At End',
				'parent_id' => $this->site->draftHomepage->id,
				'slug' => 'add-at-end',
				'layout' => [
					'name' => 'test-layout',
					'version' => 1
				]
			],
			[
				'Authorization' => 'Bearer ' . $this->$user->api_token
			]
		);
		// Page has been created
		$response->assertStatus(201);
		$response->assertJson([
				'data' => [
				'title' => 'Add At End',
				'parent_id' => $this->site->draftHomepage->id,
				'slug' => 'add-at-end'
			]
		]);
		$this->assertTrue($this->pageIsLastChildOf($this->getPageId($response->json()['data']), $this->site->draftHomepage->id));
	}

	public function addPageBeforePageAddsPageBeforeThatPage()
	{
		$this->markTestIncomplete();
	}
}
