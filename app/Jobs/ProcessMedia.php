<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Media;
// use App\MediaProcessor;

class ProcessMedia implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $media;

	/**
	 * Create a new job instance.
	 *
	 * @param  Media  $media
	 * @return void
	 */
	public function __construct(Media $media)
	{
		$this->media = $media;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() // MediaProcessor $processor
	{
		//
	}
}
