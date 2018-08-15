<?php

namespace Tests\Unit\Validation\Rules;

use Tests\TestCase;
use Validator;
use Illuminate\Validation\ValidationException;

use App\Validation\Rules\MaxLengthWithoutHtmlRule;

class MaxLengthWithoutHtmlRuleTest extends TestCase
{
	public function testValidationRuleIsRegistered()
	{
		Validator::shouldReceive('extend')
			->once()
			->with(
				'max_length_without_html',
				MaxLengthWithoutHtmlRule::class . '@extension'
			);

		Validator::shouldReceive('replacer')
			->once()
			->with(
				'max_length_without_html',
				MaxLengthWithoutHtmlRule::class . '@replacer'
			);

		MaxLengthWithoutHtmlRule::register();
	}

	public function testInstanceWorksWithoutArguments()
	{
		// We want to allow using the class without arguments mainly for
		// conveniance (getMessage method), but it also sets the max length to 0
		$rule = new MaxLengthWithoutHtmlRule();
		$this->assertAttributeSame(0, 'max_length', $rule);
	}

	public function testInstanceThrowsAnExceptionIfEmptyParameters()
	{
		$this->expectException(\InvalidArgumentException::class);
		new MaxLengthWithoutHtmlRule([]);
	}

	public function testInstanceThrowsAnExceptionIfParamaterIsInvalid()
	{
		$this->assertException(\InvalidArgumentException::class, function() {
			new MaxLengthWithoutHtmlRule(['hello']);
		});

		$this->assertException(\InvalidArgumentException::class, function() {
			new MaxLengthWithoutHtmlRule([-30]);
		});

		$this->assertException(\InvalidArgumentException::class, function() {
			new MaxLengthWithoutHtmlRule(['-30']);
		});

		$this->assertException(\InvalidArgumentException::class, function() {
			new MaxLengthWithoutHtmlRule([3.56]);
		});

		$this->assertException(\InvalidArgumentException::class, function() {
			new MaxLengthWithoutHtmlRule([null]);
		});

		$this->assertException(\InvalidArgumentException::class, function() {
			new MaxLengthWithoutHtmlRule([true]);
		});
	}

	public function testInstanceAcceptsPositiveIntegerOrNumericStringAndCorrectlySetsMaxLength()
	{
		$max_length = 500;
		$ruleOne = new MaxLengthWithoutHtmlRule([$max_length]);
		$this->assertAttributeSame($max_length, 'max_length', $ruleOne);

		$max_length = 22;
		$ruleTwo = new MaxLengthWithoutHtmlRule([$max_length]);
		$this->assertAttributeSame($max_length, 'max_length', $ruleTwo);

		$max_length = '34';
		$ruleThree = new MaxLengthWithoutHtmlRule([$max_length]);
		$this->assertAttributeSame(34, 'max_length', $ruleThree);

		$max_length = '56';
		$ruleFour = new MaxLengthWithoutHtmlRule([$max_length]);
		$this->assertAttributeSame(56, 'max_length', $ruleFour);
	}

	public function testValidationRuleWorks()
	{
		// TODO: try a bunch of valid and invalid input and see if the rule passes
		// different lengths, with different amounts of HMTL etc.
	}

