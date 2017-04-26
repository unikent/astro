<template>
<div class="editor-body">
	<div class="editor-wrapper" ref="editor">
		<iframe ref="iframe" class="editor-content" :style="dimensions" :src="getUrl" frameborder="0"></iframe>
		<div class="iframe-overlay"></div>
		<footer class="b-bar">

			<el-tooltip class="item" effect="dark" content="Switch preview mode" placement="top">
				<el-select placeholder="view" v-model="currentView" class="switch-view">
					<el-option v-for="(view, key) in views" :label="view.label" :value="key">
						<div class="view-icon">
							<Icon :glyph="view.icon" aria-hidden="true" width="20" height="20" />
						</div>
						<span class="view-label">{{ view.label }}</span>
					</el-option>
				</el-select>
			</el-tooltip>

			<el-button-group class="undo-redo">
				<el-button disabled><Icon :glyph="UndoIcon" aria-hidden="true" width="14" height="14" class="ico" /></i></el-button>
				<el-button disabled><Icon :glyph="RedoIcon" aria-hidden="true" width="14" height="14" class="ico" /></el-button>
			</el-button-group>

			<el-button class="save-button" @click="showPageData = true">Save</el-button>
		</footer>
	</div>

	<nav :style="{marginLeft: sideBarOpen ? 0 : '-250px'}" class="editor-nav editor-sidebar">
		<page-sidebar></page-sidebar>
		<div class="left-collapse" @click="sideBarOpen = !sideBarOpen">
			<i :class="{'el-icon-arrow-left' : sideBarOpen, 'el-icon-arrow-right' : !sideBarOpen }" :style="{ marginLeft: sideBarOpen ? '2px' : '5px'}"></i>
		</div>
	</nav>

	<aside :style="{marginRight: blockListOpen ? 0 : '-380px'}" class="editor-component-list editor-sidebar">
		<div class="right-collapse" @click="blockListOpen = !blockListOpen">
			<i :class="{'el-icon-arrow-right' : blockListOpen, 'el-icon-arrow-left' : !blockListOpen}" :style="{ marginLeft: blockListOpen ? '5px' : '3px'}"></i>
		</div>
		<block-sidebar></block-sidebar>
	</aside>

	<el-dialog title="Current page data" v-model="showPageData">
		<pre class="tmp-preview">{{ JSON.stringify(page, null, 4) }}</pre>
	</el-dialog>

	<el-dialog v-model="preview.visible">
		<img :src="preview.url" />
	</el-dialog>
</div>
</template>

<script>
import { Loading } from 'element-ui';
import { mapState } from 'vuex';

import PageSidebar from '../PageSidebar.vue';
import BlockSidebar from '../BlockSidebar.vue';

import Icon from '../Icon.vue';
import UndoIcon from '!IconPath/undo.svg';
import RedoIcon from '!IconPath/redo.svg';
import DesktopIcon from '!IconPath/desktop.svg';
import TabletIcon from '!IconPath/tablet.svg';
import MobileIcon from '!IconPath/mobile.svg';

/* global window */

export default {
	name: 'editor',

	components: {
		PageSidebar,
		BlockSidebar,
		Icon
	},

	data() {
		return {
			views: {
				'desktop': {
					icon: DesktopIcon,
					label: 'Desktop',
					width: '100%',
					height: '100vh'
				},
				'tablet': {
					icon: TabletIcon,
					label: 'Tablet',
					width: '768px',
					height: '1024px'
				},
				'mobile': {
					icon: MobileIcon,
					label: 'Mobile',
					width: '320px',
					height: '568px'
				}
			},
			currentView: 'desktop',
			UndoIcon,
			RedoIcon,
			showPageData: false,
			sideBarOpen: true,
			blockListOpen: true
		};
	},

	computed: {
		getUrl() {
			return `${window.Laravel.base}/preview`;
		},

		dimensions() {
			return {
				width: this.views[this.currentView].width,
				height: this.views[this.currentView].height
			};
		},

		...mapState([
			'page',
			'preview'
		])
	},

	mounted() {
		const
			loader = Loading.service({
				target: this.$refs.editor,
				text: 'Loading preview...',
				customClass: 'loading-overlay'
			}),
			removeLoader = () => {
				loader.close()
				this.$refs.iframe.removeEventListener('load', removeLoader);
			};

		this.$refs.iframe.addEventListener('load', removeLoader);
	}
};
</script>