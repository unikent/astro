<?php
namespace App\Http\Requests\Api\v1\Page;

use Gate;
use App\Models\Page;
use Illuminate\Validation\Rule;
use App\Http\Requests\FormRequest;

class PersistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|max:255',
            'layout_name' => 'required|string|definition_exists:App\Models\Definitions\Layout,layout_version',
            'layout_version' => 'required|numeric',

            'route.slug' => [ 'required_with:route.parent_id', 'max:255' ],
            'route.parent_id' => [ 'required_with:route.slug' ],

            'site_id' => [],
            'site.name' => [ 'required_with:site.publishing_group_id', 'max:255' ],
            'site.publishing_group_id' => [ 'required_with:site.name' ],

            'is_published' => 'filled|boolean',

            'options' => 'array',
        ];

        $data = $this->validationData();
        $page = $this->route('page') ?: null;

        $rules['route.slug'][] = Rule::unique('routes', 'slug')->where(function($q) use ($data, $page) {
            if($page){
                $q->where('page_id', '!=', $page->getKey());        // Prevent the unique check from tripping over itself when updating
            }

            if(isset($data['route.parent_id'])){
                $q->where('parent_id', $data['route.parent_id']);   // The slug must be unique at this level in the tree; so scope the query accordingly
            }

            $q->where('is_canonical', 1);                           // If the slug is already in use at this level but is not canonical, we can re-purpose it
        });

        $rules['route.parent_id'][] = Rule::exists('routes', 'id');

        $rules['site_id'][] = Rule::exists('sites', 'id');

        $rules['site.publishing_group_id'][] = Rule::exists('publishing_groups', 'id');

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
        ];
    }
}
