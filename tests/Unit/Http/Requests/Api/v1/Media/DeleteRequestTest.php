<?php
namespace Tests\Unit\Http\Requests\Api\v1\Media;

use Faker;
use Mockery;
use Validator;
use App\Models\Site;
use App\Models\PublishingGroup;
use Illuminate\Support\Collection;
use Tests\Unit\Http\Requests\RequestTestCase;
use App\Http\Requests\Api\v1\Media\DeleteRequest;

class DeleteRequestTest extends RequestTestCase
{

    protected static $modelClass = Media::class;
    protected static $requestClass = DeleteRequest::class;


    protected function getAttrs()
    {
        $pgs = factory(PublishingGroup::class, 2)->create();

        $sites = new Collection([
            factory(Site::class)->create([ 'publishing_group_id' => $pgs[0]->getKey() ]),
            factory(Site::class)->create([ 'publishing_group_id' => $pgs[1]->getKey() ]),
        ]);

        return [
            'site_ids' => $sites->pluck('id')->toArray(),
            'publishing_group_ids' => $pgs->pluck('id')->toArray(),
        ];
    }


    /**
     * @test
     * @group media
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
    public function validation_WhenSiteIdsArePresentButPublishingGroupIdsAreNot_IsValid()
    {
        $attrs = $this->getAttrs();
        unset($attrs['publishing_group_ids']);

        $request = $this->mockRequest('DELETE', $attrs);
        $validator = $request->getValidatorInstance();

        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     */
    public function validation_WhenPublishingGroupIdsArePresentButSiteIdsAreNot_IsValid()
    {
        $attrs = $this->getAttrs();
        unset($attrs['site_ids']);

        $request = $this->mockRequest('DELETE', $attrs);
        $validator = $request->getValidatorInstance();

        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     */
    public function validation_WhenPublishingGroupIdsAndSiteIdsAreMissing_IsInvalid()
    {
        $request = $this->mockRequest('DELETE', []);
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

        $request = $this->mockRequest('DELETE', $attrs);
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

        $request = $this->mockRequest('DELETE', $attrs);
        $validator = $request->getValidatorInstance();

        $validator->passes();
        $this->assertCount(1, $validator->errors('publishing_group_ids'));
    }

}
