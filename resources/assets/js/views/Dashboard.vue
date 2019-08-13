<template>
<el-card v-if="siteData && siteData.id && homepageID">

	<div class="el-card__body">
		<div class="el-row">
			<h1><strong>{{ siteData.name }}</strong></h1>
		</div>
		<div class="el-row">
			<el-col :xs="24" :md="8">
				<el-input v-model="filter" placeholder="Filter by keyword"></el-input>
			</el-col>
		</div>
		<div class="el-row" style="margin-top: 1rem;">
				<page-item
					v-for="page in filteredFlattenedPages"
					:page="page.page"
					:key="page.page.id"
					:class="{'el-row__dimmed': !page.matches}"
				></page-item>
		</div>
	</div>
</el-card>
</template>

<script>
import { mapState } from 'vuex';
import PageItem from '../components/PageItem';
import ElSelectDropdown from "../../../../node_modules/element-ui/packages/select/src/select-dropdown.vue";

export default {
	components: {
		ElSelectDropdown,
		PageItem},
	data() {
		return {
			filter: '',
			statusFilter: '',
			statusOptions: [
				{
					label: 'All Statuses',
					value: '',
				},
				{
					label: 'New',
					value: 'new',
				},
				{
					label: 'Updated',
					value: 'draft',
				},
				{
					label: 'Published',
					value: 'published',
				},
			],
		}
	},
	computed: {
		...mapState({
			siteData: state => state.site.siteData,
			pages: state => (state.site.pages && state.site.pages.length > 0) ? state.site.pages : [],
			homepageID: state => state.site.pages && state.site.pages.length > 0 ? state.site.pages[0].id : '',
		}),
		filteredFlattenedPages() {
			let pages = [];
			this.filterPages(this.pages, pages, this.lowercaseFilter);
			return pages;
		},
		lowercaseFilter() {
			return this.filter ? this.filter.toLowerCase() : '';
		},
	},
	methods: {
		filterPages(pages, resultArray, filter) {
			pages.forEach((page) => {
				// so we know where to insert this page in case we add its descendants first...
				const nextPageIndex = resultArray.length;

				if(page.children) {
					this.filterPages(page.children, resultArray, filter);
				}
				const matches = this.shouldPageBeDisplayed(page, filter);
				if((nextPageIndex !== resultArray.length) || matches) {
					resultArray.splice(nextPageIndex,0,{matches, page});
				}
			});
		},
		shouldPageBeDisplayed(page, filter) {
			return this.pageMatchesFilter(page, filter) &&
					this.pageMatchesStatus(page, this.statusFilter);
		},
		pageMatchesStatus(page, status) {
			return (!status || this.statusFilter === page.status);
		},
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
};
</script>