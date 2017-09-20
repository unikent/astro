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

	<el-button class="toolbar__button-save" type="success" @click="savePage">Save</el-button>

	<el-button class="toolbar__button-preview" :plain="true" type="info" @click="previewPage">Preview <icon name="newwindow" aria-hidden="true" width="14" height="14" class="ico" /></el-button>

	<el-button class="toolbar__button-publish" type="danger" @click="showPublishModal">Publish ...</el-button>

</div>


</template>


<script>
import { mapState, mapMutations, mapActions } from 'vuex';

import Icon from 'components/Icon';
import { undoStackInstance } from 'plugins/undo-redo';


export default {
	name: 'toolbar',

	components: {
		Icon
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

		view: {
			get() {
				return this.currentView;
			},
			set(value) {
				this.changeView(value);
			}
		},

		draftLink() {
			return window.astro.base_url + '/draft/' + `${this.$route.params.page_id}`;
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
		]),

		...mapActions([
			'handleSavePage'
		]),

		updateCurrentSavedState() {
			this.$store.commit('updateCurrentSavedState');
		},

		/**
		save the page
		also send a notification object so we can output a success message
		*/
		savePage() {
			this.handleSavePage({notify: this.$notify});
		},

		/* autosave the page and open a preview window */
		previewPage() {
			/* handleSavePage returns a promise so we here we wait for it to complete before
			opening the preview window */
			this.handleSavePage()
				.then(() => {
					window.open(this.draftLink,'_blank');
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
