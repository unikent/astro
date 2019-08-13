<template>
	<div>
	<filterable-page-list-item
			v-for="page in filteredFlattenedPages"
			:page="page.page"
			:key="page.page.id"
			:class="{'el-row__dimmed': !page.matches}"
	></filterable-page-list-item>
	</div>
</template>

<script>
import FilterablePageListItem from './FilterablePageListItem';

export default {
	name: 'filterable-page-list',
	components: {
		FilterablePageListItem,
	},
	props: {
		// a string to filter the pages by
		filter: {
			type: String,
			default: '',
		},
		// hierarchical ordered array of pages
		pages: {
			type: Array,
			required: true,
		},
		// array of strings matching the statuses to include,
		// defaults to empty to include ALL
		statusFilter: {
			type: Array,
			default() { return [];},
		},
	},
	computed: {
		/**
		 * Turns the hierarchy of pages into a flattened array, of
		 * { page: PageObject, matched: Boolean } where page is the page,
		 * and matched indicates whether this page matched the filter criteria,
		 * or if one of its child pages matched.
		 */
		filteredFlattenedPages() {
			let pages = [];
			this.filterPages(this.pages, pages, this.lowercaseFilter);
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
		}
	},
}
</script>