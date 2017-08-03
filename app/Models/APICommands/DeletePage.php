<?php

namespace App\Models\APICommands;

use App\Models\Contracts\APICommand;
use App\Models\Page;
use App\Models\PageContent;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

/**
 * Delete a page means:
 * - Find the current draft
 * - Set it to null
 * - Set all its children to null.
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
            $id = $input['page_id'];
            $pagecontent = PageContent::find($id);
            $revision = $pagecontent->draft;
            if($revision) {
                $page = $revision->draftPage;
                if($page){
                    $this->markPagesDeleted([$page]);
                    $page->removeEmptyPages();
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Recursively mark all drafts of a Page as deleted.
     * @param Collection $page Collection of Pages
     */
    public function markPagesDeleted($pages)
    {
        foreach($pages as $page){
            $page->setDraft(null);
            $this->markPagesDeleted($page->children);
        }
    }

    /**
     * Get the error messages for this command.
     * @param Collection $data The input data for this command.
     * @return array Custom error messages mapping field_name => message
     */
    public function messages(Collection $data, Authenticatable $user)
    {
        return [
            'page_id' => 'The page does not exist.'
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
          'page_id' => [
              'exists:page_content,id'
          ]
        ];
    }
}