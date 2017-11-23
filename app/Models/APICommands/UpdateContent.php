<?php

namespace App\Models\APICommands;

use App\Models\Contracts\APICommand;
use App\Models\Revision;
use App\Models\Block;
use App\Validation\Brokers\RegionBroker;
use App\Validation\Brokers\BlockBroker;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

use App\Models\Definitions\Block as BlockDefinition;
use App\Models\Definitions\Region as RegionDefinition;
use App\Models\Page;
use Illuminate\Support\Facades\Validator;

class UpdateContent implements APICommand
{

	/**
	 * Carry out the command, based on the provided $input.
	 * @param array $input The input options as key=>value pairs.
	 * @return mixed
	 */
	public function execute($input, Authenticatable $user)
	{
		$result = DB::transaction(function () use ($input, $user) {
			$page = Page::find($input['id']);

			// Update with new content.
			$errors = $this->processBlocks($page, $input['blocks']);

			// Save our previous state to the revisions table.
			$previous_revision = $page->revision;
			$revision = Revision::create([
				'revision_set_id' => $previous_revision->revision_set_id,
				'title' => $previous_revision->title,
				'layout_name' => $previous_revision->layout_name,
				'layout_version' => $previous_revision->layout_version,
				'created_by' => $user->id,
				'updated_by' => $user->id,
				'blocks' => $page->bake(),
				'options' => '',
				'valid' => !$errors
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
	protected function processBlocks($page, $regions)
	{
		$errors = false;
		foreach ($regions as $region => $sections) {
			$page->clearRegion($region);

			foreach ($sections as $section) {
				// Remove any existing Blocks in the region (to avoid re-ordering existing)
				// TODO: explore updating block order rather than deleting each time
				// Re/create all the blocks

				if (!empty($section['blocks'])) {
					foreach ($section['blocks'] as $delta => $data) {
						$block = new Block;

						$block->fill($data);

						$block->page_id = $page->getKey();

						$block->order = $delta;
						$block->region_name = $region;
						$block->section_name = $section['name'];

						$block->errors = $this->validateBlock($block);
						$errors = $errors || !empty($block->errors);
						$block->save();

						// associate media items with this block
						if (isset($data['media']) && is_array($data['media'])) {
							$media_block_ids = [];

							foreach ($data['media'] as $media) {
								if (isset($media['id'], $media['associated_field'])) {
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
		return $errors;
	}

	public function validateBlock($block)
	{
		$rules = [];
		// ...load the Block definition...
		$version = isset($block['definition_version']) ? $block['definition_version'] : null;
		$file = BlockDefinition::locateDefinition($block['definition_name'], $version);
		$blockDefinition = BlockDefinition::fromDefinitionFile($file);

		// ...load the validation rules from the definition...
		$bb = new BlockBroker($blockDefinition);

		// ...and then merge the block field validation rules.
		foreach ($bb->getRules() as $field => $ruleset) {
			$rules[$field] = $ruleset;
		}
		$validator = Validator::make($block->fields, $rules);
		if ($validator->fails()) {
			$errors = $validator->errors();
			return $errors;
		}
		return null;
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
				'exists:pages,id',
				'page_is_draft:' . $data->get('id')
			],
			'blocks' => [
				'present',
				'array'
			]
		];
		// For each block instance...
		if ($data->has('blocks') && is_array($data->get('blocks'))) {
			foreach ($data->get('blocks', []) as $region => $sections) {
				// ...load the Region definition...
				$file = RegionDefinition::locateDefinition($region);
				$regionDefinition = RegionDefinition::fromDefinitionFile($file);

				$rules[sprintf('blocks.%s', $region)] = ['size:' . count($regionDefinition->sections)];

				foreach ($sections as $section_delta => $section) {
					// ...load the validation rules from the definition...

					//test that this is a valid section in the region definition
					if (isset($regionDefinition->sections[$section_delta])) {
						$rules[sprintf('blocks.%s.%d.name', $region, $section_delta)] = [
							'in:' . $regionDefinition->sections[$section_delta]['name']
						];
					}


					$rb = new RegionBroker($regionDefinition);
					$sectionConstraintRules = $rb->getSectionConstraintRules($section['name']);
					if (!empty($sectionConstraintRules['blockLimits']['blocks'])) {

						$sectionBlocksRules = !empty($sectionConstraintRules['blocksRequired']) ? $sectionConstraintRules['blocksRequired']['blocks'] : [];

						if (!empty($section['blocks'])) {
							$sectionBlocksRules = array_merge($sectionConstraintRules['blockLimits']['blocks'], $sectionBlocksRules);
						}
						
						$rules[sprintf('blocks.%s.%d.blocks', $region, $section_delta)] = $sectionBlocksRules;
					}
					

					foreach ($section['blocks'] as $block_delta => $block) {
						// ...merge any region constraint validation rules...
						$allowedBlocksRules = $sectionConstraintRules['allowedBlocks'];

						foreach ($allowedBlocksRules as $field => $ruleset) {
							$key = sprintf('blocks.%s.%d.blocks.%d.%s', $region, $section_delta, $block_delta, $field);
							$rules[$key] = $ruleset;
						}					
					}
				}
			}
		}
		
		return $rules;
	}
}
