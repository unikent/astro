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
			<el-col :xs="24" :md="16" style="padding-left: 1rem;">
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
				:sort-order="sortOrder"
		>
			<el-row class="el-row__pageitem" slot-scope="{ page, matches }" :key="page.id">
				<el-col :style="{'padding-left': (pageIndent(page)) + 'rem'}">
					<router-link :to="{name: 'page', params: {site_id: page.site_id, page_id: page.id}}">{{ page.title }}</router-link>
					<br>
					<small>
						Edited: {{ dateDifference(page.revision.updated_at)}}
						on {{ formattedDate(page.revision.updated_at)}}
					</small>
					<small>
						-
						<a target="_blank"
							:title="page.full_path"
							:href="pageDraftPreviewURL(page)">Preview</a>
					</small>
					<small v-if="(page.status !== 'new')">
						, <a target="_blank"
							:title="pagePublishedURL(page)"
							:href="pagePublishedURL(page)">Live Version</a>
					</small>
				</el-col>
			</el-row>
		</filterable-page-list>
	</div>
</el-card>
</template>

<script>
import { mapState } from 'vuex';
import FilterablePageList from '../components/FilterablePageList';
import {prettyDate, getDraftPreviewURL, getPublishedPreviewURL} from '../classes/helpers.js';

export default {
	components: {
		FilterablePageList
	},
	data() {
		return {
			filter: '',
			sortOrder: '',
			sortOptions: [
				{
					label: 'Sort by default',
					value: '',
				},
				{
					label: 'Sort by edited date (recent first)',
					value: 'updated-desc',
				},
				{
					label: 'Sort by edited date (oldest first)',
					value: 'updated-asc',
				},
				{
					label: 'Sort by page title',
					value: 'title',
				},
				{
					label: 'Sort by URL',
					value: 'url',
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
		/**
		 * The indent level for the page item in the list
		 * @param {Object} page - The page item
		 * @return {integer} - The depth of the page if sorting by hierarchy, otherwise 0
		 */
		pageIndent(page) {
			return ['','url'].indexOf(this.sortOrder) !== -1 ? page.depth : 0;
		},
		/**
		 * Returns a human readable formatted version of the yyyy-mm-dd hh:ii:ss date
		 * @param date - yyyy-mm-dd hh:ii:ss date
		 * @returns {string} Nicely formatted date
		 */
		formattedDate(date) {
			const dt = new Date(date);
			return dt.toLocaleString("en-GB", {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric'});
		},
		/**
		 * Returns a human readable description of the elapsed time between now and the provided date.
		 * @param date - yyyy-mm-dd hh:ii:ss date assumed to be UCT
		 * @returns {string} Nicely formatted date
		 */
		dateDifference(date) {
			return prettyDate(date, false, 0);
		},
		pageDraftPreviewURL(page) {
			return getDraftPreviewURL(this.siteData.host, page.full_path);
		},
		pagePublishedURL(page) {
			return getPublishedPreviewURL(this.siteData.host, page.full_path);
		},
	}
};
</script>
