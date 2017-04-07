<?php

namespace App\Models;

use App\Models\Traits\Tracked;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Media extends Model
{
	use Tracked, SoftDeletes;

	protected
		$fillable = [
			'type',
			'name',
			'size',
			'sha1',
			'format',
			'mime_type',
			'width',
			'height',
			'aspect_ratio',
			'duration'
		],
		$appends = [
			'src'
		],
		$sortable = [
			'id',
			'title',
			'file_name',
			'file_size',
			'created_at',
			'updated_at'
		],
		$file,
		$hash;

	public function setFile(UploadedFile $file)
	{
		$this->file = $file;
	}

	public function save(array $options = [])
	{
		DB::beginTransaction();

		$filePath = $this->file->getPathname();
		$fileName = str_slug($this->file->getClientOriginalName(), '.');

		$id3 = new \getID3();
		$fileInfo = $id3->analyze($filePath);

		$metaData = $this->transformMetaData($fileInfo);

		$this->fill(array_merge(
			[
				'name' => $fileName,
				'sha1' => $this->hash
			],
			$metaData
		));

		$saved = parent::save();

		if(!$saved)
		{
			return false;
		}

		if(!$this->saveFile($this->id, $fileName))
		{
			DB::rollBack();
			return false;
		}

		DB::commit();

		return $saved;
	}

	public function exists()
	{
		$filePath = $this->file->getPathname();
		$this->hash = sha1_file($filePath);

		// TODO: if hashing fails return an error
		// if(!$this->hash)
		// {
		// 	return error
		// }

		// check if file already exists
		return self::where('sha1', $this->hash)->first();
	}

	protected function saveFile($dir, $filename)
	{
		return $this->file->storeAs(
			'public/' . config('app.media_path') . '/' . $dir,
			$filename
		);
	}

	public function getSrcAttribute()
	{
		$attr = $this->attributes;

		return (
			'storage/' .
			config('app.media_path') . '/' .
			$attr['id'] . '/' .
			$attr['name']
		);
	}

	public function getCreatedByAttribute()
	{
		return $this->resolveUserById($this->attributes['created_by']);
	}

	public function getUpdatedByAttribute()
	{
		return $this->resolveUserById($this->attributes['updated_by']);
	}

	protected function resolveUserById($id)
	{
		return ($user = User::find($id)) ? $user->name : null;
	}

	public static function transformMetaData($meta)
	{
		$get = function($key, $default = null) use ($meta) {
			return array_get($meta, $key, $default);
		};

		$x = $get('video.resolution_x');
		$y = $get('video.resolution_y');
		$aspect_ratio = isset($x, $y) && $y > 0 ? $x / $y : null; // scared of zeros

		return [
			'size'         => $get('filesize'),
			'format'       => $get('fileformat'),
			'mime_type'    => $get('mime_type'),
			'width'        => $x,
			'height'       => $y,
			'aspect_ratio' => $aspect_ratio,
			'duration'     => $get('playtime_seconds')
		];
	}

}
