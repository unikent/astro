<?php
namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\RequestException;

class CachedHttpClient
{
	protected $httpClient;
	protected $httpHandler;
	protected $httpOptions;

	public function __construct(
		$httpClient = null,
		$httpHandler = null,
		$httpOptions = null
	) {
		// default to guzzle unless specified
		if ($httpHandler !== null) {
			$this->httpHandler = $httpHandler;
		}

		if ($httpClient !== null) {
			$this->httpClient = $httpClient;
		} else {
			if ($httpHandler !== null) {
				$this->httpClient = new Client(['handler' => $this->httpHandler]);
			} else {
				$this->httpClient = new Client();
			}
		}

		if ($httpOptions !== null) {
			$this->httpOptions = $httpOptions;
		} else {
			// set sensible default options
			$this->httpOptions = [
				'http_errors' => true
			];
		}

		if (config('definitions.proxy_url')) {
			$this->httpOptions['proxy'] = [
				'https' => config('definitions.proxy_url'),
			];
		}
	}


	/**
	* Get
	*
	* perform http get request and cache the results for $minutesToCache minutes
	* using the $url as the key
	*
	*
	* @param mixed $url - the http endpoint to make the get request to
	* @param mixed $minutesToCache - number of seconds to cache the result
	* @return string - the request body
	*
	* @throws GuzzleHttp\Exception\RequestException on http error
	*/
	public function get($url, $minutesToCache)
	{
		$options = Cache::remember($url, $minutesToCache, function () use ($url) {
			$guzzleOptions = [];
			try {
				$res = $this->httpClient->request(
					'GET',
					$url,
					$this->httpOptions
				);
			} catch (RequestException $e) {
				Log::error("Failed to contact API for dynamic options $url");
				throw ($e);
			}

			$body = $res->getBody();
			return $body->getContents();
		});

		return $options;
	}
}
