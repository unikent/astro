<template>
<div>
	<back-bar :title="title" />
	<ul v-if="pages" v-for="page in pages" class="page-list">
		<PageListItem class="item" :site="site" :page="page" :editing="edit"></PageListItem>
	</ul>
</div>
</template>

<script>
import PageListItem from './PageListItem';
import BackBar from './BackBar';
import { mapState, mapActions } from 'vuex';


export default {

	props: ['title'],

	components: {
		PageListItem,
		BackBar
	},

	data() {
		return {
			site: 1,
			edit: null,
			loading: true
		};
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