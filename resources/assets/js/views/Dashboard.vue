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
		<filterable-page-list
				class="el-row"
				style="margin-top: 1rem;"
				:pages="pages"
				:filter="filter">
		</filterable-page-list>
	</div>
</el-card>
</template>

<script>
import { mapState } from 'vuex';
import FilterablePageList from '../components/FilterablePageList';
import ElSelectDropdown from "../../../../node_modules/element-ui/packages/select/src/select-dropdown.vue";

export default {
	components: {
		ElSelectDropdown,
		FilterablePageList
	},
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
	},
};
</script>