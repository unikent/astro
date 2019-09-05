<?php
namespace Tests\Unit\Validation\Brokers;

use Config;
use Tests\TestCase;
use App\Validation\Brokers\RegionBroker;
use Illuminate\Validation\ValidationException;
use App\Models\Definitions\Block as BlockDefinition;
use App\Models\Definitions\Region as RegionDefinition;
use Illuminate\Validation\Validator as LaravelValidator;

class RegionBrokerTest extends TestCase
{

	/**
	 * @test
	 */
	public function getSectionConstraintRules_CreatesRuleValidatingAllowedBlocks()
	{
        $file = RegionDefinition::locateDefinition('test-region-v1');
        $region = RegionDefinition::fromDefinitionFile($file);
		$rv = new RegionBroker($region);

		$rules = $rv->getSectionConstraintRules("test-section");

		$this->assertArrayHasKey('definition_name', $rules['allowedBlocks']);
		$this->assertEquals('inVersioned:{version},test-block-v1', $rules['allowedBlocks']['definition_name'][0]);
	}

	/**
	 * @test
	 */
	public function getSectionConstraintRules_CreatesRuleValidatingBlockLimits()
	{
        $file = RegionDefinition::locateDefinition('test-region-with-section-with-min-blocks-v1');
        $region = RegionDefinition::fromDefinitionFile($file);
		$rv = new RegionBroker($region);

		$rules = $rv->getSectionConstraintRules("test-section");

		$this->assertArrayHasKey('blocks', $rules['blockLimits']);
		$this->assertEquals('min:2', $rules['blockLimits']['blocks'][0]);
		$this->assertEquals('max:5', $rules['blockLimits']['blocks'][1]);
	}

	/**
	 * @test
	 */
	public function getSectionConstraintRules_CreatesRuleValidatingBlockRequired()
	{
        $file = RegionDefinition::locateDefinition('test-region-with-required-section-v1');
        $region = RegionDefinition::fromDefinitionFile($file);
		$rv = new RegionBroker($region);

		$rules = $rv->getSectionConstraintRules("test-section");

		$this->assertArrayHasKey('blocks', $rules['blocksRequired']);
		$this->assertEquals('required', $rules['blocksRequired']['blocks'][0]);
	}

}
