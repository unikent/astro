<?php

namespace App\Models\APICommands;

use App\Models\Contracts\APICommand;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use App\Models\Site;
use Illuminate\Validation\Rule;

/**
 * Updates meta for a site including its name, domain name, path, publishing group and any other options.
 * @package App\Models\APICommands
 */
class UpdateSite implements APICommand
{
	/**
	 * Names of the primitive data types which map to single fields in the site table which
	 * may be included as part of the update.
	 */
	const UPDATABLE_PRIMITIVE_FIELDS = ['name', 'path', 'host', 'publishing_group_id'];

	/**
	 * Carry out the command, based on the provided $input.
	 * If nothing has been changed, does nothing.
	 * @param array $input The input options as key=>value pairs.
	 * @return mixed
	 */
	public function execute($input, Authenticatable $user)
	{
		$result = DB::transaction(function () use ($input, $user) {
			$site = Site::find($input['id']);
			$changed = false;
			if (isset($input['options']) && is_array($input['options'])) {
				$options = $site->options;
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
				$site->options = $options;
			}
			foreach( self::UPDATABLE_PRIMITIVE_FIELDS as $field) {
				if(!empty($input[$field])){
					$site->$field = $input[$field];
					$changed = true;
				}
			}
			if ($changed ) {
				$site->save();
			}
			return $site;
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
		return [
			'id.exists' => 'The site specified does not exist',
			'id.required' => 'You cannot update a site without a site!',
			'options.required_without' => 'Update site API request must include at least one field to update.'
		];
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
				'exists:sites,id',
				'required'
			],
			'name' => [
				'nullable',
				'max:190',
				'string'
			],
			'publishing_group_id' => [
				Rule::exists('publishing_groups', 'id'),
				'nullable'
			],
			'host' => [
				'nullable',
				'max:100',
				'regex:/^[a-z0-9.-]+(:[0-9]+)?$/',
				'unique:sites,host,null,id,path,' . $data->get('path')
			],
			'path' =>[
				'nullable',
				'regex:/^(\/[a-z0-9_-]+)*$/i',
				'unique:sites,path,null,id,host,' . $data->get('host'),
				'unique_site_path:' . $data->get('host')
			],
			'options' => [
				'array',
				'nullable',
				'required_without_all:' . join(",",self::UPDATABLE_PRIMITIVE_FIELDS)
			],
		];
		return $rules;
	}
}