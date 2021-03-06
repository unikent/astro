<?php
namespace Tests\Unit\Http;

use Tests\TestCase;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;

abstract class HttpTestCase extends TestCase {

	use InteractsWithAuthentication, MakesHttpRequests;

}
