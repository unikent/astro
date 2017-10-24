<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 27/07/17
 * Time: 11:27
 */

namespace Tests\Unit\Models\APICommands;

use App\Models\APICommands\AddPage;
use App\Models\APICommands\CreateSite;
use App\Models\Contracts\APICommand;

class AddPageTest extends APICommandTestCase
{
    protected $site = null;

    public function createSite()
    {
        $site = $this->execute(CreateSite::class, [

        ]);
    }

    public function getValidData()
    {
        $this->site = $this->site ? $this->site : $this->api()->createSite('Test Site', 'example.org', null, [
            'name' => 'test-layout',
            'version' => 1
        ]);
        return [
            'title' => 'Lovely Page',
            'site_id' => $this->site->id,
            'slug' => 'foo',
            'next_id' => null,
            'parent_id' => $this->site->draftHomepage->id,
            'layout' => [
                'name' => 'test-layout',
                'version' => 1
            ]
        ];
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenInput_isValid_passes()
    {
        $validator = $this->validator($this->input(null));
        $validator->passes();
        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenParentID_IsMissing()
    {
        $data = $this->input([], 'title');
        $this->assertTrue( $this->validator($data)->fails());
        $data['title'] = '';
        $this->assertTrue( $this->validator($data)->fails());
        $data['title'] = str_repeat('a',200);
        $this->assertTrue( $this->validator($data)->fails());
        $data['title'] = null;
        $this->assertTrue( $this->validator($data)->fails());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenParent_existsButIsNotDraft_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenParent_isNullOrInvalid_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenNextID_IsValidButDifferentParentID_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenNextID_isPresentButInvalid_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenTitleIsMissingOrTooLong_fails()
    {
        $data = $this->input([], 'title');
        $this->assertTrue( $this->validator($data)->fails());
        $data['title'] = '';
        $this->assertTrue( $this->validator($data)->fails());
        $data['title'] = null;
        $this->assertTrue( $this->validator($data)->fails());
        $data['title'] = str_repeat('a',200);
        $this->assertTrue( $this->validator($data)->fails());
    }

    /**
     * Valid paths consist of alphanumeric characters, plus hyphens and underscores.
     * @return array Invalid paths
     */
    public function invalidSlugProvider()
    {
        return [
            ['/'],
            [''],
            ['/foo/bar/'],
            ['/foo'],
            ['foo/'],
            ['@"'],
            [null]
        ];
    }

    /**
     * @test
     * @group APICommands
     * @dataProvider invalidSlugProvider
     */
    public function validation_whenSlug_isMissingOrInvalid_fails($slug)
    {
        $data = $this->input(['slug' => $slug]);
        $this->assertTrue( $this->validator($data)->fails());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenSlug_alreadyExistsInParentsChildren_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenSlug_matchesHostAndPathOfOtherSite_fails()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenLayoutNameIsMissingOrInvalid_fails()
    {
        $data = $this->input(['layout' => ['name' => '', 'layout' => 1]]);
        $this->assertTrue($this->validator($data)->fails());
        $data = $this->input(['layout' => ['name' => '//£*', 'layout' => 1]]);
        $this->assertTrue($this->validator($data)->fails());
        $data = $this->input(['layout' => null ]);
        $this->assertTrue($this->validator($data)->fails());
        $data = $this->input(null,['layout']);
        $this->assertTrue($this->validator($data)->fails());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenLayoutVersionIsMissingOrInvalid_fails()
    {
        $data = $this->input([]);
        unset($data['layout']['version']);
        $this->assertTrue($this->validator($data)->fails());
        $data['layout']['version'] = 'v1';
        $this->assertTrue($this->validator($data)->fails());
        $data['layout']['version'] = '';
        $this->assertTrue($this->validator($data)->fails());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenLayoutDefinitionNotFound_fails()
    {
        $data = $this->input([]);
        $data['layout']['name'] = 'missing-layout-name';
        $this->assertTrue($this->validator($data)->fails());
        $data['layout']['name'] = 'test-layout';
        $data['layout']['version'] = 22;
        $this->assertTrue($this->validator($data)->fails());
    }

    /**
     * @test
     * @group APICommands
     */
    public function addChild_returns_newlyCreatedDraftPage()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_returns_newlyCreatedPage()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function addChild_addsPage_atEndOfParentsChildren()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_movesPage_nextToCorrectSibling_ifNextID()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function addChild_creates_aRevisionAndRevisionSet_andSetsTheseForThePage()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     * @group APICommands
     */
    public function addChild_setsCorrectFields()
    {
        $this->markTestIncomplete();
    }

    /**
     * @return APICommand A new instance of the class to test.
     */
    public function command()
    {
        return new AddPage();
    }
}
