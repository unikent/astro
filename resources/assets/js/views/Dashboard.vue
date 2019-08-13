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
			<el-input v-model="filter" placeholder="Filter by page title"></el-input>
		</div>
		<div class="el-row">
			<ul>
				<li is="page-item"
					v-for="page in pages"
					:page="page"
					:key="page.id"
					:filter="filter"
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

export default {
	components: {PageItem},
	data() {
		return {
			filter: '',
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