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
	 * @test
	 * @group api
	 * @dataProvider adminOwnerEditorProvider
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
