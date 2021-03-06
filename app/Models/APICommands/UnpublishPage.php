<?php

namespace App\Models\APICommands;

use App\Events\PageEvent;
use App\Exceptions\UnpublishedPageException;
use App\Models\Contracts\APICommand;
use App\Models\Page;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

/**
 * Publish a page and (optionally) all its descendants.
 * @package App\Models\APICommands
 */
class UnpublishPage implements APICommand
{

	/**
	 * Carry out the command, based on the provided $input.
	 * @param array $input The input options as key=>value pairs.
	 * @return mixed
	 */
	public function execute($input, Authenticatable $user)
	{
		return DB::transaction(function () use ($input) {
			$page = Page::find($input['id']);
			event(new PageEvent(PageEvent::UNPUBLISHING, $page, null));
			$page->publishedVersion()->delete();
			event(new PageEvent(PageEvent::UNPUBLISHED, $page, null));
			return $page;
		});
	}

	/**
	 * Get the error messages for this command.
	 * @param Collection $data The input data for this command.
	 * @return array Custom error messages mapping field_name => message
	 */
	public function messages(Collection $data, Authenticatable $user)
	{
		return [
			'id.exists' => 'The page does not exist.',
			'id.page_is_draft' => 'You can only unpublish draft pages.',
			'id.page_is_published' => 'The page is already unpublished.'
		];
	}

	/**
	 * Get the validation rules for this command.
	 * @param Collection $data The input data for this command.
	 * @return array The validation rules for this command.
	 */
	public function rules(Collection $data, Authenticatable $user)
	{
		return [
			'id' => [
				'exists:pages,id',
				'page_is_draft:' . $data->get('id'),
				'page_is_published:' . $data->get('id')
			],
		];
	}
}