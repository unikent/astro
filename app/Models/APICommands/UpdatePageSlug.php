<?php

namespace App\Models\APICommands;

use App\Models\Contracts\APICommand;
use App\Models\Revision;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use App\Models\Page;

/**
 * Updates the slug for a page.
 * Updates the path for the page and all of its descendants.
 * @package App\Models\APICommands
 */
class UpdatePageSlug implements APICommand
{

    /**
     * Carry out the command, based on the provided $input.
     * @param array $input The input options as key=>value pairs.
     * @return mixed
     */
    public function execute($input, Authenticatable $user)
    {
        $result = DB::transaction(function() use($input,$user){
            $page = Page::find($input['id']);


            $page->fresh();
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
                'exists:pages,id'
            ],
            'slug' => [
                'string'
            ]
        ];
        return $rules;
    }
}