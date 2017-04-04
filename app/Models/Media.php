<?php

namespace App\Models;

use App\Models\Traits\Tracked;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Media extends Model
{
	use Tracked;

	protected
		$fillable = [
			'type',
			'title',
			'path',
			'file_name',
			'file_mime',
			'file_size',
			'sha1',
			'meta'
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
		$path = $this->saveFile();

		if(!$path) {
			return false;
		}

		$fullPath = storage_path('app/' . $path);

		$getID3 = new \getID3();
		$fileInfo = $getID3->analyze($fullPath);

		$this->fill([
			'path'      => $path,
			'file_name' => $this->file->getClientOriginalName(),
			'file_mime' => $this->file->getMimeType(),
			'file_size' => filesize($fullPath),
			'sha1'      => $this->hash,
			'meta'      => isset($fileInfo['video']) ? $fileInfo['video'] : null
		]);

		return parent::save();
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

	public function saveFile()
	{
		$filePath = $this->file->getPathname();

		$path = $this->getFolderPath($filePath, $this->hash);

		return $this->file->storeAs(
			'public/uploads/' . $path, $this->file->getClientOriginalName()
		);
	}

	/**
	 * Creates a string for deep folder structures of type "1/23/456/7891/".
	 * Hopefully this is better long term than storing it all in one folder.
	 *
	 * @param  string  $filePath  The temporary file path.
	 * @param  string  $hash  The sha1 or other hash of file.
	 *
	 * @return  string  The freshly figured out path.
	 */
	protected function getFolderPath($filePath, $hash)
	{
		// path starts life as a 10 char hash
		$path = sprintf('%u', crc32($hash));

		// loop and add slashes
		for($pos = 1, $i = 0; $i < 3; $i++) {
			$path = substr_replace($path, '/', $pos, 0);
			$pos += $i + 3;
		}

		return $path;
	}

	public function getMetaAttribute()
	{
		return !empty($this->attributes['meta']) ? json_decode($this->attributes['meta'], true) : null;
	}

	public function setMetaAttribute($json)
	{
		$this->attributes['meta'] = json_encode($json);
	}

	public function getCreatedByAttribute()
	{
		$user = !empty($this->attributes['created_by']) ?
			User::findOrFail($this->attributes['created_by']) : null;

		return isset($user) ? $user->name : null;
	}

}