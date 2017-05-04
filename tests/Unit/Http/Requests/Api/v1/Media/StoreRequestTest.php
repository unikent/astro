<?php
namespace Tests\Unit\Http\Requests\Api\v1\Media;

use Faker;
use Mockery;
use Validator;
use App\Models\Site;
use Tests\FileUploadTrait;
use Tests\FileCleanupTrait;
use App\Models\PublishingGroup;
use Illuminate\Support\Collection;
use Tests\Unit\Http\Requests\RequestTestCase;
use App\Http\Requests\Api\v1\Media\StoreRequest;

class StoreRequestTest extends RequestTestCase
{

    use FileUploadTrait, FileCleanupTrait;


    protected static $modelClass = Media::class;
    protected static $requestClass = StoreRequest::class;


    protected function getAttrs()
    {
        $pgs = factory(PublishingGroup::class, 2)->create();

        $sites = new Collection([
            factory(Site::class)->create([ 'publishing_group_id' => $pgs[0]->getKey() ]),
            factory(Site::class)->create([ 'publishing_group_id' => $pgs[1]->getKey() ]),
        ]);

        return [
            'upload' => $this->setupFileUpload('media', 'image.jpg'),

            'site_ids' => $sites->pluck('id')->toArray(),
            'publishing_group_ids' => $pgs->pluck('id')->toArray(),
        ];
    }



    /**
     * @test
     */
    public function validation_WithValidAttributes_IsValid()
    {
        $request = $this->mockRequest('DELETE', $this->getAttrs());

        $validator = $request->getValidatorInstance();
        $this->assertTrue($validator->passes());
    }



    /**
     * @test
     */
    public function validation_WhenPublishingGroupIdsArePresentButSiteIdsAreNot_IsValid()
    {
        $attrs = $this->getAttrs();
        unset($attrs['publishing_group']);

        $request = $this->mockRequest('POST', $attrs);
        $validator = $request->getValidatorInstance();

        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     */
    public function validation_WhenSiteIdsArePresentButPublishingGroupIdsAreNot_IsValid()
    {
        $attrs = $this->getAttrs();
        unset($attrs['publishing_group_ids']);

        $request = $this->mockRequest('POST', $attrs);
        $validator = $request->getValidatorInstance();

        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     */
    public function validation_WhenPublishingGroupIdsAndSiteIdsAreMissing_IsInvalid()
    {
        $request = $this->mockRequest('POST', []);
        $validator = $request->getValidatorInstance();

        $this->assertFalse($validator->passes());
    }



    /**
     * @test
     */
    public function validation_WhenSiteIdsIsNotArray_IsInvalid()
    {
        $attrs = $this->getAttrs();
        $attrs['site_ids'] = '1,2';

        $request = $this->mockRequest('POST', $attrs);
        $validator = $request->getValidatorInstance();

        $validator->passes();
        $this->assertCount(1, $validator->errors('site_ids'));
    }


    /**
     * @test
     */
    public function validation_WhenPublishingGroupIdsIsNotArray_IsInvalid()
    {
        $attrs = $this->getAttrs();
        $attrs['publishing_group_ids'] = '1,2';

        $request = $this->mockRequest('POST', $attrs);
        $validator = $request->getValidatorInstance();

        $validator->passes();
        $this->assertCount(1, $validator->errors('publishing_group_ids'));
    }



    /**
     * @test
     */
    public function validation_WhenUploadIsNotPresent_IsInvalid()
    {
        $attrs = $this->getAttrs();
        unset($attrs['upload']);

        $request = $this->mockRequest('POST', $attrs);

        $validator = $request->getValidatorInstance();
        $validator->passes();

        $this->assertCount(1, $validator->errors('upload'));
    }

    /**
     * @test
     */
    public function validation_WhenUploadIsNotAFile_IsInvalid()
    {
        $attrs = $this->getAttrs();
        $attrs['upload'] = 'foobar';

        $request = $this->mockRequest('POST', $attrs);

        $validator = $request->getValidatorInstance();
        $validator->passes();

        $this->assertCount(1, $validator->errors('upload'));
    }

}
