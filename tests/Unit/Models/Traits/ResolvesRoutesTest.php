<?php
namespace Tests\Unit\Models\Traits;

use App\Models\Traits\ResolvesRoutes;
use Tests\TestCase;

class ResolvesRoutesTest extends TestCase
{
    /**
     * @var ResolvesRoutes
     */
    public $fixture;

    /**
     * @throws \ReflectionException
     */
    public function setUp() : void
    {
        $this->fixture = $this->getMockForTrait(ResolvesRoutes::class);
    }

    /**
     * @test
     * @param String $input - input path with query string
     * @param String $expectedPath - expected result path
     * @param Array $expectedParams - expected array of parameters
     * @dataProvider pathWithArrayQueryStringsProvider
     */
    public function parsePathAndParams_withArrayParams_returnsPathAndCorrectArrayOfParams($input, $expectedPath, $expectedParams)
    {
        [$parsedPath, $parsedParams] = $this->fixture->parsePathAndParams($input);
        $this->assertEquals($expectedPath, $parsedPath);
        $this->assertEquals($expectedParams, $parsedParams);
    }

    /**
     * @test
     * @param String $input - input path with query string
     * @param String $expectedPath - expected result path
     * @param Array $expectedParams - expected array of parameters
     * @dataProvider pathWithSimpleQueryStringsProvider
     */
    public function parsePathAndParams_withSimpleParams_returnsPathAndCorrectArrayOfParams($input, $expectedPath, $expectedParams)
    {
        [$parsedPath, $parsedParams] = $this->fixture->parsePathAndParams($input);
        $this->assertEquals($expectedPath, $parsedPath);
        $this->assertEquals($expectedParams, $parsedParams);
    }

    /**
     * @test
     * @dataProvider pathWithEmptyParamsProvider
     */
    public function parsePathAndParams_withEmptyParams_returnsInputPath($path)
    {
        [$parsedPath, $params] = $this->fixture->parsePathAndParams($path);
        $this->assertEquals([], $params);
        $this->assertEquals($path, $parsedPath);
    }

    /**
     * data provider for paths without query strings
     * @return array
     */
    public function pathWithEmptyParamsProvider()
    {
        return [
            [ '' ],
            [ '/'],
            [ '/one' ],
            [ '/one/two/']
        ];
    }

    /**
     * data provider for paths with query strings and expected results
     */
    public function pathWithSimpleQueryStringsProvider()
    {
        // each array entry is 'path-with-querystring', 'expected-output-path', 'expected-output-query-params-array'
        return [
            [ '/?', '/', []],
            [ '/one?a=2&c=4&b=6', '/one', ['a' => 2, 'c' => 4, 'b'  => 6]],
            [ '/two?foo', '/two', ['foo' => '']],
            [ '/three?bar=', '/three', ['bar' => '']],
            [ '/four?foo=bar[]', '/four', ['foo' => 'bar[]']],
        ];
    }

    /**
     * data provider for paths with query strings and expected results
     */
    public function pathWithArrayQueryStringsProvider()
    {
        // each array entry is 'path-with-querystring', 'expected-output-path', 'expected-output-query-params-array'
        return [
            [ '/?a[]=2&a[]=6&a[]=9', '/', ['a' => [2,6,9]]],
            [ '/one?a[]=2&c=4&a[]=6', '/one', ['a' => [2,6], 'c' => 4]],
            [ '/?two[]=2&two[]=6&two[]', '/', ['two' => ['2','6', '']]],
            [ '/?three[]=2&three[]=6&three[]=', '/', ['three' => ['2','6', '']]],
            [ '/four?five[]=2&five[]=6&five[]', '/four', ['five' => ['2','6', '']]],
            [ '?a[b][c]=22', '', ['a' => ['b' => ['c' => 22]]]],
            [ '?a[b][c]=42&a[b][d]=43&a[b][f]&a[b][e]=[1,2,3]', '', [
                'a' => [
                    'b' => [
                        'c' => '42',
                        'd' => '43',
                        'e' => '[1,2,3]', // note that array syntax as VALUE isn't parsed into an array, it is still a string
                        'f' => ''
                    ]
                ]
            ]]
        ];
    }
}
