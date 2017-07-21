<template>
<main>
	<toolbar/>

	<div class="editor-body">

		<div class="editor-wrapper" ref="editor">
			<iframe :src="getPreviewUrl" class="editor-content" :style="dimensions" frameborder="0" />
			<div
				class="iframe-overlay"
				:style="{ 'position' : displayIframeOverlay ? 'absolute' : null }"
			/>


		</div>

		<sidebar />
		<block-picker />
	</div>
</main>
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

import Toolbar from 'components/Sidebar/Toolbar';


/* global document */

export default {
	name: 'editor',

	components: {
		Sidebar,
		BlockPicker,
		Icon,
		Toolbar
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

	computed: {

		...mapState([
			'displayIframeOverlay',
			'currentView'
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
	}
};
</script>
