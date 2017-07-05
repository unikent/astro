<template>
<div>
	<ul v-if="pages" v-for="page in pages" class="page-list">
		<PageListItem class="item" :site="site" :page="page" :editing="edit"></PageListItem>
	</ul>
</div>
</template>

<script>
import PageListItem from './PageListItem';
import { Loading } from 'element-ui';
import { mapState, mapActions } from 'vuex';


export default {

	data() {
		return {
			site: 1,
			edit: null,
			loading: true
		};
	},

	components: {
		PageListItem
	},

	computed: {
		...mapState('site',{
			pages: state => state.pages
		})
	},

	methods: {
		...mapActions({
			fetchSite: 'site/fetchSite'
		})
	},

	created() {
		this.fetchSite();
		this.$bus.$on('rename-page', (id) => this.edit = id);
	}
};
</script>