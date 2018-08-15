<?php

namespace Tests\Unit\Validation\Rules;

use Tests\TestCase;
use Validator;
use Mockery as m;

use App\Validation\Rules\MaxLengthWithoutHtmlRule;

class MaxLengthWithoutHtmlRuleTest extends TestCase
{
	public function testValidationRuleIsRegistered()
	{
		Validator::shouldReceive('extend')
			->once()
			->with('max_length_without_html', m::type('callable'));

		Validator::shouldReceive('replacer')
			->once()
			->with('max_length_without_html', m::type('callable'));

		MaxLengthWithoutHtmlRule::register();
	}

	public function testInstanceWorksWithoutArguments()
	{
		$this->markTestIncomplete();
		// we want to allow using the class without arguments
		$rule = new MaxLengthWithoutHtmlRule();
	}

	public function testInstanceThrowsAnExceptionIfNoParametersSupplied()
	{
		$this->markTestIncomplete();
		// InvalidArgumentException
	}

	public function testValidationRuleWorks()
	{
		// try a bunch of valid and invalid input and see if the rule passes
		// try some stuff with html entitites, make sure they are counted as 1 character
		// $this->assertFalse($v->passes());
	}

	public function testReplacerCorrectlyReplacesPlaceholder()
	{
		$this->markTestIncomplete();
		// ensure messages returned are correct (and replacer works)
	}
}
