<?php

namespace App\Validation\Rules;

use Illuminate\Support\Facades\Validator;

class MaxLengthWithoutHtmlRule
{
	/**
	 * The max length to accept.
	 *
	 * @var int
	 */
	protected $max_length;

	/**
	 * Require a certain number of parameters to be present (if set).
	 *
	 * @param  array  $parameters
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct($parameters = null)
	{
		if (!is_null($parameters) && count($parameters) < 1) {
			throw new \InvalidArgumentException(
				'"max_length_plaintext" validation rule requires at least 1 parameter.'
			);
		}

		$this->max_length = is_null($parameters[0]) ? 0 : (int) $parameters[0];
	}

	/**
	 * Determine if the length of the string is too large once stripped of HTML.
	 *
	 * @param  string  $attribute The name of the attribute being validated
	 * @param  string  $value  The value of the attribute
	 * @return bool
	 */
	public function passes($attribute, $value)
	{
		// Our length has to be multibyte "safe" to be consistent with the client-side
		return mb_strlen(
			// We decode html entities so things like &nbsp; don't count as several chars
			html_entity_decode(
				// Strip all HTML before decoding the HTML entities
				strip_tags($value),
				ENT_QUOTES | ENT_HTML5,
				'UTF-8'
			)
		) <= $this->max_length;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return 'The :attribute field may not be greater than :size characters.';
	}

	/**
	 * Add the validation rule (extension) and a placeholder replacer.
	 */
	public static function register()
	{
		Validator::extend('max_length_without_html', static::class . '@extension');
		Validator::replacer('max_length_without_html', static::class . '@replacer');
	}

	/**
	 * Creates a new instance of our rule object, to test against and returns if
	 * the rule passes based on the parameters supplied.
	 *
	 * @param  string  $attr The name of the attribute being validated
	 * @param  string  $value  The value of the attribute
	 * @param  array  $parameters The parameters passed to this rule
	 * @param  Illuminate\Validation\Validator $validator  The Validator instance
	 *
	 * @return  boolean  Whether this rule passes.
	 */
	public static function extension($attr, $value, $parameters, $validator)
	{
		return (new static($parameters))->passes($attr, $value);
	}

	/**
	 * The replacer that swaps ours size placeholder in our message with the actual
	 * value for this rule instance.
	 *
	 * @param  string  $message  The raw error message (with placeholders)
	 * @param  string  $attribute  The attribute being validated
	 * @param  string  $rule  The current rule being validated
	 * @param  array  $parameters The parameters passed to this rule
	 *
	 * @return  string  The error message with the placeholder replaced.
	 */
	public static function replacer($message, $attribute, $rule, $parameters)
	{
		return str_replace(':size', $parameters[0], $message);
	}

	/**
	 * A wrapper around this Rule object's "message" method, which is compatible
	 * with Laravel v5.5+.
	 *
	 * @return  string  The error message from our message method.
	 */
	public static function getMessage()
	{
		return (new static())->message();
	}

}
