<?php

namespace App\Models\APICommands;

use App\Models\Contracts\APICommand;
use App\Models\Site;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

class GetSiteTree implements APICommand
{

    /**
     * Carry out the command, based on the provided $input.
     * @param array $input The input options as key=>value pairs.
     * @return mixed
     */
    public function execute($input, Authenticatable $user)
    {

    }

    /**
     * Get the error messages for this command.
     * @param Collection $data The input data for this command.
     * @return array Custom error messages mapping field_name => message
     */
    public function messages(Collection $data, Authenticatable $user)
    {
        return [
            'site_id' => 'Site not found.'
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
            'site_id' => [
                'exists:sites,id'
            ]
        ];
    }
}