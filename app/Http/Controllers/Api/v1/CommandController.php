<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Controller for running miscellaneous commands
 * @package App\Http\Controllers\Api\v1
 */
class CommandController extends ApiController
{
	/**
	 * POST /api/v1/command/swapsites
	 * Swaps the hosts of two sites.
	 * The sites MUST have different hosts.
	 * The switched host sites must not have the same host and path as any existing site.
	 * @param Request $request
	 * @return Response
	 */
	public function swapsites(Request $request)
	{
		$this->authorize('swapsites', Site::class);
		$from = Site::findOrFail($request->get('from_id'));
		$to = Site::findOrfail($request->get('to_id'));
		$output = new BufferedOutput();
		if ($from->host === $to->host) {
			throw new \InvalidArgumentException('Cannot switch domains of two sites with the same domain name: ' . $from->host);
		}
		$tmp_host = microtime(true);
		$tmp_from_result = Artisan::call('astro:updatesiteurl', [
			'--site-id' => $from->id,
			'--new-host' => $tmp_host,
			'--new-path' => $from->path,
			'--yes' => true,
			'--republish' => true,
		], $output);
		if ($tmp_from_result) {
			throw new \Exception('Failed to switch domains (step 1)');
		}
		$to_result = Artisan::call('astro:updatesiteurl', [
			'--site-id' => $to->id,
			'--new-host' => $from->host,
			'--new-path' => $to->path,
			'--yes' => true,
			'--republish' => true,
		], $output);
		if ($to_result) {
			throw new \Exception('Failed to switch domains (step 2)');
		}
		$from_result = Artisan::call('astro:updatesiteurl', [
			'--site-id' => $from->id,
			'--new-host' => $to->host,
			'--new-path' => $from->path,
			'--yes' => true,
			'--republish' => true,
		], $output);
		if ($from_result) {
			throw new \Exception('Failed to switch domains (step 3)');
		}
		exit($output->fetch());
	}
}
