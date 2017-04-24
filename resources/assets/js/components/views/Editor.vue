<style lang="scss">
.el-select-dropdown__item {
	.view-icon {
		width: 22px;
		text-align: center;
		float: left;

		svg {
			vertical-align: top;
			fill: #555;
		}
	}
	.view-label {
		float: right;
		color: #8492a6;
		font-size: 13px;
	}
	&.selected {
		.view-label {
			color: #fff;
		}
		svg {
			fill: #fff;
		}
	}
}

.loading-overlay {
	background-color: #fff;
	z-index: 0;
}

.el-input__inner {
	user-select: none;
}

.iframe-overlay {
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
}

.switch-view {
	vertical-align: middle;
	width: 105px;
}

.undo-redo {
	margin-left: 10px;
	color: #1f2d3d;

	.ico {
		fill: #555;
	}
}

.left-collapse,
.right-collapse {
	background-color: #f5f7fa;
	width: 24px;
	height: 40px;
	position: absolute;
	border: 1px solid #bcc8dc;
	transform: translateY(-50%);
	top: 50%;
	color: #666;
	line-height: 38px;
	font-size: 13px;
}

.left-collapse {
	right: -24px;
	border-left: 0;
	border-bottom-right-radius: 4px;
	border-top-right-radius: 4px;
}

.right-collapse {
	left: -24px;
	border-right: 0;
	border-bottom-left-radius: 4px;
	border-top-left-radius: 4px;
}

.image-preview {
	text-align: center;

	img {
		max-width: 100%;
		height: auto;
	}
}

.save-button {
	float: right;
}

.tmp-preview {
	background-color: #f3f7fd;
	white-space: pre-wrap;
}
</style>

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