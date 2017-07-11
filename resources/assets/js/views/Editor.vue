<template>
<div class="editor-body">

	<div class="editor-wrapper" ref="editor">
		<iframe :src="getPreviewUrl" class="editor-content" :style="dimensions" frameborder="0" />
		<div
			class="iframe-overlay"
			:style="{ 'position' : displayIframeOverlay ? 'absolute' : null }"
		/>

		<!-- TODO: Move this bottom bar to its own component -->
		<footer class="b-bar">

			<el-tooltip class="item" effect="dark" content="Switch preview mode" placement="top">
				<el-select placeholder="view" v-model="currentView" class="switch-view">
					<el-option v-for="(view, key) in views" :label="view.label" :value="key" :key="view.label">
						<div class="view-icon">
							<Icon :name="view.icon" aria-hidden="true" width="20" height="20" />
						</div>
						<span class="view-label">{{ view.label }}</span>
					</el-option>
				</el-select>
			</el-tooltip>

			<el-button-group class="undo-redo">
				<el-button :disabled="!undoRedo.canUndo" @click="undo">
					<Icon name="undo" aria-hidden="true" width="14" height="14" class="ico" />
				</el-button>
				<el-button :disabled="!undoRedo.canRedo" @click="redo">
					<Icon name="redo" aria-hidden="true" width="14" height="14" class="ico" />
				</el-button>
			</el-button-group>

			<el-button class="save-button" @click="savePage">Save</el-button>

			<div
				class="page-status"
				:class="{'page-status--is-published' : page.is_published}"
			>{{ page.is_published ? 'published' : 'draft'}}</div>

		</footer>
	</div>

	<sidebar />
	<block-picker />
</div>
</template>

<script>
import { mapState } from 'vuex';
import { Loading } from 'element-ui';

import Config from 'classes/Config';
import Sidebar from 'components/Sidebar';
import BlockPicker from 'components/BlockPicker';
import Icon from 'components/Icon';

import { undoStackInstance } from 'plugins/undo-redo';
import { onKeyDown, onKeyUp } from 'plugins/key-commands';

/* global document */

export default {
	name: 'editor',

	components: {
		Sidebar,
		BlockPicker,
		Icon
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

		this.onKeyDown = onKeyDown(undoStackInstance);
		this.onKeyUp = onKeyUp(undoStackInstance);

		document.addEventListener('keydown', this.onKeyDown);
		document.addEventListener('keyup', this.onKeyUp);
	},

	destroyed() {
		document.removeEventListener('keydown', this.onKeyDown);
		document.removeEventListener('keyup', this.onKeyUp);
	},

	data() {
		return {
			currentView: 'desktop'
		};
	},

	computed: {
		...mapState([
			'preview',
			'displayIframeOverlay',
			'undoRedo'
		]),

		...mapState({
			page: state => state.page.pageData,
			pageLoaded: state => state.page.loaded
		}),

		getPreviewUrl() {
			return `${Config.get('base_url', '')}/preview/${this.$route.params.site_id}`;
		},

		dimensions() {
			return {
				width: this.views[this.currentView].width,
				height: this.views[this.currentView].height
			};
		}
	},

	watch: {
		pageLoaded() {
			this.loader.close();
		}
	},

	mounted() {
		this.loader = Loading.service({
			target: this.$refs.editor,
			text: 'Loading preview...',
			customClass: 'loading-overlay'
		});
	},

	methods: {
		savePage() {
			this.$api
				.put(`page/${this.$route.params.site_id}`, this.page)
				.then(() => {
					this.$snackbar.open({
						message: 'Successfully saved page'
					})
				})
				.catch(() => {});
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