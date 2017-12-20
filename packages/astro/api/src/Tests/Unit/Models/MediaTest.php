<?php
namespace Astro\API\Tests\Unit\Models;

use Config;
use Mockery;
use File;
use Astro\API\Tests\TestCase;
use Astro\API\Models\Site;
use Astro\API\Models\Media;
use Astro\API\Tests\FileUploadTrait;
use Astro\API\Tests\FileCleanupTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class MediaTest extends TestCase
{

	use FileUploadTrait, FileCleanupTrait;

	public function setUp()
	{
		parent::setUp();

		Config::set('app.media_url', 'public/tests/media');
		Config::set('app.media_path', storage_path('tests/media'));
	}

	/**
	 * @test
	 * @group media
	 */
	public function scopeSites_ReturnsOnlyItemsAssociatedWithTheGivenSites()
	{
		$sites = factory(Site::class, 3)->create();

		$file = static::setupFile('media', 'image.jpg');
		$m1 = factory(Media::class)->create([ 'format' => 'image', 'file' => $file ]);
		$m1->sites()->attach($sites[0]);

		$file = static::setupFile('media', 'document.pdf');
		$m2 = factory(Media::class)->create([ 'format' => 'document', 'file' => $file ]);
		$m2->sites()->attach($sites[1]);

		$file = static::setupFile('media', 'audio.mp3');
		$m3 = factory(Media::class)->create([ 'format' => 'audio', 'file' => $file ]);
		$m3->sites()->attach($sites[2]);

		$ids = Media::query()->sites([ $sites[1]->getKey(), $sites[2]->getKey() ])->get()->pluck('id');

		$this->assertCount(2, $ids);
		$this->assertContains($m2->getKey(), $ids);
		$this->assertContains($m3->getKey(), $ids);
	}

	/**
	 * @test
	 * @group media
	 */
	public function scopeTypes_ReturnsOnlyItemsMatchingGivenTypes()
	{
		$file = static::setupFile('media', 'image.jpg');
		$m1 = factory(Media::class)->create([ 'type' => 'image', 'file' => $file ]);

		$file = static::setupFile('media', 'document.pdf');
		$m2 = factory(Media::class)->create([ 'type' => 'document', 'file' => $file ]);

		$file = static::setupFile('media', 'audio.mp3');
		$m3 = factory(Media::class)->create([ 'type' => 'audio', 'file' => $file ]);

		$ids = Media::query()->types([ 'image', 'document' ])->get()->pluck('id');

		$this->assertCount(2, $ids);
		$this->assertContains($m1->getKey(), $ids);
		$this->assertContains($m2->getKey(), $ids);
	}

	/**
	 * @test
	 * @group media
	 */
	public function scopeMimeTypes_ReturnsOnlyItemsMatchingGivenMimeTypes()
	{
		$file = static::setupFile('media', 'image.jpg');
		$m1 = factory(Media::class)->create([ 'format' => 'image', 'file' => $file ]);

		$file = static::setupFile('media', 'document.pdf');
		$m2 = factory(Media::class)->create([ 'format' => 'document', 'file' => $file ]);

		$file = static::setupFile('media', 'audio.mp3');
		$m3 = factory(Media::class)->create([ 'format' => 'audio', 'file' => $file ]);

		$ids = Media::query()->mimeTypes([ 'image/jpeg', 'audio/mpeg' ])->get()->pluck('id');

		$this->assertCount(2, $ids);
		$this->assertContains($m1->getKey(), $ids);
		$this->assertContains($m3->getKey(), $ids);
	}

	/**
	 * @test
	 * @group media
	 */
	public function setFilenameAttribute_StripsUnsafeCharactersButLeavesExtensionIntact()
	{
		$media = new Media;
		$media->filename = 'foo!bar123\..\\;99.doc';
		$this->assertEquals('foobar123.99.doc', $media->filename);
	}

	/**
	 * @test
	 * @group media
	 */
	public function getFileAttribute_WhenNotPersistedAndNotPopulated_ReturnsNull()
	{
		$media = new Media;
		$this->assertNull($media->file);
	}

	/**
	 * @test
	 * @group media
	 */
	public function getFileAttribute_WhenNotPersistedAndPopulated_ReturnsFileObject()
	{
		$media = new Media;
		$media->file = $this->setupFile('media', 'image.jpg');

		$this->assertInstanceOf(SymfonyFile::class, $media->file);
	}

	/**
	 * @test
	 * @group media
	 */
	public function getFileAttribute_WhenPersisted_ReturnsFileObject()
	{
		$media = factory(Media::class)->create([ 'file' => $this->setupFile('media', 'image.jpg') ]);

		$media = Media::all()->last();
		$this->assertInstanceOf(SymfonyFile::class, $media->file);
	}

	/**
	 * @test
	 * @group media
	 */
	public function getSrcAttribute_WithoutIdAndFilename_ReturnsEmptyString()
	{
		$media = new Media;
		$this->assertEquals('', $media->url);
	}

	/**
	 * @test
	 * @group media
	 */
	public function getSrcAttribute_WithIdButWithoutFilename_ReturnsEmptyString()
	{
		$media = new Media;
		$media->id = 123;

		$this->assertEquals('', $media->url);
	}

	/**
	 * @test
	 * @group media
	 */
	public function getSrcAttribute_WithFilenameButWithoutId_ReturnsEmptyString()
	{
		$media = new Media;
		$media->filename = 'filename123.doc';

		$this->assertEquals('', $media->url);
	}

	/**
	 * @test
	 * @group media
	 */
	public function getSrcAttribute_WithIdAndFilename_ReturnsPath()
	{
		$media = new Media;
		$media->id = 123;
		$media->filename = 'filename123.doc';

		$this->assertEquals('public/tests/media/123/filename123.doc', $media->url);
	}

	/**
	 * @test
	 * @group media
	 */
	public function extractMeta_WithFile_ReturnsArray()
	{
		$file = static::setupFile('media', 'image.jpg');

		$meta = Media::extractMeta($file);
		$this->assertTrue(is_array($meta));
	}

	/**
	 * @test
	 * @group media
	 */
	public function extractMeta_WithImage_ReturnsSize()
	{
		$file = static::setupFile('media', 'image.jpg');

		$meta = Media::extractMeta($file);
		$this->assertArrayHasKey('filesize', $meta);
		$this->assertEquals(55451, $meta['filesize']);
	}

	/**
	 * @test
	 * @group media
	 */
	public function extractMeta_WithImage_ReturnsFormat()
	{
		$file = static::setupFile('media', 'image.jpg');

		$meta = Media::extractMeta($file);
		$this->assertArrayHasKey('format', $meta);
		$this->assertEquals('jpg', $meta['format']);
	}

	/**
	 * @test
	 * @group media
	 */
	public function extractMeta_WithImage_ReturnsMimeType()
	{
		$file = static::setupFile('media', 'image.jpg');

		$meta = Media::extractMeta($file);
		$this->assertArrayHasKey('mime_type', $meta);
		$this->assertEquals('image/jpeg', $meta['mime_type']);
	}

	/**
	 * @test
	 * @group media
	 */
	public function extractMeta_WithImage_ReturnsWidth()
	{
		$file = static::setupFile('media', 'image.jpg');

		$meta = Media::extractMeta($file);
		$this->assertArrayHasKey('width', $meta);
		$this->assertEquals(800, $meta['width']);
	}

	/**
	 * @test
	 * @group media
	 */
	public function extractMeta_WithImage_ReturnsHeight()
	{
		$file = static::setupFile('media', 'image.jpg');

		$meta = Media::extractMeta($file);
		$this->assertArrayHasKey('height', $meta);
		$this->assertEquals(600, $meta['height']);
	}

	/**
	 * @test
	 * @group media
	 */
	public function extractMeta_WithImage_ReturnsAspectRatio()
	{
		$file = static::setupFile('media', 'image.jpg');

		$meta = Media::extractMeta($file);
		$this->assertArrayHasKey('aspect_ratio', $meta);
		$this->assertEquals(1.3333333333333, $meta['aspect_ratio']);
	}

	/**
	 * @test
	 * @group media
	 */
	public function hash_WithFile_ReturnsHash()
	{
		$file = static::setupFile('media', 'image.jpg');
		$this->assertEquals(sha1_file($file->getRealPath()), Media::hash($file));
	}

	/**
	 * @test
	 * @group media
	 */
	public function findByHash_WhenHashExists_ReturnsItem()
	{
		$file = static::setupFileUpload('media', 'image.jpg');
		$hash = Media::hash($file);

		$media = factory(Media::class)->create([ 'file' => $file ]);

		$result = Media::findByHash($hash);
		$this->assertEquals($media->getKey(), $result->getKey());
	}

	/**
	 * @test
	 * @group media
	 */
	public function findByHash_WhenHashDoesNotExist_ReturnsNull()
	{
		$file = static::setupFile('media', 'image.jpg');
		$hash = Media::hash($file);

		$this->assertNull(Media::findByHash($hash));
	}

	/**
	 * @test
	 * @group media
	 */
	public function findByHashOrFail_WhenHashExists_ReturnsItem()
	{
		$file = static::setupFileUpload('media', 'image.jpg');
		$hash = Media::hash($file);

		$media = factory(Media::class)->create([ 'file' => $file ]);

		$result = Media::findByHashOrFail($hash);
		$this->assertEquals($media->getKey(), $result->getKey());
	}

	/**
	 * @test
	 * @group media
	 */
	public function findByHashOrFail_WhenHashDoesNotExist_ReturnsNull()
	{
		$file = static::setupFile('media', 'image.jpg');
		$hash = Media::hash($file);

		$this->expectException(ModelNotFoundException::class);

		Media::findByHashOrFail($hash);
	}

	/**
	 * @test
	 * @group media
	 */
	public function save_WhenSuccessful_PersistsModel()
	{
		$count = Media::count();

		$file = static::setupFileUpload('media', 'image.jpg');
		$media = factory(Media::class)->create([ 'file' => $file ]);

		$this->assertEquals($count + 1, Media::count());
	}

	/**
	 * @test
	 * @group media
	 */
	public function save_WhenSuccessful_ReturnsTrue()
	{
		$file = static::setupFileUpload('media', 'image.jpg');
		$media = factory(Media::class)->make([ 'file' => $file ]);

		$this->assertTrue($media->save());
	}

	/**
	 * @test
	 * @group media
	 */
	public function save_WithUploadedFile_MovesFileToDestinationDirectory()
	{
		$file = static::setupFileUpload('media', 'image.jpg');
		$media = factory(Media::class)->create([ 'file' => $file ]);

		$this->assertFalse(file_exists($file->getPath() . '/' . $file->getFilename()));
		$this->assertTrue(file_exists(storage_path('tests/media/' . $media->getKey() . '/' . $file->getClientOriginalName())));
	}

	/**
	 * @test
	 * @group media
	 */
	public function save_WithFileAndPathIsNotDestination_CopiesFileToDestinationDirectory()
	{
		$file = static::setupFile('media', 'image.jpg');
		$media = factory(Media::class)->create([ 'file' => $file ]);

		$this->assertTrue(file_exists($file->getPath() . '/' . $file->getFilename()));
		$this->assertTrue(file_exists(storage_path('tests/media/' . $media->getKey() . '/' . $media->filename)));
	}

	/**
	 * @test
	 * @group media
	 * @todo Figure out what this is testing and if it ever worked.
	 */
	// public function save_WithFileAndPathIsDestination_DoesNotCopyFile()
	// {
	// 	$file = static::setupFile('media', 'image.jpg');
	// 	$media = factory(Media::class)->create([ 'file' => $file ]);

	// 	dd(
	// 		$file->getPath() . '/' . $file->getFilename(),
	// 		storage_path('tests/media/' . $media->getKey() . '/' . $media->filename)
	// 	);

	// 	$fs = Mockery::mock(File::getFacadeRoot())->makePartial();

	// 	$fs->shouldNotReceive('copy');
	// 	File::swap($fs);

	// 	$media->save();
	// }

	/**
	 * @test
	 * @group media
	 */
	public function save_WithFile_SetsFilename()
	{
		$file = static::setupFile('media', 'image.jpg');
		$media = factory(Media::class)->create([ 'file' => $file ]);

		$this->assertEquals($file->getFilename(), $media->filename);
	}

	/**
	 * @test
	 * @group media
	 */
	public function save_WithUploadedFile_SetsFilenameToClientOriginal()
	{
		$file = static::setupFileUpload('media', 'image.jpg');
		$media = factory(Media::class)->create([ 'file' => $file ]);

		$this->assertEquals($file->getClientOriginalName(), $media->filename);
	}

	/**
	 * @test
	 * @group media
	 */
	public function save_WithFile_HashesFile()
	{
		$file = static::setupFile('media', 'image.jpg');
		$hash = Media::hash($file);

		$media = factory(Media::class)->create([ 'file' => $file ]);
		$this->assertEquals($hash, $media->hash);
	}

	/**
	 * @test
	 * @group media
	 */
	public function save_WithUploadedFile_HashesFile()
	{
		$file = static::setupFileUpload('media', 'image.jpg');
		$hash = Media::hash($file);

		$media = factory(Media::class)->create([ 'file' => $file ]);
		$this->assertEquals($hash, $media->hash);
	}

	/**
	 * @test
	 * @group media
	 */
	public function save_WithFile_ExtractsAndPersistsMetadata()
	{
		$file = static::setupFile('media', 'image.jpg');
		$media = factory(Media::class)->create([ 'file' => $file ]);

		$this->assertNotEmpty($media->filesize);
		$this->assertNotEmpty($media->width);
		$this->assertNotEmpty($media->height);
		$this->assertNotEmpty($media->format);
		$this->assertNotEmpty($media->mime_type);
		$this->assertNotEmpty($media->aspect_ratio);
	}

	/**
	 * @test
	 * @group media
	 */
	public function save_WithUploadedFile_ExtractsAndPersistsMetadata()
	{
		$file = static::setupFileUpload('media', 'image.jpg');
		$media = factory(Media::class)->create([ 'file' => $file ]);

		$this->assertNotEmpty($media->filesize);
		$this->assertNotEmpty($media->width);
		$this->assertNotEmpty($media->height);
		$this->assertNotEmpty($media->format);
		$this->assertNotEmpty($media->mime_type);
		$this->assertNotEmpty($media->aspect_ratio);
	}

	/**
	 * @test
	 * @group media
	 */
	public function delete_DoesNotRemoveDirectoryFromDisk()
	{
		$file = static::setupFile('media', 'image.jpg');
		$media = factory(Media::class)->create([ 'file' => $file ]);

		$media->delete();
		$this->assertTrue(is_dir(storage_path('tests/media/' . $media->getKey() . '/')));
	}

	/**
	 * @test
	 * @group media
	 */
	public function forceDelete_RemovesModelFromDatabase()
	{
		$file = static::setupFile('media', 'image.jpg');
		$media = factory(Media::class)->create([ 'file' => $file ]);

		$media->forceDelete();
		$this->assertNull(Media::find($media->getKey()));
	}

	/**
	 * @test
	 * @group media
	 */
	public function forceDelete_RemovesDirectoryFromDisk()
	{
		$file = static::setupFile('media', 'image.jpg');
		$media = factory(Media::class)->create([ 'file' => $file ]);

		$media->forceDelete();
		$this->assertFalse(is_dir(storage_path('tests/media/' . $media->getKey() . '/')));
	}

}
