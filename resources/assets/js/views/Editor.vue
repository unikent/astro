<template>
<div class="page"  v-if="canUser('page.edit')">

	<div class="editor-body">
		<div class="editor-wrapper" ref="editor">
			<iframe :src="getPreviewUrl" id="editor-content" class="editor-content" :style="dimensions" frameborder="0" />
			<div
				class="iframe-overlay"
				:style="{ 'position' : displayIframeOverlay ? 'absolute' : null }"
			/>
		</div>
		<sidebar />
	</div>

	<modal-container />
</div>
<div class="page" v-else>
	<el-alert
		title="You don't have access to this site"
		type="error"
		description="You don't have permission to access this site. Please contact the site owner."
		:closable="false"
		show-icon
	>
	</el-alert>
</div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import { Loading } from 'element-ui';

import Config from 'classes/Config';
import Sidebar from 'components/sidebar';
import ModalContainer from 'components/ModalContainer';
import Icon from 'components/Icon';
import { undoStackInstance } from 'plugins/undo-redo';
import { onKeyDown, onKeyUp } from 'plugins/key-commands';
import requiresSitePermissions from 'mixins/requiresSitePermissionsMixin';

export default {
	name: 'editor',

	mixins: [requiresSitePermissions],

	components: {
		Sidebar,
		ModalContainer,
		Icon
	},

	provide: {
		fieldType: 'block'
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
		// we have left the page editor so remove the snapeshot of the latest saved content
		this.$store.commit('resetCurrentSavedState');

		document.removeEventListener('keydown', this.onKeyDown);
		document.removeEventListener('keyup', this.onKeyUp);
	},

	methods: {

		showLoader() {
			this.loader = Loading.service({
				target: this.$refs.editor,
				text: 'Loading preview...',
				customClass: 'loading-overlay'
			});
		},
	},
	computed: {

		...mapState([
			'displayIframeOverlay',
			'currentView'
		]),

		...mapState({
			pageLoaded: state => state.page.loaded
		}),

		...mapGetters([
			'canUser'
		]),

		...mapGetters('auth', [
			'username'
		]),

		// get the URL for the route to show the editor preview page (not the external page preview)
		getPreviewUrl() {
			// TODO: Don't reload page when page_id changes, use state instead
			return `${Config.get('base_url', '')}/preview/${this.$route.params.page_id}`;
		},

		dimensions() {
			return {
				width: this.views[this.currentView].width,
				height: this.views[this.currentView].height
			};
		}

	},

	watch: {
		pageLoaded(hideLoader) {
			// update/set the current snapshot of the saved page content
			this.$store.commit('updateCurrentSavedState');
			if(hideLoader) {
				if(this.loader) {
					this.loader.close();
				}
			}
			else {
				this.showLoader();
			}
		}
	}
};
</script>
