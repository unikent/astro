<template>
	<div>
		<slot name="header" v-if="(filteredFlattenedPages.length)"></slot>
		<template v-for="{page, matches} in sortedPages">
		<slot
				v-if="matches || displayUnmatched()"
				:page="page"
				:matches="page">

			<!-- default page item if no alternative content provided -->
			<el-row class="el-row__pageitem">
				<el-col :sm="12" :md="8" :style="{'padding-left': (page.depth) + 'rem'}">
					<router-link :to="{name: 'page', params: {site_id: page.site_id, page_id: page.id}}">{{ page.title }}</router-link>
				</el-col>
				<el-col :sm="12" :md="8"><small>{{ page.full_path }}</small></el-col>
				<el-col :sm="6" :md="6" :title="page.revision.updated_at">{{ page.revision.updated_at}}</el-col>
			</el-row>
			<!-- end default page item -->
		</slot>
		</template>
		<slot name="footer" v-if="(filteredFlattenedPages.length)"></slot>
		<slot name="no-matches" v-if="(!filteredFlattenedPages.length)">No pages match your search.</slot>
	</div>
</template>

<script>

/**
 * Displays a site's hierarchical page structure with optional filtering.
 */
export default {
	name: 'filterable-page-list',
	props: {
		/**
		 * a string to filter the pages by
		 */
		filter: {
			type: String,
			default: '',
		},
		/**
		 * hierarchical ordered array of pages
		 */
		pages: {
			type: Array,
			required: true,
		},
		/** array of strings matching the statuses to include,
		 * defaults to empty to include ALL.
	 	 * Current statuses are 'new' (never published), 'draft' (published but updated) and 'published' (published, not updated)
		 */
		statusFilter: {
			type: Array,
			default() { return [];},
		},
		/**
		 * Sort order for pages.
		 * - 'title' - the Page Title A-Z
		 * - 'url' - the Page path A-Z
		 * - 'updated-desc' - edited date (newest first)
		 * - 'updated-asc' - edited date (oldest first)
		 */
		sortOrder: {
			type: String,
			default: '',
		},
	},
	computed: {
		/**
		 * Apply sorting to the filtered pages. Options are:
		 */
		sortedPages() {
			// to avoid sorting filteredFlattenedPages itself
			let pages = [...this.filteredFlattenedPages];
			switch(this.sortOrder) {
				case 'title':
					pages.sort((p1,p2) => { return p1.page.title.toLowerCase() < p2.page.title.toLowerCase() ? -1 : (p1.page.title.toLowerCase() > p2.page.title.toLowerCase() ? 1 : 0)});
					break;
				case 'url':
					pages.sort((p1,p2) => { return p1.page.full_path < p2.page.full_path ? -1 : (p1.page.full_path > p2.page.full_path ? 1 : 0)});
					break;
				case 'updated-desc':
					pages.sort((p1,p2) => { return p1.page.revision.updated_at < p2.page.revision.updated_at ? 1 : (p1.page.revision.updated_at > p2.page.revision.updated_at ? -1 : 0)});
					break;
				case 'updated-asc':
					pages.sort((p1,p2) => { return p1.page.revision.updated_at < p2.page.revision.updated_at ? -1 : (p1.page.revision.updated_at > p2.page.revision.updated_at ? 1 : 0)});
					break;
			}
			return pages;
		},
		/**
		 * Turns the hierarchy of pages into a flattened array, of
		 * { page: PageObject, matched: Boolean } where page is the page,
		 * and matched indicates whether this page matched the filter criteria,
		 * or if one of its child pages matched.
		 */
		filteredFlattenedPages() {
			let pages = [];
			this.filterPages(this.pages, pages, this.lowercaseFilter, this.statusFilter);
			return pages;
		},
		/**
		 * Converts the supplied text filter to lower case to simplify matching
		 */
		lowercaseFilter() {
			return this.filter ? this.filter.toLowerCase() : '';
		},
	},
	methods: {
		/**
		 * Filters and flattens all the pages, depth-first.
		 * @param {Array} pages - Hierarchical array of pages, where each page's children are
		 *        stored in a "children" attribute.
		 * @param {Array} resultArray - Array of objects, which either matched the filters
		 *        or had children that did. Each object in the array is a { page, matched } pair,
		 *        where page is the page, and matched is a boolean indicating whether this page matched
		 *        the filters (true) or if it is included because its children matched (false).
		 * @param {String} filter - The text to filter on.
		 * @param {Array} statusFilter - Array of page statuses as strings to filter on, or empty
		 *        array to include pages of all statuses.
		 */
		filterPages(pages, resultArray, filter, statusFilter) {
			pages.forEach((page) => {
				// We include a page if ANY of its descendants match the search filter.
				// We will check the status of these descendants first (and add them to
				// the results array if they match).
				// Track the index where we will be inserting this page if it or any of its
				// descendants matches (descendants may be added before we add this page so
				// we can't just add it at the end).
				const nextPageIndex = resultArray.length;

				// check (and if they match add to resultsArray) any descendants of this page
				if(page.children) {
					this.filterPages(page.children, resultArray, filter, statusFilter);
				}

				// does this page itself match the criteria?
				const matches = this.shouldPageBeDisplayed(page, filter, statusFilter);

				// if either descendants matched (we have added them to the results array)
				// or this page matches, add it to the results array.
				if((nextPageIndex !== resultArray.length) || matches) {
					resultArray.splice(nextPageIndex,0,{matches, page});
				}
			});
		},
		/**
		 * Should the given page be displayed (does it match the filters) regardless of its descendants?
		 * @param {Object} page - Page object to test
		 * @param {String} filter - Keyword to search by (presumed to be lowercase)
		 * @param {Array} statusFilter - Array of pages statuses to filter by (or empty for all).
		 * @returns {Boolean} - True if this page matches the filter, otherwise false.
		 */
		shouldPageBeDisplayed(page, filter, statusFilter) {
			return this.pageMatchesFilter(page, filter) &&
				this.pageMatchesStatus(page, statusFilter);
		},
		/**
		 * Does a page match one of the given statuses
		 * @param {Object} page - The page to test
		 * @param {Array} statuses - Array of page statuses
		 * @returns {Boolean} - True if the page status is one of the statuses or the array is empty, otherwise false.
		 */
		pageMatchesStatus(page, statuses) {
			return (!statuses || statuses.length === 0 || statuses.includes(page.status));
		},
		/**
		 * Does the page match the keyword filter?
		 * @param {Object} page - The page object to filter
		 * @param {String} filter - Lowercase string to search the page's main fields for
		 * @returns {boolean} - True if the filter is matched or the filter is empty, otherwise false.
		 */
		pageMatchesFilter(page, filter) {
			if(!filter) {
				return true;
			}
			if(page.title && page.title.toLowerCase().indexOf(filter) !== -1) {
				return true;
			}
			return false;
		},
		displayUnmatched() {
			return ['','url'].indexOf(this.sortOrder) !== -1;
		},
	},
}
</script>