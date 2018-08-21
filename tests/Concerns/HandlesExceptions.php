<?php

namespace Tests\Concerns;

trait HandlesExceptions
{
	/**
	 * @param string $type
	 * @param callable $closure
	 * @param string|null $message
	 */
	protected function assertException($type, callable $closure, $message = null)
	{
		$exception = null;

		try {
			call_user_func($closure);
		}
		catch (\Exception $e) {
			$exception = $e;
		}

		self::assertThat($exception, new \PHPUnit_Framework_Constraint_Exception($type));

		if (!is_null($message)) {
			self::assertThat($exception, new \PHPUnit_Framework_Constraint_ExceptionMessage($message));
		}
	}
}
