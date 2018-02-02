<?php

namespace App\Models\APICommands;

use App\Models\Contracts\APICommand;
use App\Models\Page;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use App\Models\DeletedPage;
use Illuminate\Validation\Rule;

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
            DeletedPage::insert($this->createDeletedPages($page->getDescendantsAndSelf()));
            // if we have a published version, delete that too
			$published_version = $page->publishedVersion();
			if($published_version){
				$published_version->delete();
			}
			// baum deletes all descendants when deleting a page.
            $page->delete();
            return true;
        });
    }

	/**
	 * Get an array of attributes to create DeletedPage records from for this page and one for each descendant.
	 *
	 * @param Collection $pages - All the Pages to save as DeletedPages
	 *
	 * @return array - Array of DeletedPage properties to bulk insert.
	 *
	 * @todo - should we only do this for pages which have been published at least once or for all pages?
	 */
    public function createDeletedPages($pages)
	{
		$deletes = [];
		foreach( $pages as $item){
			$deletes[] = [
				'revision_id' => $item->revision->id,
				'path' => $item->path
			];
		}
		return $deletes;
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
              'required',
              Rule::exists('pages')->where(function($query) use($data) {
                  $query->where('id', $data->get('id'))
                        ->where('version', Page::STATE_DRAFT);
              })
          ]
        ];
    }
}