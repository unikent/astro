<?php
namespace App\Http\Requests\Api\v1\Page;

use Gate;
use App\Models\Page;
use Illuminate\Validation\Rule;
use App\Http\Requests\FormRequest;
use App\Validation\Brokers\BlockBroker;
use App\Models\Definitions\Block as BlockDefinition;
use App\Models\Definitions\Region as RegionDefinition;

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
                // Prevent the unique check from tripping over itself when updating
                $q->where('page_id', '!=', $page->getKey());
            }

            if(isset($data['route']) && isset($data['route']['parent_id'])){
                // The slug must be unique at this level in the tree; so scope the query accordingly
                $q->where('parent_id', '=', $data['route']['parent_id']);
            }
        });

        $rules['route.parent_id'][] = Rule::exists('routes', 'id');

        $rules['site_id'][] = Rule::exists('sites', 'id');

        $rules['site.publishing_group_id'][] = Rule::exists('publishing_groups', 'id');

        // For each block instance...
        if($this->has('blocks')){
            foreach($this->get('blocks') as $region => $blocks){
                foreach($blocks as $delta => $block){
                    // ...load the Region definition...
                    $file = RegionDefinition::locateDefinition($region);
                    $regionDefinition = RegionDefinition::fromDefinitionFile($file);

                    // ...load the Block definition...
                    $version = isset($block['definition_version']) ? $block['definition_version'] : null;
                    $file = BlockDefinition::locateDefinition($block['definition_name'], $version);
                    $blockDefinition = BlockDefinition::fromDefinitionFile($file);

                    // ...load the validation rules from the definition...
                    $bb = new BlockBroker($blockDefinition);

                    // ...merge any region constraint validation rules...
                    foreach($bb->getRegionConstraintRules($regionDefinition) as $field => $ruleset){
                        $key = sprintf('blocks.%s.%d.%s', $region, $delta, $field);
                        $rules[$key] = $ruleset;
                    }

                    // ...and then merge the block field validation rules.
                    foreach($bb->getRules() as $field => $ruleset){
                        $key = sprintf('blocks.%s.%d.fields.%s', $region, $delta, $field);
                        $rules[$key] = $ruleset;
                    }

                }
            }
        }

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
