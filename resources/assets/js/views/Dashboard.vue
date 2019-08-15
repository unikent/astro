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
			<el-col :xs="24" :md="8">
				<el-select v-model="statusFilter" multiple placeholder="Filter by status">
					<el-option
						v-for="option in statusOptions"
						:value="option.value"
						:label="option.label"
						:key="option.value"></el-option>
				</el-select>
			</el-col>
			<el-col :xs="24" :md="8">
				<el-select v-model="sortOrder" placeholder="Sort">
					<el-option
							v-for="option in sortOptions"
							:value="option.value"
							:label="option.label"
							:key="option.value"></el-option>
				</el-select>
			</el-col>
		</div>
		<filterable-page-list
				class="el-row"
				style="margin-top: 1rem;"
				:pages="pages"
				:filter="filter"
				:status-filter="statusFilter"
				:sort-order="sortOrder"
		>
			<el-row class="el-row__pageitem" slot-scope="{ page, matches }" :key="page.id">
				<el-col :sm="12" :md="8" :style="{'padding-left': (page.depth) + 'rem'}">
					<router-link :to="{name: 'page', params: {site_id: page.site_id, page_id: page.id}}">{{ page.title }}</router-link>
					<br><small>{{ page.full_path }}</small>
				</el-col>
				<el-col :sm="6" :md="4" :title="page.revision.updated_at">
					<small>{{ updatedDate(page.revision.updated_at)}}</small>
				</el-col>
				<el-col :sm="8" :md="4" :title="page.revision.updated_at" v-if="page.published_at">
					<small>{{ publishedDate(page.published_at) }}</small>
				</el-col>
			</el-row>
		</filterable-page-list>
	</div>
</el-card>
</template>

<script>
import { mapState } from 'vuex';
import FilterablePageList from '../components/FilterablePageList';
import ElSelectDropdown from "../../../../node_modules/element-ui/packages/select/src/select-dropdown.vue";
import {prettyDate} from '../classes/helpers.js';

export default {
	components: {
		ElSelectDropdown,
		FilterablePageList
	},
	data() {
		return {
			filter: '',
			sortOrder: '',
			sortOptions: [
				{
					label: 'Default',
					value: '',
				},
				{
					label: 'Edited (new -> old)',
					value: 'updated-desc',
				},
				{
					label: 'Edited (old -> new)',
					value: 'updated-asc',
				},
				{
					label: 'Page Title',
					value: 'title',
				},
				{
					label: 'URL',
					value: 'url',
				},
			],
			statusFilter: [],
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
	methods: {
		updatedDate(date) {
			return prettyDate(date);
		},
		publishedDate(date) {
			return prettyDate(date);
		},
	}
};
</script>