<template>
<div>
	<back-bar :title="title" />

	<div v-if="pages" v-for="page in pages" class="page-list">
		<page-list-item
			:key="page.id"
			:page="page"
			:flatten="true"
			:open-modal="showPageModal"
			path="0"
			:depth="0"
		/>
	</div>

	<div class="b-bottom-bar">
		<el-button class="u-mla" @click="() => { this.showPageModal(pages[0]) }">+ Add Page</el-button>
	</div>
</div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import Draggable from 'vuedraggable';

import PageListItem from './PageListItem';
import BackBar from './BackBar';

export default {

	name: 'page-list',

	props: ['title'],

	components: {
		PageListItem,
		BackBar,
		Draggable
	},

	data() {
		return {

			loading: true

		};
	},

	computed: {
		...mapState('site', {
			pages: state => state.pages
		})
	},

	methods: {
		...mapActions({

			showPageModal: 'site/showPageModal'
		})

	}
};
</script>