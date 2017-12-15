<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Astro\API\Models\Media;

use Config;
use Intervention\Image\ImageManager;
use Intervention\Image\Exception\ImageException;

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
	public function handle()
	{
		// $this->cacheKey = 'processing-media-'.$this->media->id;

		// if(Cache::get($this->cacheKey) !== null)
		// {
		// 	Log::info('Job in progress. Aborting new request.');
		// 	return $this->media;
		// }

		// Cache::forever($this->cacheKey, 1);

		if($this->media->type === 'image')
		{
			$dir = sprintf(
				'%s/%d',
				Config::get('app.media_path'),
				$this->media->id
			);

			$filename_no_ext = pathinfo($this->media->filename, PATHINFO_FILENAME);

			$filepath = sprintf(
				'%s/%s',
				$dir,
				$filename_no_ext . '_400x400.jpg'
			);

			try {
				$manager = new ImageManager();
				$img = $manager->make($dir . '/' . $this->media->filename);

				$img->backup();

				$img->fit(400)->save($filepath);

				$img->reset();

				$base64Img = (
					(string) $img
						->resize(50, null, function($constraint) {
							$constraint->aspectRatio();
						})
						->blur(5)
						->encode('data-url')
				);

				$this->media->variants = [
					'400x400' => $filename_no_ext . '_400x400.jpg',
					'base64' => $base64Img
				];
			}
			catch(ImageException $e) {
				$this->media->variants = new \ArrayObject();
			}

			$this->media->save();
		}

		// handle exceptions and delete the cache key

		// Cache::forget($this->cacheKey);
	}
}
