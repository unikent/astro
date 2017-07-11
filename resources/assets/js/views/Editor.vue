<template>
<div class="editor-body">
	<div class="editor-wrapper" ref="editor">
		<iframe ref="iframe" class="editor-content" :style="dimensions" :src="getPreviewUrl" frameborder="0" />
		<div
			class="iframe-overlay"
			:style="{ 'position' : displayIframeOverlay ? 'absolute' : null }"
		/>
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

	<nav
		:style="{marginLeft: sideBarOpen ? 0 : '-250px', maxWidth: 0, minWidth: '249px'}"
		class="editor-nav editor-sidebar"
		:class="{ 'editor-nav--is-over': sideBarHover || !sideBarOpen }"
		@mouseenter="sideBarHover = true"
		@mouseleave="sideBarHover = false"
	>
		<page-sidebar></page-sidebar>
		<div class="left-collapse" @click="sideBarOpen = !sideBarOpen">
			<i :class="{'el-icon-arrow-left' : sideBarOpen, 'el-icon-arrow-right' : !sideBarOpen }" :style="{ marginLeft: sideBarOpen ? '2px' : '5px'}"></i>
		</div>
	</nav>

	<aside
		:style="{marginRight: blockListOpen ? 0 : '-380px'}"
		class="editor-component-list editor-sidebar"
		:class="{ 'editor-component-list--is-over': blockListHover || !blockListOpen }"
		@mouseenter="blockListHover = true"
		@mouseleave="blockListHover = false"
	>
		<div class="right-collapse" @click="blockListOpen = !blockListOpen">
			<i :class="{'el-icon-arrow-right' : blockListOpen, 'el-icon-arrow-left' : !blockListOpen}" :style="{ marginLeft: blockListOpen ? '5px' : '3px'}"></i>
		</div>
		<block-sidebar></block-sidebar>
	</aside>

	<el-dialog v-model="preview.visible" style="text-align: center;">
		<img :src="preview.url" style="max-width: 95%;" />
	</el-dialog>
</div>
</template>

<script>
import { Loading } from 'element-ui';
import { mapState } from 'vuex';

import Config from 'classes/Config';
import PageSidebar from 'components/PageSidebar';
import BlockSidebar from 'components/Sidebar/BlockSidebar';
import { undoStackInstance } from 'plugins/undo-redo';
import { onKeyDown, onKeyUp } from 'plugins/key-commands';

import Icon from 'components/Icon';

/* global document */

export default {
	name: 'editor',

	components: {
		PageSidebar,
		BlockSidebar,
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
			currentView: 'desktop',
			sideBarOpen: true,
			blockListOpen: true,
			blockListHover: false,
			sideBarHover: false
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
				.then((response) => {
					console.log(response.data);
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