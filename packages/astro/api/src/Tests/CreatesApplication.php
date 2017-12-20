<?php

namespace Astro\API\Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
	/**
	 * Creates the application.
	 *
	 * @return \Illuminate\Foundation\Application
	 */
	public function createApplication()
	{
		$path = __DIR__ . '/../../../../../';
		$path .= 'bootstrap/app.php';
		$app = require $path;

		$app->make(Kernel::class)->bootstrap();

		return $app;
	}
}
