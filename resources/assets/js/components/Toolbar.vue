<template>
<div class="toolbar">

	<el-tooltip class="item" effect="dark" content="Switch preview mode" placement="top">
		<el-select placeholder="view" v-model="view" class="switch-view">
			<el-option v-for="(view, key) in views" :label="view.label" :value="key" :key="view.label">
				<div class="view-icon">
					<icon :name="view.icon" aria-hidden="true" width="20" height="20" />
				</div>
				<span class="view-label">{{ view.label }}</span>
			</el-option>
		</el-select>
	</el-tooltip>

	<el-button class="toolbar__button-save" type="primary" @click="savePage" v-loading.fullscreen.lock="fullscreenLoading">Save</el-button>

	<el-button class="toolbar__button-preview" :plain="true" type="primary" @click="previewPage">Preview <icon name="newwindow" aria-hidden="true" width="14" height="14" class="ico" /></el-button>

	<el-button
		class="toolbar__button-publish"
		v-if="invalidBlocks"
		type="success"
		@click="showPublishModal"
	>Publish...</el-button>
	<el-button
		class="toolbar__button-publish"
		v-else
		type="success"
		@click="showPublishValidationWarningModal"
	>Publish...</el-button>

</div>


</template>


<script>
import { mapState, mapMutations, mapActions, mapGetters } from 'vuex';

import Icon from 'components/Icon';
import { undoStackInstance } from 'plugins/undo-redo';
import { win } from 'classes/helpers';
import Config from 'classes/Config';

export default {
	name: 'toolbar',

	components: {
		Icon
	},

	data() {
		return {
			fullscreenLoading: false
		}
	},

	computed: {
		...mapState([
			'displayIframeOverlay',
			'undoRedo',
			'currentView'
		]),

		...mapState({
			page: state => state.page.pageData,
			pageLoaded: state => state.page.loaded
		}),

		...mapGetters([
			'getInvalidBlocks',
			'draftPreviewURL'
		]),

		view: {
			get() {
				return this.currentView;
			},
			set(value) {
				this.changeView(value);
			}
		},

		draftLink() {
			return this.draftPreviewURL;
		},

		invalidBlocks() {
			var invalidBlocks = false;
			if(this.getInvalidBlocks().length === 0) {
				invalidBlocks = true;
			}
			return invalidBlocks;
		}
	},

	created() {
		this.views = {
			desktop: {
				icon: 'desktop',
				label: 'Desktop',
				width: '100%',
				height: '100vh'
			},
			tablet: {
				icon: 'tablet',
				label: 'Tablet',
				width: '768px',
				height: '1024px'
			},
			mobile: {
				icon: 'mobile',
				label: 'Mobile',
				width: '320px',
				height: '568px'
			}
		};
	},

	methods: {
		...mapMutations([
			'changeView',
			'showPublishModal',
			'showPublishValidationWarningModal'
		]),

		...mapActions([
			'handleSavePage'
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
			});
		},

		/* autosave the page and open a preview window */
		previewPage() {
			/* handleSavePage returns a promise so we here we wait for it to complete before
			opening the preview window */
			this.handleSavePage()
				.then(() => {
					win.open(this.draftLink,'_blank');
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
