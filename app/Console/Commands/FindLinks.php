<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Models\Page;
use App\Models\User;
use App\Models\LocalAPIClient;
use Illuminate\Console\Command;
use App\Models\Definitions\Block;
use Illuminate\Validation\ValidationException;

class FindLinks extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'astro:findlinks
								{--site-id=}
								{--link=}
								{--published}
								';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Find links in the given site which match the given link.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$site = Site::find(intval($this->option('site-id')));
		$link = $this->option('link');
		$published = $this->option('published');

		// check we have a site
		if (!$site) {
			$this->error("Site not found. Please specify the --site-id of the site you would like to search.");
			return;
		}

		// check we have a link
		if (empty($link)) {
			$this->error("You need to specify the --link you would like to find.");
			return;
		}

		$version = $published ? Page::STATE_PUBLISHED : Page::STATE_DRAFT;
		$urls = [];
		foreach ($site->pages($version)->get() as $page) {
			foreach ($page->revision->blocks as $regionName => $sections) {
				foreach ($sections as $sectionName => $section) {
					foreach ($section['blocks'] as $block) {
						$definitionId = "{$block['definition_name']}-v{$block['definition_version']}";
						$this->info('checking ' . $page->full_path . " - {$regionName} - {$section['name']} - $definitionId");
						$definition = Block::fromDefinitionFile(Block::locateDefinition($definitionId));
						$urls += $this->checkFields($definition->fields, $block['fields']);
					}
				}
			}
		}

		dd($urls);
	}

	public function checkFields($fields, $data, $indent = 0)
	{
		$urls = [];
		foreach ($fields as $field) {
			$value = !empty($data[$field['name']]) ? $data[$field['name']] : null;
			if ($value) {
				switch ($field['type']) {
					case 'group':
						$urls += $this->checkFields($field['fields'], $value, $indent+1);
						break;
					case 'collection':
						foreach ($value as $item) {
							$urls += $this->checkFields($field['fields'], $item, $indent+1);
						}
						break;
					default:
					// echo $field['type'] . ': ';
					// var_dump($value);
						if (is_array($value)) {
							$value = print_r($value, true);
						}
						if (preg_match_all('/(http|https)\S+/', $value, $matches)) {
								var_dump($matches);
							foreach ($matches[0] as $match) {
								$urls[] = $match;
							}
						}
						break;
				}
			}
		}
		return $urls;
	}
}
