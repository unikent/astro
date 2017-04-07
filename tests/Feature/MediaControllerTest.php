<?php

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Filesystem\Filesystem;

class MediaControllerTest extends TestCase
{
	use WithoutMiddleware;

	protected $uploadedFile;

	public function test_media_can_be_added()
	{
		$filePath = resource_path('assets/support/images/valid-image-file.jpg');
		$sha1 = sha1_file($filePath);

		$upload = new UploadedFile(
			$filePath,
			'valid-image-file.jpg',
			'image/jpeg',
			filesize($filePath),
			null,
			true
		);

		$response = $this->postJson('api/media', [
			'upload' => $upload
		]);

		$response->assertStatus(200);

		$response->assertJsonStructure([
			'data' => [
				'id',
				'name',
				'sha1',
				'size',
				'format',
				'mime_type',
				'width',
				'height',
				'aspect_ratio',
				'duration'
			]
		]);

		$this->assertDatabaseHas('media', [
			'sha1' => $sha1
		]);

		$responseData = $response->json();

		$this->uploadedFile = storage_path(
			'app/public/uploads/' . $responseData['data']['id'] . '/valid-image-file.jpg'
		);

		$this->assertFileExists($this->uploadedFile);
	}

	/**
	 * Clean up the testing environment before the next test.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		(new FileSystem())->delete($this->uploadedFile);
		unset($this->uploadedFile);

		parent::tearDown();
	}
}
