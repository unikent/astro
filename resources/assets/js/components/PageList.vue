<template>
<div>
	<back-bar :title="title" />

	<div v-if="pages" v-for="page in pages" class="page-list">
		<page-list-item
			:key="page.id"
			:page="page"
			:flatten="true"
			:open-modal="showPageModal"
			:open-edit-modal="showEditPageModal"
			:open-copy-modal="showCopyPageModal"
			path="0"
			:depth="0"
		/>
	</div>

	<div class="b-bottom-bar">

		<el-button v-if="canUser('page.add')" class="u-mla" @click="() => { this.showPageModal(pages[0]) }">+ Add Page</el-button>
	</div>
</div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex';
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
		}),

		...mapGetters([
			'canUser'
		])
	},

	methods: {
		...mapActions({

			showPageModal: 'site/showPageModal',
			showEditPageModal: 'site/showEditPageModal',
			showCopyPageModal: 'site/showCopyPageModal'
		})

	}
};
</script>