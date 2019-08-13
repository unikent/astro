<template>
<el-card v-if="siteData && siteData.id && homepageID">

	<div class="el-card__body">
		<div class="el-row">
			<h1><strong>{{ siteData.name }}</strong></h1>
		</div>
		<div class="el-row">
			<router-link :to="`/site/${siteData.id}/page/${homepageID}`"><button class="el-button el-button--primary">Edit Pages</button></router-link>
		</div>
		<div class="el-row">
			<el-input v-model="filter" placeholder="Filter by keyword"></el-input>
			<el-select
					v-model="statusFilter"
					placeholder="Filter by status">
				<el-option
						v-for="item in statusOptions"
						:key="item.value"
						:label="item.label"
						:value="item.value">
				</el-option>
			</el-select>
		</div>
		<div class="el-row">
			<ul>
				<li is="page-item"
					v-for="page in pages"
					:page="page"
					:key="page.id"
					:filter="filter"
					:statusFilter="statusFilter"
				>
				</li>
			</ul>
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
		lowerFilter() {
			return this.filter ? this.filter.toLowerCase() : '';
		},
	},
};
</script>