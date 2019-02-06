<?php

namespace Tests\Unit\Helpers;

use Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use App\Helpers\CachedHttpClient;
use GuzzleHttp\Handler\MockHandler;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CachedHttpClientTest extends TestCase
{

	protected $httpHandler;
	protected $cachingClient;

	public function setUp()
	{
		parent::setUp();
		Cache::flush(); // remove anything in laravels cache
		$this->httpHandler = new MockHandler();
		$this->cachingClient = new CachedHttpClient(
			null,
			$this->httpHandler,
			null
		);
	}


	/**
	 * @group cfc
	 * @test
	 */
	public function repeatedCallToHttpGetReturnsValuesFromCache()
	{
		// set up some expected api responses
		$sciences = json_encode([
			'maths' => 'https://www.kent.ac.uk/smsas/',
			'physical sciences'=> 'https://www.kent.ac.uk/physical-sciences',
			'computing' => 'http://www.cs.kent.ac.uk/'
		]);


		$humanities = json_encode([
			'arts' => 'https://www.kent.ac.uk/arts/',
			'history'=> 'https://www.kent.ac.uk/history/',
			'music' => 'https://www.kent.ac.uk/smfa/'
		]);

		// add mocked responses to http client. First call gives sciences, second humanities
		$this->httpHandler->append(
			new Response(200, ['Content-Type' => 'application/json'], $sciences)
		);
		$this->httpHandler->append(
			new Response(200, ['Content-Type' => 'application/json'], $humanities)
		);

		// given we have made a successful call to an api
		$minutesToCache = 10;
		$url = 'http://api.example.com/departmentsList';
		$firstResult = $this->cachingClient->get($url, $minutesToCache);

		// when we make a repeated call
		$secondResult = $this->cachingClient->get($url, $minutesToCache);

		// then expect the data is returned from the cache
		// we know it is cached because the second call would have returned different results
		$this->assertEquals($firstResult, $secondResult);
	}

	/**
	 * @group cfc
	 * @test
	 */
	public function repeatedCallToHttpRetrievesDataFromHttpIfCacheHasExpired()
	{
		// set up some expected api responses
		$sciences = json_encode([
			'maths' => 'https://www.kent.ac.uk/smsas/',
			'physical sciences'=> 'https://www.kent.ac.uk/physical-sciences',
			'computing' => 'http://www.cs.kent.ac.uk/'
		]);


		$humanities = json_encode([
			'arts' => 'https://www.kent.ac.uk/arts/',
			'history'=> 'https://www.kent.ac.uk/history/',
			'music' => 'https://www.kent.ac.uk/smfa/'
		]);

		// add mocked responses to http client. First call gives sciences, second humanities
		$this->httpHandler->append(
			new Response(200, ['Content-Type' => 'application/json'], $sciences)
		);
		$this->httpHandler->append(
			new Response(200, ['Content-Type' => 'application/json'], $humanities)
		);


		// given we have made a successful call to an api
		$minutesToCache = 5;
		$url = 'http://api.example.com/departmentsList';
		$firstResult = $this->cachingClient->get($url, $minutesToCache);

		/*
		laravel switches to the array cache driver when runnings tests and that never
		sets an expiry time!

		So, for let's manually simulate the item expiring from the cache.
		Which makes the usefulness of this test somewhat limited.
		*/
		Cache::forget($url);

		// when we make a repeated call
		$secondResult = $this->cachingClient->get($url, $minutesToCache);

		// then expect the data is not returned from the cache
		// so the results should be different
		$this->assertNotEquals($firstResult, $secondResult);
	}

	/**
	 * @group cfc
	 * @test
	 */
	public function callToHttpThrowExceptionOnHttpError()
	{
		// given we have an empty cache
		// when we call a non existance endpoint
		$this->httpHandler->append(
			new RequestException("Error Communicating with Server", new Request('GET', 'test'))
		);


		$url = 'https://this-url-does-not-exist.diamonds';
		$minutesToCache = 0;

		// then an exception this thrown for the request
		$this->expectException(RequestException::class);
		$result = $this->cachingClient->get($url, $minutesToCache);
	}
}
