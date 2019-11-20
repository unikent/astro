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
								{--search=}
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
		$linkToSearch = $this->option('search');
		$linkToSearch = ltrim($linkToSearch, 'http://');
		$linkToSearch = ltrim($linkToSearch, 'https://');
		$published = $this->option('published');

		// check we have a site
		if (!$site) {
			$this->error("Site not found. Please specify the --site-id of the site you would like to search.");
			return;
		}

		// check we have a link
		if (empty($linkToSearch)) {
			$this->error("You need to specify the link you would like to find using --search= option.");
			return;
		}

		// search within the contents of each page
		$version = $published ? Page::STATE_PUBLISHED : Page::STATE_DRAFT;
		$urlsCount = 0;
		foreach ($site->pages($version)->get() as $page) {
			foreach ($page->revision->blocks as $regionName => $sections) {
				foreach ($sections as $sectionName => $section) {
					foreach ($section['blocks'] as $block) {
						$blockDefinitionId = "{$block['definition_name']}-v{$block['definition_version']}";
						$definition = Block::fromDefinitionFile(Block::locateDefinition($blockDefinitionId));
						$blockUrls = $this->findURLsInPageContent($definition->fields, $block['fields'], $linkToSearch);
						if (!empty($blockUrls)) {
							$this->comment("Found matching URL" . (count($blockUrls) > 1 ? 's': ''). " in: {$page->full_path} - {$page->revision->title} \n region: {$regionName} \n section: {$section['name']} \n block: $blockDefinitionId");
							foreach ($blockUrls as $url) {
								$this->info(" - {$url}");
							}
							$urlsCount += count($blockUrls);
						}
					}
				}
			}
		}

		// search within the site's menu
		$menuVersion = $published ? 'menu_published' : 'menu_draft';
		foreach ($site->options[$menuVersion] as $menuItem) {
			if (strpos($menuItem['url'], $linkToSearch) !== false) {
				$this->comment("Found matching URL in menu item: {$menuItem['text']}");
				$this->info(" - {$menuItem['url']}");
				$urlsCount += 1;
			}
		}

		// final words
		if (empty($urlsCount)) {
			$this->comment("No URLs found matching '{$linkToSearch}'");
		} else {
			$this->comment("Found a total of {$urlsCount} URL" . ($urlsCount > 1 ? 's': ''));
		}
	}

	public function findURLsInPageContent($fields, $data, $search)
	{
		$urls = [];
		foreach ($fields as $field) {
			$value = !empty($data[$field['name']]) ? $data[$field['name']] : null;
			if ($value) {
				switch ($field['type']) {
					case 'group':
						$urls = array_merge($urls, $this->findURLsInPageContent($field['fields'], $value, $search));
						break;
					case 'collection':
						foreach ($value as $item) {
							$urls = array_merge($urls, $this->findURLsInPageContent($field['fields'], $item, $search));
						}
						break;
					default:
						if (is_array($value)) {
							$value = print_r($value, true);
						}
						if (preg_match_all('/(http|https):\/\/[^\s\'"]+/', $value, $matches)) {
							foreach ($matches[0] as $match) {
								if (strpos($match, $search) !== false) {
									$urls[] = $match;
								}
							}
						}
						break;
				}
			}
		}
		return $urls;
	}
}
