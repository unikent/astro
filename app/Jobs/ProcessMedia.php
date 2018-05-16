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
	protected $transforms = [
		 'base64', '400x400', '400w', '800w', '2000w'
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
			case '400w':
				return $img->resize(400, null, function($constraint) {
					$constraint->aspectRatio();
				});
			case '800w':
				return $img->resize(800, null, function($constraint) {
					$constraint->aspectRatio();
					// $constraint->upsize();
				});
			case '2000w':
				return $img->resize(2000, null, function($constraint) {
					$constraint->aspectRatio();
					// $constraint->upsize();
				});
			case 'base64':
				return (string) $img
					->resize(50, null, function($constraint) {
						$constraint->aspectRatio();
					})
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
					if($type === 'base64') {
						$media = $this->transform($type, $img);
					}
					else {
						$append = '_' . $type . '.jpg';
						$media = $filename_no_ext . $append;
						$this
							->transform($type, $img)
							->interlace()
							->save($filepath . $append);
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
