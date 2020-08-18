<template>
<div class="toolbar">

	<el-button-group class="undo-redo">
		<el-tooltip effect="dark" content="Undo">
			<el-button :disabled="!undoRedo.canUndo" @click="undo">
				<Icon name="undo" aria-hidden="true" width="14" height="14" class="ico" />
			</el-button>
		</el-tooltip>
		<el-tooltip effect="dark" content="Redo">
			<el-button :disabled="!undoRedo.canRedo" @click="redo">
				<Icon name="redo" aria-hidden="true" width="14" height="14" class="ico" />
			</el-button>
		</el-tooltip>
	</el-button-group>

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
		v-loading.fullscreen.lock="fullscreenLoading"
		@click="previewPage"
		:disabled="pageHasLayoutErrors"
	>Preview <icon name="newwindow" aria-hidden="true" :width="14" :height="14" class="ico" /></el-button>

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
			'draftPreviewURL',
			'canUser',
			'getAllBlockErrorsCount'
		]),

		draftLink() {
			return this.draftPreviewURL;
		},

		invalidBlocks() {
			return this.getAllBlockErrorsCount === 0 ? true : false;
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
				// TODO: catch errors
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

			// show loading screen when someone presses preview
			this.fullscreenLoading = true;

			/* handleSavePage returns a promise so we here we wait for it to complete before
			opening the preview window */
			// TODO: catch errors
			this.handleSavePage()
				.then(() => {

					// hide loading screen after the page has saved
					this.fullscreenLoading = false;

					win.open(this.draftLink, '_blank');
				});
		},

		/* save page and then open publish modal */
		publishPage() {
			// TODO: catch errors
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
