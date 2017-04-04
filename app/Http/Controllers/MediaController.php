<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Api\ApiController;
use App\Models\Media;

class MediaController extends ApiController
{

	const UPLOAD_RULES = [
		'shared' => 'required|file',
		'image'  => 'image|dimensions:min_width=400,min_height=400',
		'upload' => 'mimes:' .
			// images
			'jpg,jpeg,png,gif,bmp,svg,' .
			// documents
			'pdf,doc,docx,key,ppt,pptx,pps,ppsx,odt,xls,xlsx,zip,' .
			// audio
			'mp3,m4a,ogg,wav,mp4,' .
			// video
			'm4v,mov,wmv,avi,mpg,ogv,3gp,3g2'
	];

	public function index()
	{
		return Media::all();
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		return Media::find($id);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update($media_id, Request $request)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$media = Media::find($id);
		$media->delete();
	}

	/**
	 * Upload a file and add it to our media DB.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$this->validateUpload($request);

		$file = $request->file('upload');

		$mediaItem = new Media();
		$mediaItem->setFile($file);

		if($item = $mediaItem->exists())
		{
			return [
				'data' => $item->toArray()
			];
		}

		if($mediaItem->save()) {
			// add to DB and return result
			return [
				'data' => $mediaItem->toArray()
			];
		}

		return $this->errorInternal('Unable to upload file');
	}

	/**
	 * Validate upload(s) based on rules defined in definition files.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return void
	 */
	protected function validateUpload(Request $request) {
		$rules = [];

		if($request->file('image'))
		{
			$rules['image'] = $this->mergeValidationRules([self::UPLOAD_RULES['image']]);
		}
		else
		{
			$upload = $request->file('upload');
			$count = count($upload);

			if(count($upload) > 1)
			{
				foreach(range(0, $count - 1) as $index) {
					$rules['upload.' . $index] =
						$this->mergeValidationRules([self::UPLOAD_RULES['upload']]);
				}
			}
			else
			{
				$rules['upload'] =
					$this->mergeValidationRules([self::UPLOAD_RULES['upload']]);
			}
		}
		// max:5120

		$this->validate($request, $rules);
	}

	/**
	 * Adds validation rules together, merging shared rule with those supplied
	 * as arguments.
	 *
	 * @param  array  One or more rules to be added.
	 * @return  string  A new rule containing all passed in.
	 */
	protected function mergeValidationRules($rules)
	{
		return implode(
			'|',
			array_merge([self::UPLOAD_RULES['shared']], $rules)
		);
	}
}
