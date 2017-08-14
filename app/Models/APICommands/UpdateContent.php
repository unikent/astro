<?php

namespace App\Models\APICommands;

use App\Models\Contracts\APICommand;
use App\Models\PageContent;
use App\Models\RevisionSet;
use App\Models\Revision;
use App\Models\Block;
use App\Validation\Brokers\BlockBroker;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

use App\Models\Definitions\Block as BlockDefinition;
use App\Models\Definitions\Region as RegionDefinition;
use App\Models\Page;

class UpdateContent implements APICommand
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

            // Update with new content.
            $this->processBlocks($page, $input['blocks']);

            // Save our previous state to the revisions table.
            $previous_revision = $page->revision;
            $revision = Revision::create([
                'revision_set_id' => $previous_revision->revision_set_id,
                'title' => $previous_revision->title,
                'layout_name' => $previous_revision->layout_name,
                'layout_version' => $previous_revision->layout_version,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'bake' => $page->bake()
            ]);
            $page->setRevision($revision);
            $page->fresh();
            return $page;
        });
        return $result;
    }

    /**
     * @param Page $page
     * @param $regions
     */
    protected function processBlocks($page, $regions) {
        foreach($regions as $region => $blocks) {
            // Remove any existing Blocks in the region (to avoid re-ordering existing)
            // TODO: explore updating block order rather than deleting each time
            $page->clearRegion($region);

            // Re/create all the blocks
            if(!empty($blocks)) {
                foreach($blocks as $delta => $data) {
                    $block = new Block;

                    $block->fill($data);

                    $block->page_content_id = $page->getKey();

                    $block->order = $delta;
                    $block->region_name = $region;

                    $block->save();

                    // associate media items with this block
                    if(isset($data['media']) && is_array($data['media'])) {
                        $media_block_ids = [];

                        foreach($data['media'] as $media) {
                            if(isset($media['id'], $media['associated_field'])) {
                                $media_block_ids[$media['id']] = [
                                    'block_associated_field' => $media['associated_field']
                                ];
                            }
                        }
                        $block->media()->sync($media_block_ids);
                    }
                }
            }
        }
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
            'blocks' => [
                'present',
                'array'
            ]
        ];
        // For each block instance...
        if($data->has('blocks') && is_array($data->get('blocks'))){
            foreach($data->get('blocks', []) as $region => $blocks){
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
}