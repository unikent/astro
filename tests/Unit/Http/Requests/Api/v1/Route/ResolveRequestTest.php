<?php
namespace Tests\Unit\Http\Requests\Api\v1\Route;

use Faker;
use Mockery;
use Validator;
use App\Models\Page;
use Tests\Unit\Http\Requests\RequestTestCase;
use App\Http\Requests\Api\v1\Route\ResolveRequest;

class ResolveTest extends RequestTestCase
{

    protected static $modelClass = Route::class;
    protected static $requestClass = ResolveRequest::class;

    /**
     * @test
     * @group validation
     */
    public function validation_WithValidParams_IsValid()
    {
        $request = $this->mockRequest('GET', [ 'path' => '/foobar' ]);
        $validator = $request->getValidatorInstance();

        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     * @group validation
     */
    public function validation_WithoutPath_IsInvalid()
    {
        $request = $this->mockRequest('GET', []);
        $validator = $request->getValidatorInstance();

        $validator->passes();
        $this->assertCount(1, $validator->errors()->get('path'));
    }

}
