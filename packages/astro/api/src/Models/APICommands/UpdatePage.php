<?php

namespace Astro\API\Models\APICommands;

use Astro\API\Models\Contracts\APICommand;
use Astro\API\Models\Revision;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Astro\API\Models\Page;

/**
 * Updates meta for a page including its title and any other options.
 * Does not update blocks or layout.
 * @package Astro\API\Models\APICommands
 */
class UpdatePage implements APICommand
{

	/**
	 * Carry out the command, based on the provided $input.
	 * If nothing has been changed, does nothing.
	 * @param array $input The input options as key=>value pairs.
	 * @return mixed
	 */
	public function execute($input, Authenticatable $user)
	{
		$result = DB::transaction(function () use ($input, $user) {
			$page = Page::find($input['id']);
			$previous_revision = $page->revision;
			$options = $previous_revision->options;
			$changed = false;
			if (isset($input['options']) && is_array($input['options'])) {
				foreach ($input['options'] as $name => $value) {
					if (null !== $value) {
						if (isset($options[$name])) {
							unset($options[$name]);
							$changed = true;
						}
					} else {
						if ($options[$name] != $value) {
							$options[$name] = $value;
							$changed = true;
						}
					}
				}
			}
			if ($changed || (!empty($input['title']) && $input['title'] != $previous_revision->title)) {
				$revision = Revision::create([
					'revision_set_id' => $previous_revision->revision_set_id,
					'title' => !empty($input['title']) ? $input['title'] : $previous_revision->title,
					'layout_name' => $previous_revision->layout_name,
					'layout_version' => $previous_revision->layout_version,
					'created_by' => $user->id,
					'updated_by' => $user->id,
					'options' => $options,
					'blocks' => $previous_revision->bake
				]);
				$page->setRevision($revision);
			}
			return $page;
		});
		return $result;
	}

	/**
	 * Get the error messages for this command.
	 * @param Collection $data The input data for this command.
	 * @return array Custom error messages mapping field_name => message
	 */
	public function messages(Collection $data, Authenticatable $user)
	{
		return [];
	}

	/**
	 * Get the validation rules for this command.
	 * @param Collection $data The input data for this command.
	 * @return array The validation rules for this command.
	 */
	public function rules(Collection $data, Authenticatable $user)
	{
		$rules = [
			'id' => [
				'exists:pages,id',
				'page_is_draft:' . $data->get('id')
			],
			'options' => [
				'array',
				'nullable'
			],
			'title' => [
				'string',
				'max:150',
				'nullable'
			]
		];
		return $rules;
	}
}