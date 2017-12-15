<?php

namespace Astro\API\Models\APICommands;

use Astro\API\Models\Contracts\APICommand;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Astro\API\Models\Site;
use Illuminate\Validation\Rule;

/**
 * Updates meta for a site including its name, domain name, path and any other options.
 * @package Astro\API\Models\APICommands
 */
class UpdateSite implements APICommand
{
    /**
     * Names of the primitive data types which map to single fields in the site table which
     * may be included as part of the update.
     */
    const UPDATABLE_PRIMITIVE_FIELDS = ['name', 'path', 'host',];

    /**
     * Take a set of options (key, values) and a set of changes to those options
     * if the key exists then update the value
     * if the key exists and the value is set to null then remove the option
     * if the key does not exist then add a new key/value
     *
     * does not update nested key value pairs
     * 
     * returns an updated set of options 
     *
     * @param array $currentOptions - existing set of options for a site
     * @param array $newOptions - updates (removals, changes, additions) to the set of options
     * @return array updated options
     */
    public function updateOptions($currentOptions, $newOptions)
    {
        foreach ($newOptions as $name => $value) {
            if ($value === null) {
                unset($currentOptions[$name]);
            } else {
                $currentOptions[$name] = $value;
            }
        }
        return $currentOptions;
    }

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
                $site->options = $this->updateOptions($site->options, $input['options']);
                $changed = true;
            }
            foreach (self::UPDATABLE_PRIMITIVE_FIELDS as $field) {
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