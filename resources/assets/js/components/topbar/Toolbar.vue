<template>
<div class="toolbar">

	<switch-mode />

	<el-button
		v-if="canUser('page.edit')"
		class="toolbar__button-save"
		type="primary"
		@click="savePage"
		v-loading.fullscreen.lock="fullscreenLoading"
		:disabled="pageHasLayoutErrors"
	>Save</el-button>

	<el-button
		v-if="canUser('page.preview')"
		class="toolbar__button-preview"
		plain
		@click="previewPage"
		:disabled="pageHasLayoutErrors"
	>Preview <icon name="newwindow" aria-hidden="true" width="14" height="14" class="ico" /></el-button>

	<template v-if="canUser('page.publish')">
		<el-button
			class="toolbar__button-publish"
			v-if="invalidBlocks"
			type="success"
			@click="publishPage"
			:disabled="pageHasLayoutErrors"
		>Publish...</el-button>
		<el-button
			class="toolbar__button-publish"
			v-else
			type="success"
			@click="showPublishValidationWarningModal"
			:disabled="pageHasLayoutErrors"
		>Publish...</el-button>
	</template>

</div>
</template>


<script>
import { mapState, mapMutations, mapActions, mapGetters } from 'vuex';

import SwitchMode from 'components/topbar/SwitchMode';
import Icon from 'components/Icon';
import { undoStackInstance } from 'plugins/undo-redo';
import { win } from 'classes/helpers';

export default {
	name: 'toolbar',

	components: {
		SwitchMode,
		Icon
	},

	data() {
		return {
			fullscreenLoading: false
		}
	},

	computed: {
		...mapState([
			'undoRedo'
		]),

		...mapState({
			page: state => state.page.pageData,
			layoutErrors: state => state.page.layoutErrors
		}),

		...mapGetters([
			'getInvalidBlocks',
			'draftPreviewURL',
			'canUser'
		]),

		draftLink() {
			return this.draftPreviewURL;
		},

		invalidBlocks() {
			return this.getInvalidBlocks().length === 0 ? true : false;
		},

		pageHasLayoutErrors() {
			return this.layoutErrors.length !== 0;
		}
	},

	methods: {
		...mapMutations([
			'showPublishModal',
			'showPublishValidationWarningModal'
		]),

		...mapActions([
			'handleSavePage',
			'setPageStatusGlobally'
		]),

		updateCurrentSavedState() {
			this.$store.commit('updateCurrentSavedState');
		},

		/**
		save the page
		*/
		savePage() {
			// show the loading spinner
			this.fullscreenLoading = true;

			// we want a user notification
			// also wait till handleSavePage has finished to hide the loading spinner
			this.handleSavePage({ notify: true })
				.then(() => {
					this.fullscreenLoading = false;

					// we only need to update the status if it isn't "new" or "draft"
					if(this.page.status === 'published') {
						this.setPageStatusGlobally({
							id: this.page.id,
							status: 'draft'
						});
					}
				});
		},

		/* autosave the page and open a preview window */
		previewPage() {
			/* handleSavePage returns a promise so we here we wait for it to complete before
			opening the preview window */
			this.handleSavePage()
				.then(() => {
					win.open(this.draftLink, '_blank');
				});
		},

		/* save page and then open publish modal */
		publishPage() {
			this.handleSavePage()
				.then(() => {
					this.showPublishModal();
				});
		},

		undo() {
			return undoStackInstance.undo();
		},

		redo() {
			return undoStackInstance.redo();
		}
	}

};
</script>
