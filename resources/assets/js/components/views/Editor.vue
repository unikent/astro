<template>
<div class="editor-body">
	<div class="editor-wrapper" ref="editor">
		<iframe ref="iframe" class="editor-content" :style="dimensions" :src="getPreviewUrl" frameborder="0"></iframe>
		<div
			class="iframe-overlay"
			:style="{ 'position' : showIframeOverlay ? 'absolute' : null }"
		/>
		<footer class="b-bar">

			<el-tooltip class="item" effect="dark" content="Switch preview mode" placement="top">
				<el-select placeholder="view" v-model="currentView" class="switch-view">
					<el-option v-for="(view, key) in views" :label="view.label" :value="key" :key="view.label">
						<div class="view-icon">
							<Icon :glyph="view.icon" aria-hidden="true" width="20" height="20" />
						</div>
						<span class="view-label">{{ view.label }}</span>
					</el-option>
				</el-select>
			</el-tooltip>

			<el-button-group class="undo-redo">
				<el-button disabled><Icon :glyph="undoIcon" aria-hidden="true" width="14" height="14" class="ico" /></i></el-button>
				<el-button disabled><Icon :glyph="redoIcon" aria-hidden="true" width="14" height="14" class="ico" /></el-button>
			</el-button-group>

			<el-button class="save-button" @click="savePage">Save</el-button>
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

import Config from '../../classes/Config';
import PageSidebar from '../PageSidebar';
import BlockSidebar from '../BlockSidebar';

import Icon from '../Icon';
import undoIcon from 'IconPath/undo.svg';
import redoIcon from 'IconPath/redo.svg';
import desktopIcon from 'IconPath/desktop.svg';
import tabletIcon from 'IconPath/tablet.svg';
import mobileIcon from 'IconPath/mobile.svg';

export default {
	name: 'editor',

	components: {
		PageSidebar,
		BlockSidebar,
		Icon
	},

	created() {
		this.undoIcon = undoIcon;
		this.redoIcon = redoIcon;
		this.views = {
			desktop: {
				icon: desktopIcon,
				label: 'Desktop',
				width: '100%',
				height: '100vh'
			},
			tablet: {
				icon: tabletIcon,
				label: 'Tablet',
				width: '768px',
				height: '1024px'
			},
			mobile: {
				icon: mobileIcon,
				label: 'Mobile',
				width: '320px',
				height: '568px'
			}
		};
	},

	data() {
		return {
			currentView: 'desktop',
			showPageData: false,
			sideBarOpen: true,
			blockListOpen: true
		};
	},

	computed: {
		...mapState([
			'preview',
			'showIframeOverlay'
		]),

		...mapState({
			page: state => state.page.pageData
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
	},

	methods: {
		savePage() {
			// this.showPageData = true;

			this.$api
				.put('page/1', this.page)
				.then((response) => {
					console.log(response.data);
				});

			console.log(
				JSON.stringify(this.page, null, 4)
			);
		}
	}
};
</script>