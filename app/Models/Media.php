<?php

namespace App\Models;

use DB;
use Config;
use getID3;
use Exception;
use File as FS;
use App\Models\Traits\Tracked;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Media extends Model
{
	use Tracked, SoftDeletes;

	protected $fillable = [
		'type',
		'filename',
		'hash',

		'filesize',
		'format',
		'mime_type',

		'width',
		'height',
		'aspect_ratio',
		'duration'
	];

	protected $appends = [
		'src',
		'file',
	];

	protected $file;

	protected $fileUrl;
	protected $filePath;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct($attributes = []){
        parent::__construct($attributes);

		$this->fileUrl = Config::get('app.media_url');
		$this->filePath = Config::get('app.media_path');
    }

	/**
	 * Sanitizes the filename to a safe set of characters
	 *
	 * @param string $value
	 * @return void
	 */
	public function setFilenameAttribute($value)
	{
		$value = preg_replace('/[^\w\-\.]+/', '', $value);
		$value = preg_replace('/[\.]{2,}/', '.', $value);

		$this->attributes['filename'] = $value;
	}

	/**
	 * Sets $file.
	 *
	 * @param File $file
	 * @return void
	 */
	public function setFileAttribute(File $file)
	{
		$this->file = $file;
	}

	/**
	 * Attempts to load a File object, and returns the value of $file.
	 *
	 * @param File $file
	 * @return void
	 */
	public function getFileAttribute()
	{
		if(!is_a($this->file, File::class)){
			$this->loadFile();
		}

		return $this->file;
	}

	/**
	 * When persisted, initializes a File object and sets $file.
	 * @return void
	 */
	protected function loadFile()
	{
		if($this->exists){
			$this->setFileAttribute(new File($this->filePath . '/' . $this->id . '/' . $this->filename));
		}
	}


	/**
	 * Returns an HTML-friendly src attribute
	 * @return string
	 */
	public function getSrcAttribute()
	{
		return ($this->id && !empty($this->filename)) ? sprintf('%s/%d/%s', $this->fileUrl, $this->id, $this->filename) : '';
	}

    /**
     * Save the model to the database.
     *
     * We wrap the save operation in a transaction, to ensure that we have an ID assigned
     * before writing the file to disk. Any failure should bail out safely.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
    	DB::beginTransaction();

    	try {
			if(is_a($this->file, File::class)){
				$this->hash = static::hash($this->file);
				$this->filename = $this->file->getFilename();

				$this->fill(static::extractMeta($this->file));
			}

			$saved = parent::save();

			if(is_a($this->file, File::class)){
				$path = sprintf('%s/%d', $this->filePath, $this->id);

				if(is_a($this->file, UploadedFile::class)){
					$this->filename = $this->file->getClientOriginalName();
					$this->file->move($path, $this->filename);

				// Ensure that we don't move the File if it is already in the right place
				} elseif(!preg_match('#^' . $this->filePath . '#', $this->file->getPath())) {
					$this->filename = $this->file->getFilename();

					if(!FS::isDirectory($path)){
						FS::makeDirectory($path, 493, true);
					}

					FS::copy($this->file->getPath() . '/' . $this->file->getFilename(), $path . '/' . $this->filename);
				}

				$saved = parent::save();
				$this->loadFile(); // Reload the File as location on disk has changed.
			}

			DB::commit();
			return $saved;
		} catch(Exception $e){
			DB::rollBack();
			throw $e;
		}
    }


    /**
     * Force a hard delete on a soft deleted model.
     *
     * This method protects developers from running forceDelete when trait is missing.
     *
     * @return bool|null
     */
    public function forceDelete()
    {
      	DB::beginTransaction();

    	try {
    		FS::deleteDirectory($this->filePath);
			parent::forceDelete();

			DB::commit();
		} catch(Exception $e){
			DB::rollBack();
			throw $e;
		}
    }


    /**
     * Transforms the getID3 metadata into something a little friendlier...
     *
     * @param  array $meta
     * @return array
     */
	public static function extractMeta(File $file)
	{
		$meta = (new getID3)->analyze($file->getRealPath());

		$get = function($key, $default = null) use ($meta) {
			return array_get($meta, $key, $default);
		};

		$x = $get('video.resolution_x');
		$y = $get('video.resolution_y');
		$aspect_ratio = isset($x, $y) && $y > 0 ? $x / $y : null; // scared of zeros

		return [
			'filesize'     => $get('filesize'),
			'format'       => $get('fileformat'),
			'mime_type'    => $get('mime_type'),
			'width'        => $x,
			'height'       => $y,
			'aspect_ratio' => $aspect_ratio,
			'duration'     => $get('playtime_seconds')
		];
	}

	/**
	 * Attempts to retrieve a Media item using its file hash
	 *
	 * @param  string $hash
	 * @return Media|null
	 */
	public static function findByHash($hash)
	{
		return static::where('hash', '=', $hash)->first();
	}

	/**
	 * Hashes a file for use with the Media model
	 *
	 * @param  File   $file
	 * @return string
	 */
	public static function hash(File $file)
	{
		return sha1_file($file->getRealPath());
	}

}
