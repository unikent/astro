<?php

namespace App\Models\APICommands;

use DB;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\Contracts\APICommand;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Moving a Page always moves its subpages with it.
 * @package App\Models\APICommands
 */
class MovePage implements APICommand
{

    /**
     * Moving a Page
     * @param $input
     * @return mixed
     */
    public function execute($input, Authenticatable $user)
    {
        return DB::transaction(function() use($input,$user){
            $page = Page::find($input['page_id']);
            $parent = Page::find($input['parent_id']);
            $next = Page::find(!empty($input['next_id']) ? $input['next_id'] : null);
            if($parent->id != $page->parent_id){
                $this->createRedirects($page,$parent);
                $this->updatePaths($page,$parent);
            }
            if($next){
                $page->makePreviousSiblingOf($next);
            }else{
                $page->makeLastChildOf($parent);
            }
            $page->refresh();
            return $page;
        });
    }

    /**
     * Replace the beginning of an existing path with a different prefix.
     * @param string $path Current path
     * @param string $new_prefix New prefix
     * @param int $trim_count Number of characters to remove from the beginning of the current path.
     * @return string The new path.
     */
    public function replacePath($path, $new_prefix, $trim_count)
    {
        return $new_prefix . substr($path, $trim_count);
    }

    /**
     * Create Redirects for the moved page and all its descendants.
     * @param Page $page The Page that will be moved.
     * @param Page $parent The new parent Page (where the Page is moving to).
     */
    public function createRedirects($page, $parent)
    {
        $redirects = [];
        $remove_length = strlen($page->parent->path);
        foreach($page->getDescendantsAndSelf() as $item){
            $redirects[] = [
                'from' => $item->path,
                'to' => $this->replacePath($item->path, $parent->path , $remove_length)
            ];
        }
        Redirect::insert($redirects);
    }

    /**
     * Update the paths in the database for a Page and its descendants when it moves to a new parent.
     * @param Page $page The Page which is moving
     * @param Page $parent The Page which will be the new parent.
     */
    public function updatePaths($page,$parent)
    {
        $prefix_length = strlen($page->parent->path);
        $sql = "CONCAT(:path, SUBSTRING(path, $prefix_length))";
        Page::where('lft', '>=', $page->lft)
            ->where('lft', '<', $page->rgt)
            ->update(['path' => DB::raw($sql)], [$parent->path]);
    }

    public function messages(Collection $data, Authenticatable $user)
    {
        return [
            'parent_id.same_site' => 'The parent must be in the same site.',
            'parent_id.exists' => 'Parent not foundy',
            'parent_id.required' => 'Where my parent?',
            'parent_id.descendant_or_self' => 'huh?'
        ];
    }

    public function rules(Collection $data, Authenticatable $user)
    {
        return [
            'page_id' => [
                'required',
                'exists:pages,id'
            ],
            // parent must exist and be in the same site as this page
            'parent_id' => [
                'required',
                'exists:pages,id',
                'same_site:' . $data->get('page_id'),
                'not_descendant_or_self:'.$data->get('page_id')
             ],
            // if before_id exists it must have the parent_id specified for the new route / page.
            'next_id' => [
                'nullable',
                Rule::exists('pages','id')->where(function($query) use($data) {
                    $query->where('parent_id', $data->get('parent_id'));
                })
            ],

        ];
    }
}