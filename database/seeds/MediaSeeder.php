<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Filesystem\Filesystem;

use Astro\API\Models\Media;

class MediaSeeder extends Seeder
{
	protected $filesystem;
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$this->fileSystem = new Filesystem();

		$uploadDir = storage_path('app/public/' . config('app.media_path'));

		$this->cleanFiles($uploadDir);

		$faker = Faker::create();
		$getID3 = new \getID3();

		foreach(range(1, 200) as $index)
		{
			$startDate = '2016-09-01 00:00:00';
			$endDate = '2017-03-25 00:00:00';
			$dir = $uploadDir . '/' . $index;

			$createdDate = $faker->dateTimeBetween($startDate, $endDate);

			if(!is_dir($dir)) {
				$this->fileSystem->makeDirectory($dir);
			}

			$fileName = $faker->file(
				resource_path('assets/support/images/seed'),
				$dir,
				false
			);

			$meta = $getID3->analyze($dir . '/' . $fileName);

			$media = array_merge(
				[
					'type'       => $faker->randomElement(['image', 'document', 'video', 'audio']),
					'name'       => $fileName,
					'sha1'       => $faker->regexify('[a-z0-9]{40}'),
					'created_at' => $createdDate,
					'updated_at' => $faker->dateTimeBetween($createdDate, $endDate)
				],
				Media::transformMetaData($meta)
			);

			DB::table('media')->insert($media);
		}
	}

	private function cleanFiles($uploadDir)
	{
		$this->fileSystem = new Filesystem();

		if(!is_dir($uploadDir)) {
			$this->fileSystem->makeDirectory($uploadDir);
		}

		$this->fileSystem->cleanDirectory($uploadDir);
	}
}
