<template>
<div class="page">

	<div class="editor-body">
		<div class="editor-wrapper" ref="editor">
			<iframe :src="getPreviewUrl" id="editor-content" class="editor-content" :style="dimensions" frameborder="0" />
			<div
				class="iframe-overlay"
				:style="{ 'position' : displayIframeOverlay ? 'absolute' : null }"
			/>
		</div>
		<sidebar>
			<edit-profile />
		</sidebar>
	</div>

</div>
</template>

<script>
import { mapState } from 'vuex';

import Config from 'classes/Config';
import ResizableSidebar from 'components/sidebar/ResizableSidebar';
import EditProfile from '@profiles/components/EditProfile';

export default {
	name: 'profile-editor',

	props: ['site-id', 'profile-id'],

	components: {
		ResizableSidebar,
		EditProfile
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

	computed: {

		...mapState([
			'displayIframeOverlay',
			'currentView'
		]),

		getPreviewUrl() {
			return `${Config.get('base_url', '')}/site/${this.siteId}/preview/profile/${this.profileId}`;
		},

		dimensions() {
			return {
				width: this.views[this.currentView].width,
				height: this.views[this.currentView].height
			};
		}

	}

};
</script>
