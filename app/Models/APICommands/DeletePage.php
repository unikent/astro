<?php

namespace App\Models\APICommands;

use App\Models\Contracts\APICommand;
use App\Models\Page;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use App\Models\DeletedPage;

/**
 * Delete a page and all its descendants, recording this as DeletedPages.
 * @package App\Models\APICommands
 */
class DeletePage implements APICommand
{

    /**
     * Carry out the command, based on the provided $input.
     * @param array $input The input options as key=>value pairs.
     * @return mixed
     */
    public function execute($input, Authenticatable $user)
    {
        return DB::transaction(function() use($input) {
            $id = $input['id'];
            $page = Page::find($id);
            $deletes = [];
            foreach( $page->getDescendantsAndSelf() as $item){
                $deletes[] = [
                    'revision_id' => $item->revision->id,
                    'path' => $item->path
                ];
            }
            DeletedPage::insert($deletes);
            $page->delete();
            return true;
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
            'id' => 'The page does not exist.'
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
              'exists:pages,id'
          ]
        ];
    }
}