<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 27/07/17
 * Time: 11:27
 */

namespace Tests\Unit\Models\APICommands;

use App\Models\APICommands\CreateSite;

class CreateSiteTest extends APICommandTestCase
{
    public function fixture()
    {
        return new CreateSite();
    }
    public function getValidData()
    {
        return [
            'name' => 'A Valid Name',
            'publishing_group_id' => factory(\App\Models\PublishingGroup::class)->create()->getKey(),
            'host' => 'example.com',
            'path' => '',
            'default_layout_name' => 'test-layout',
            'default_layout_version' => 1
        ];
    }

    /**
     * @test
     * @group validation
     */
    public function validation_whenInputIsValid_passes()
    {
        $validator = $this->validator($this->input(null));
        $validator->passes();
        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     * @group validation
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
     * @group validation
     */
    public function validation_whenPublishingGroupIsMissing_fails()
    {
        $data = $this->input(null, 'publishing_group_id');
        $this->assertTrue($this->validator($data)->fails());
    }

    /**
     * @test
     * @group validation
     */
    public function validation_whenPublishingGroupDoesNotExist_fails()
    {
        $data = $this->input(['publishing_group_id' => 0xffffff]);
        $this->assertTrue($this->validator($data)->fails());
    }

    /**
     * @test
     * @group validation
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
     * @group validation
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
     * @group validation
     * @dataProvider invalidPathProvider
     */
    public function validation_whenPathIsNotValid_fails($path)
    {
        $data = $this->input(['path' => $path]);
        $this->assertTrue( $this->validator($data)->fails());
    }

    /**
     * @test
     * @group validation
     */
    public function validation_whenHostAndPathAreNotUnique_fails()
    {

    }

    /**
     * @test
     * @group validation
     */
    public function validation_whenDefaultLayoutNameIsMissingOrInvalid_fails()
    {
        $data = $this->input(['default_layout_name' => '']);
        $this->assertTrue($this->validator($data)->fails());
        $data = $this->input(['default_layout_name' => '//£*']);
        $this->assertTrue($this->validator($data)->fails());
        $data = $this->input(['default_layout_name' => null]);
        $this->assertTrue($this->validator($data)->fails());
        $data = $this->input(null,['default_layout_name']);
        $this->assertTrue($this->validator($data)->fails());
    }

    /**
     * @test
     * @group validation
     */
    public function validation_whenDefaultLayoutVersionIsMissingOrInvalid_fails()
    {

    }

    public function validation_whenDefaultLayoutDefinitionNotFound_fails()
    {
        
    }

}
