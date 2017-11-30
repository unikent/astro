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

use App\Models\Page;
use App\Models\Revision;
use App\Models\RevisionSet;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class CreateSiteTest extends APICommandTestCase
{
    public function command()
    {
        return new CreateSite();
    }

    public function getValidData()
    {
        return [
            'name' => 'A Valid Name',
            'host' => 'example.com',
            'path' => '',
            'template_definition' => [
                'name' => 'one-page-site',
                'version' => 1
            ]
        ];
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenInputIsValid_passes()
    {
        $validator = $this->validator($this->input(null));
        $validator->passes();
        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenNameIsMissingOrTooLong_fails()
    {
        $data = $this->input([], 'name');
        $this->assertTrue( $this->validator($data)->fails());
        $data['name'] = '';
        $this->assertTrue( $this->validator($data)->fails());
        $data['name'] = str_repeat('a',200);
        $this->assertTrue( $this->validator($data)->fails());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenHostIsMissingOrInvalid_fails()
    {
        $data = $this->input([], 'host');
        $this->assertTrue( $this->validator($data)->fails());
        $data['host'] = '';
        $this->assertTrue( $this->validator($data)->fails());
        $data['host'] = str_repeat('/',200);
        $this->assertTrue( $this->validator($data)->fails());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenPathIsEmpty_succeeds()
    {
        $data = $this->input(['path' => '']);
        $this->assertTrue($this->validator($data)->passes());
        $data = $this->input(['path' => null]);
        $this->assertFalse($this->validator($data)->fails());
        $data = $this->input(null, ['path']);
        $this->assertFalse($this->validator($data)->fails());
    }

    /**
     * Paths must be empty or:
     * begin with a /, followed by one or more alphanumeric characters, hyphens or underscores, followed by more of the same.
     * @return array Invalid paths
     */
    public function invalidPathProvider()
    {
        return [
          ['/'],
            ['/foo/'],
            ['/foo/bar/'],
            ['foo'],
            ['foo/'],
            ['/@"']
        ];
    }

    /**
     * @test
     * @group APICommands
     * @dataProvider invalidPathProvider
     */
    public function validation_whenPathIsNotValid_fails($path)
    {
        $data = $this->input(['path' => $path]);
        $this->assertTrue( $this->validator($data)->fails());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenHostAndPathAreNotUnique_fails()
    {
        // create a site
        $existing = $this->execute(CreateSite::class, $this->getValidData());
        // attempt to create another site with the same input
        $this->assertTrue($this->validator($this->input([]))->fails());

        // add subpages to the created site
        $pageone = $this->execute(AddPage::class, [
            'site_id' => $existing->id,
            'parent_id' => $existing->draftHomepage->id,
            'slug' => 'one',
            'title' => 'Page One',
            'layout' => [
                'name' => 'test-layout',
                'version' => 1
            ]
        ]);
        $pagetwo = $this->execute(AddPage::class, [
            'site_id' => $existing->id,
            'parent_id' => $pageone->id,
            'slug' => 'two',
            'title' => 'Page Two',
            'layout' => [
                'name' => 'test-layout',
                'version' => 1
            ]
        ]);
        $this->assertTrue($this->validator($this->input(['path' => '/one']))->fails());
        $this->assertTrue($this->validator($this->input(['path' => '/one/two']))->fails());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenHostExistsButPathDoesNotClash_passes()
    {
        // create a site
        $existing = $this->execute(CreateSite::class, $this->getValidData());

        // add subpages to the created site
        $pageone = $this->execute(AddPage::class, [
            'site_id' => $existing->id,
            'parent_id' => $existing->draftHomepage->id,
            'slug' => 'one',
            'title' => 'Page One',
            'layout' => [
                'name' => 'test-layout',
                'version' => 1
            ]
        ]);
        $pagetwo = $this->execute(AddPage::class, [
            'site_id' => $existing->id,
            'parent_id' => $pageone->id,
            'slug' => 'two',
            'title' => 'Page Two',
            'layout' => [
                'name' => 'test-layout',
                'version' => 1
            ]
        ]);
        $this->assertTrue($this->validator($this->input(['path' => '/food']))->passes());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenSiteDefinitionNameIsMissingOrInvalid_fails()
    {
        $data = $this->input(['site_definition' => ['name' => '', 'version' => 1]]);
        $this->assertTrue($this->validator($data)->fails());
        $data = $this->input(['site_definition' => ['name' => '//£*', 'version' => 1]]);
        $this->assertTrue($this->validator($data)->fails());
        $data = $this->input(['site_definition' => null ]);
        $this->assertTrue($this->validator($data)->fails());
        $data = $this->input(null,['site_definition']);
        $this->assertTrue($this->validator($data)->fails());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenSiteDefinitionVersionIsMissingOrInvalid_fails()
    {
        $data = $this->input([]);
        unset($data['site_definition']['version']);
        $this->assertTrue($this->validator($data)->fails());
        $data['site_definition']['version'] = 'v1';
        $this->assertTrue($this->validator($data)->fails());
        $data['site_definition']['version'] = '';
        $this->assertTrue($this->validator($data)->fails());
    }

    /**
     * @test
     * @group APICommands
     */
    public function validation_whenSiteDefinitionNotFound_fails()
    {
        $data = $this->input([]);
        $data['site_definition']['name'] = 'missing-layout-name';
        $this->assertTrue($this->validator($data)->fails());
        $data['site_definition']['name'] = 'one-page-site';
        $data['site_definition']['version'] = 22;
        $this->assertTrue($this->validator($data)->fails());
    }

    /**
     * @test
     * @group APICommands
     */
    public function createHomePage_creates_APageARevisionAndARevisionSet_withCorrectFields()
    {
        $site = factory(Site::class)->create();
        $user = factory(User::class)->create();
        $title = 'Test Title';
        $layout = ['name' => 'test-layout', 'version' => 1];
        $homepage = $this->command()->createHomePage($site, $title, $layout, $user);

        $this->assertInstanceOf(Page::class, $homepage);
        $this->assertEquals($homepage->id, $site->draftHomepage->id);
        $this->assertEquals(null, $site->draftHomepage->parent_id);
        $this->assertEquals($site->id, $site->draftHomepage->site_id);
        $this->assertInstanceOf(Revision::class, $homepage->revision);
        $this->assertEquals($title, $homepage->revision->title);
        $this->assertEquals($layout['name'], $homepage->revision->layout_name);
        $this->assertEquals($layout['version'], $homepage->revision->layout_version);
        $this->assertEquals($user->id, $homepage->created_by);
        $this->assertEquals($user->id, $homepage->updated_by);
        $this->assertInstanceOf(RevisionSet::class, $homepage->revision->set);
    }

    /**
     * @test
     * @group APICommands
     */
    public function execute_createsASite_WithADraftHomePage()
    {
        $site = $this->command()->execute(new Collection($this->input(null)), factory(User::class)->create());
        $this->assertEquals(Page::STATE_DRAFT, $site->draftHomepage->version);
    }

}
