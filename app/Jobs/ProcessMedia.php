<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Media;

use Config;
use Intervention\Image\ImageManager;
use Intervention\Image\Exception\ImageException;

class ProcessMedia implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $media;
	/*
	base64 is used for a low-quality initial image on page load
	base64video is used for a low-quality initial placeholder image for videos (16:9)
	400x400 is used in the UI when choosing an image
	all other image processing happens on the media server when an image is requested
	*/
	protected $transforms = [
		 'base64', 'base64video', '400x400'
	];

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

	protected function transform($type, $img) {
		switch($type) {
			case '400x400':
				return $img->fit(400);
			case 'base64':
				return (string) $img
					->fit(50, 33)
					->blur(3)
					->encode('data-url');
			case 'base64video':
				return (string) $img
					->fit(50, 28)
					->blur(3)
					->encode('data-url');
		}
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
				$filename_no_ext
			);

			try {
				$manager = new ImageManager();
				$img = $manager->make($dir . '/' . $this->media->filename);

				$img->backup();

				$variants = [];

				foreach($this->transforms as $type) {
					if($type === 'base64' or $type === 'base64video') {
						$media = $this->transform($type, $img);
					}
					else {
						$append = '_' . $type . '.jpg';
						$media = $filename_no_ext . $append;
						$this
							->transform($type, $img)
							->interlace()
							->save($filepath . $append, 70);
					}

					$variants[$type] = $media;
					$img->reset();
				}

				$this->media->variants = $variants;
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
