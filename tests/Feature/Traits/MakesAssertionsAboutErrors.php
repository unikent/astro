<?php

namespace Tests\Feature\Traits;

/**
 * Utility methods to check if error json returned by failed API requests
 * is in the right format and contains the right errors.
 * @package Tests\Feature\Traits
 */
trait MakesAssertionsAboutErrors
{
	/**
	 * Is the given array of data a valid error response?
	 * Valid error json
	 * - MUST contain an 'errors' array with AT LEAST 1 error
	 * - Each error MUST contain a summary error 'message'
	 * - Each error MUST contain 'details'  - which can be anything...
	 * - For validation errors, details will be an array of 'field' => [error-details]
	 * @param string|array $data - Array representation of json response body to check or the actual json string.
	 * @param bool|array $require_details - Whether there must also be a 'details' section in the error response. If true, then at least one detail must exist. If an array, then each item in the array must be a key in the details array.
	 * @return bool - True if the data matches the schema for an error response.
	 */
	public function assertValidErrorResponseBody($data, $require_details = false)
	{
		$data = is_array($data) ? $data : json_decode($data, true);
		// An error response must include an 'errors' array with at least one error message.
		$this->assertTrue(is_array($data['errors']) && count($data['errors']) > 0, __METHOD__ . ' response payload does not contain errors array.');
		foreach($data['errors'] as $index => $error) {
			$this->assertTrue(strlen($error['message']) > 0, __METHOD__ . " error $index does not contain a message.");
			$this->assertTrue(array_key_exists('details', $error) || !$require_details);
			if(array_key_exists('details', $error) && is_array($require_details)) {
				// if we are testing for a validation error, details should be [ property => [error-messages] ]
				foreach ($error['details'] as $prop => $messages) {
					$this->assertTrue(is_array($messages) && !empty($messages), __METHOD__ . " error {$index}.{$prop} does not contain details");
					foreach ($messages as $msg_idx => $msg) {
						$this->assertTrue(strlen($msg) > 0, __METHOD__ . " error {$index}.{$prop}[{$msg}] is empty");
					}
				}
				if(is_array($require_details)) {
					foreach($require_details as $detail) {
						$this->assertArrayHasKey($detail, $error['details'], __METHOD__ . " error {$index}.details does not contain an entry for {$detail}");
					}
				}
			}
		}
	}
}