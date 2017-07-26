<template>
<div class="page">



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



		<el-dialog
			title="Publish"
			v-model="publish_modal"
			:modal-append-to-body="true"
			:before-close="handleClose"
		>
			<el-form :model="form">
				<el-form-item label="Before you publish this page, give it an audit message">
					<el-input v-model="form.message" auto-complete="off"></el-input>
					<span class="help">This is used for an audit trail of your previously published pages</span>
				</el-form-item>
			</el-form>
			<span slot="footer" class="dialog-footer">
				<el-button @click="cancelPublish">Cancel and don't publish</el-button>
				<el-button v-if="form.message === ''" disabled type="primary" @click="publishPage">Publish now</el-button>
				<el-button v-else type="danger" @click="publishPage">Publish now</el-button>
			</span>
		</el-dialog>


	</div>
</div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import { Loading } from 'element-ui';

import Config from 'classes/Config';
import Sidebar from 'components/Sidebar';
import BlockPicker from 'components/BlockPicker';
import Icon from 'components/Icon';

/* global document */

export default {
	name: 'editor',

	components: {
		Sidebar,
		BlockPicker,
		Icon
	},


	data() {
		return {
			form: {
				message: ''
			}
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

	destroyed() {
		document.removeEventListener('keydown', this.onKeyDown);
		document.removeEventListener('keyup', this.onKeyUp);
	},

	methods: {
		...mapMutations([
			'changeView',
			'showPublishModal',
			'hidePublishModal'
		]),

		publishPage() {
			this.$api
				.post(`page/${this.$route.params.site_id}/publish`, this.page)
				.then(() => {
					this.hidePublishModal();
					this.$message({
						message: 'Published page',
						type: 'success',
						duration: 2000
					});
					this.form.message = '';
				})
				.catch(() => {});
		},

		cancelPublish() {
			this.hidePublishModal();
			this.form.message = '';
		},

		handleClose(done) {
			this.hidePublishModal();
			this.form.message = '';
		}
	},

	computed: {

		...mapState([
			'displayIframeOverlay',
			'currentView',
			'publishModal'
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
		},

		publish_modal: {
			get() {
				return this.publishModal.visible;
			},
			set(value) {
				if(value) {
					this.showPublishModal();
				}
				else {
					this.hidePublishModal();
				}
			}
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
