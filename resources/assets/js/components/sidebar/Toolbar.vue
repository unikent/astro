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

	<el-button-group class="undo-redo">
		<el-button :disabled="!undoRedo.canUndo" @click="undo">
			<icon name="undo" aria-hidden="true" width="14" height="14" class="ico" />
		</el-button>
		<el-button :disabled="!undoRedo.canRedo" @click="redo">
			<icon name="redo" aria-hidden="true" width="14" height="14" class="ico" />
		</el-button>
	</el-button-group>

	<el-button class="toolbar__button-save" type="success" @click="savePage">Save</el-button>

	<a class="el-button el-button--info toolbar__button-preview" type="info" :href="draftLink" target="_blank">Preview <icon name="newwindow" aria-hidden="true" width="14" height="14" class="ico" /></a>

	<el-button class="toolbar__button-publish" type="danger" @click="showPublishModal">Publish ...</el-button>

</div>


</template>


<script>
import { mapState, mapMutations } from 'vuex';

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
			//return ;
			return '/draft/' + `${this.$route.params.page_id}`;
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
			'showPublishModal'
		]),
		savePage() {
			this.$api
				.put(`pages/${this.$route.params.page_id}/content`, {
                    blocks: this.page.blocks
                })
				.then(() => {
					this.$message({
						message: 'Page saved',
						type: 'success',
						duration: 2000
					});
				})
				.catch(() => {});
		},

		// TODO - add preview the page functionality
		previewPage() {
			this.$message({
				message: 'TODO: previewing page...',
				type: 'success',
				duration: 2000
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