	public function testHtmlEntitiesAreTreatedAsSingleCharacters()
	{
		$entities = [
			// Printable ASCII (it seems &#32; or the space character has a length of 0 in DOMParser)
			'#33', '#34', '#35', '#36', '#37', 'amp', '#39', '#40', '#41', '#42', '#43', '#44', '#45', '#46', '#47', '#48', '#49', '#50', '#51', '#52', '#53', '#54', '#55', '#56', '#57', '#58', '#59', 'lt', '#61', 'gt', '#63', '#64', '#65', '#66', '#67', '#68', '#69', '#70', '#71', '#72', '#73', '#74', '#75', '#76', '#77', '#78', '#79', '#80', '#81', '#82', '#83', '#84', '#85', '#86', '#87', '#88', '#89', '#90', '#91', '#92', '#93', '#94', '#95', '#96', '#97', '#98', '#99', '#100', '#101', '#102', '#103', '#104', '#105', '#106', '#107', '#108', '#109', '#110', '#111', '#112', '#113', '#114', '#115', '#116', '#117', '#118', '#119', '#120', '#121', '#122', '#123', '#124', '#125', '#126',
			// ISO-8859-1
			'Agrave', 'Aacute', 'Acirc', 'Atilde', 'Auml', 'Aring', 'AElig', 'Ccedil', 'Egrave', 'Eacute', 'Ecirc', 'Euml', 'Igrave', 'Iacute', 'Icirc', 'Iuml', 'ETH', 'Ntilde', 'Ograve', 'Oacute', 'Ocirc', 'Otilde', 'Ouml', 'Oslash', 'Ugrave', 'Uacute', 'Ucirc', 'Uuml', 'Yacute', 'THORN', 'szlig', 'agrave', 'aacute', 'acirc', 'atilde', 'auml', 'aring', 'aelig', 'ccedil', 'egrave', 'eacute', 'ecirc', 'euml', 'igrave', 'iacute', 'icirc', 'iuml', 'eth', 'ntilde', 'ograve', 'oacute', 'ocirc', 'otilde', 'ouml', 'oslash', 'ugrave', 'uacute', 'ucirc', 'uuml', 'yacute', 'thorn', 'yuml',
			// ISO-8859-1 Symbols
			'nbsp', 'iexcl', 'cent', 'pound', 'curren', 'yen', 'brvbar', 'sect', 'uml', 'copy', 'ordf', 'laquo', 'not', 'shy', 'reg', 'macr', 'deg', 'plusmn', 'sup2', 'sup3', 'acute', 'micro', 'para', 'cedil', 'sup1', 'ordm', 'raquo', 'frac14', 'frac12', 'frac34', 'iquest', 'times', 'divide',
			// Math
			'forall', 'part', 'exist', 'empty', 'nabla', 'isin', 'notin', 'ni', 'prod', 'sum', 'minus', 'lowast', 'radic', 'prop', 'infin', 'ang', 'and', 'or', 'cap', 'cup', 'int', 'there4', 'sim', 'cong', 'asymp', 'ne', 'equiv', 'le', 'ge', 'sub', 'sup', 'nsub', 'sube', 'supe', 'oplus', 'otimes', 'perp', 'sdot',
			// Greek
			'Alpha', 'Beta', 'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Eta', 'Theta', 'Iota', 'Kappa', 'Lambda', 'Mu', 'Nu', 'Xi', 'Omicron', 'Pi', 'Rho', 'Sigma', 'Tau', 'Upsilon', 'Phi', 'Chi', 'Psi', 'Omega', 'alpha', 'beta', 'gamma', 'delta', 'epsilon', 'zeta', 'eta', 'theta', 'iota', 'kappa', 'lambda', 'mu', 'nu', 'xi', 'omicron', 'pi', 'rho', 'sigmaf', 'sigma', 'tau', 'upsilon', 'phi', 'chi', 'psi', 'omega', 'thetasym', 'upsih', 'piv',
			// Misc
			'OElig', 'oelig', 'Scaron', 'scaron', 'Yuml', 'fnof', 'circ', 'tilde', 'ensp', 'emsp', 'thinsp', 'zwnj', 'zwj', 'lrm', 'rlm', 'ndash', 'mdash', 'lsquo', 'rsquo', 'sbquo', 'ldquo', 'rdquo', 'bdquo', 'dagger', 'Dagger', 'bull', 'hellip', 'permil', 'prime', 'Prime', 'lsaquo', 'rsaquo', 'oline', 'euro', 'trade', 'larr', 'uarr', 'rarr', 'darr', 'harr', 'crarr', 'lceil', 'rceil', 'lfloor', 'rfloor', 'loz', 'spades', 'clubs', 'hearts', 'diams'
		];

		$input = ['test' => '&' . implode(';&', $entities) . ';'];

		$successful = Validator::make(
			$input,
			['test' => ['max_length_without_html:' . count($entities)]],
			['max_length_without_html' => MaxLengthWithoutHtmlRule::getMessage()]
		);

		$failing = Validator::make(
			$input,
			['test' => ['max_length_without_html:' . (count($entities) - 1)]],
			['max_length_without_html' => MaxLengthWithoutHtmlRule::getMessage()]
		);

		$this->assertTrue($successful->passes());
		$this->assertFalse($failing->passes());
	}

	public function testReplacerCorrectlyReplacesPlaceholder()
	{
		MaxLengthWithoutHtmlRule::register();

		$max_length = '4';

		try {
			Validator::make(
				['test' => '<p>hello</p>'],
				['test' => ['max_length_without_html:' . $max_length]],
				['max_length_without_html' => MaxLengthWithoutHtmlRule::getMessage()]
			)->validate();

			$this->fail('Should throw a valaidation exception.');
		}
		catch(ValidationException $e) {
			$error = $e->validator->errors()->get('test')[0];

			$this->assertThat(
				$error,
				$this->logicalAnd(
					$this->logicalNot($this->stringContains(':size')),
					$this->stringContains($max_length)
				)
			);
		}
	}
}
