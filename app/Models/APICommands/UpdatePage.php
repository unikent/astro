<?php

namespace App\Models\APICommands;

use App\Models\Contracts\APICommand;
use App\Models\Revision;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use App\Models\Page;

/**
 * Updates meta for a page including its title and any other options.
 * Does not update blocks or layout.
 * @package App\Models\APICommands
 */
class UpdatePage implements APICommand
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

            $previous_revision = $page->revision;
            $options = $previous_revision->options;
            if(isset($input['options']) && is_array($input['options'])){
                foreach($input['options'] as $name => $value){
                    if(null !== $value){
                        unset($options[$name]);
                    }else{
                        $options[$name] = $value;
                    }
                }
            }

            $revision = Revision::create([
                'revision_set_id' => $previous_revision->revision_set_id,
                'title' => !empty($input['title']) ? $input['title'] : $previous_revision->title,
                'layout_name' => $previous_revision->layout_name,
                'layout_version' => $previous_revision->layout_version,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'options' => $options,
                'blocks' => $previous_revision->bake,
				'valid' => $previous_revision->valid
            ]);
            $page->setRevision($revision);
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
            'options' => [
                'array'
            ],
            'title' => [
                'string'
            ]
        ];
        return $rules;
    }
}