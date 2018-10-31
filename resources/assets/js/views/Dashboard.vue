<template>
<el-card v-if="siteData && siteData.id && homepageID">

	<div class="el-card__body">
		<div class="el-row">
			<h1><strong>{{ siteData.name }}</strong></h1>
		</div>
		<div class="el-row">
			<router-link :to="`/site/${siteData.id}/page/${homepageID}`"><button class="el-button el-button--primary">Edit Pages</button></router-link>
		</div>
	</div>
</el-card>
</template>

<script>
import { mapState } from 'vuex';

export default {
	computed: {
		...mapState({
			siteData: state => state.site.siteData,
			homepageID: state => state.site.pages && state.site.pages.length > 0 ? state.site.pages[0].id : '',
		}),
	},
};
</script>