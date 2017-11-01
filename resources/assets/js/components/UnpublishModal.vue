/**
UnpublishModal.Vue

Controls the display of a modal when the user tried to unpublish a page

There is a single modal, but different messages can be displayed in it.
Using a single modal rather than a series of separate ones makes the transition between different states visually more fluid.

The 3 states are:

1. The initial state. User is given the option to unpublish or cancel.
2. A successful unpublish. 
3. An error message. Details of the error are given in a slide-down, in case they need to contact with the problem they're having.

An element loading spinner is shown after the user hits 'Unpublish'.

*/
<template>
<el-dialog
	title="Unpublish"
	v-model="unpublishModalVisible"
	:modal-append-to-body="true"
	v-loading.fullscreen.lock="loading"
	element-loading-text="Publishing your page..."
	class="publish-modal"
	:before-close="handleClose"
	:close-on-press-escape="false"
	:close-on-click-modal="false"
>

	<div :style="unpublished===true || error!=='' ? 'display:none;': 'display:block;'">
		<el-alert
			:title="`Unpublish the page ${unpublishModal.page.title}`"
			type="warning"
			:description="renderedURL"
			show-icon
			:closable="false"
		>
		</el-alert>
		<p>When you unpublish a page it will be removed immediately from the website.</p>
		<p>It will still appear in your page listing as 'unpublished' and you will be able to edit or republish it later.</p>
		<div class="publish-modal__buttons">
			<span slot="footer" class="dialog-footer">
				<el-button @click="cancelUnpublish">Cancel</el-button>
				<el-button type="danger" @click="unpublishPage">Unpublish now</el-button>
			</span>
		</div>
	</div>

	<div :style="unpublished===true ? 'display:block;': 'display:none;'">
		<el-alert
			:title="`You have successfully unpublished the page ${unpublishModal.page.title}`"
			type="success"
			show-icon
			:closable="false"
			>
		</el-alert>
		<p>People viewing the url <el-tag type="gray">{{renderedURL}}</el-tag> will now get a 'not found' error page.</p>
		<p>The page is still available in your page listing sidebar with a status 'Unpublished'.</p>
		<p>You can edit or republish it just like any other unpublished page.</p>
		<div class="publish-modal__buttons">
			<span slot="footer" class="dialog-footer">
				<el-button type="primary" @click="cancelUnpublish">Close</el-button>
			</span>
		</div>
	</div>

	<div :style="unpublished===false && error!=='' ? 'display:block;': 'display:none;'">
		<el-alert
			title="Page not unpublished"
			type="error"
			description="Sorry we had a problem unpublishing this page. It might be a connection problem, so try again later."
			show-icon
			:closable="false"
		>
		</el-alert>
		<el-collapse class="publish-modal__errors">
			<el-collapse-item title="Still having problems?" name="1">
				<p>If you're having persistent problems unpublishing your page, contact us and let us know the following error message:</p>
				<el-tag type="gray">{{ error }}</el-tag>
			</el-collapse-item>
		</el-collapse>
		<div class="publish-modal__buttons">
			<span slot="footer" class="dialog-footer">
				<el-button type="primary" @click="cancelUnpublish">Close</el-button>
			</span>
		</div>
	</div>
</el-dialog>
</template>

<script>
import { mapState, mapMutations, mapGetters } from 'vuex';
import Config from '../classes/Config.js';


export default {
	name: 'unpublish-modal',

	data() {
		return {
			unpublished: false,
			loading: false,
			error: ''
		}
	},

	computed: {
		...mapState([
			'unpublishModal'
		]),

		...mapGetters([
			'siteDomain',
			'sitePath'
		]),

		// basically controls show/hide of the modal
		unpublishModalVisible: {
			get() {
				return this.unpublishModal.visible;
			},
			set(visible) {
				if (!visible) {
					this.hideUnpublishModal();
				}
			}
		},
		// frontend URL - so the user can view the page's url
		renderedURL() {
			return this.siteDomain + this.sitePath + this.unpublishModal.page.path;
		}
	},

	methods: {
		...mapMutations([
			'showUnpublishModal',
			'hideUnpublishModal'
		]),

		/**
		unpublish the page
		*/
		unpublishPage() {
			// show the loading spinner first, in case of latency on unpublish
			// when the unpublish has finished ok, hide the spinner and show the unpublished message in the modal
			// if there's a problem, show an error message
			this.loading = true;
			this.$api
				.post('pages/' + this.$route.params.page_id + '/unpublish', this.page) // TODO - check this later
				.then(() => {
					this.loading = false;
					this.unpublished = true;
					this.error = '';
				})
				.catch((error) => {
					if (error.config && error.response) {
						this.error = error.config.method + ' ' + error.config.url + ' ' + error.response.status + ' (' + error.response.statusText + ')';
					}
					else {
						this.error = 'Network connection problem - you may not have a reliable connection to the internet.';
					}
					this.loading = false;
					this.unpublished = false;
				});
		},

		/**
		called when the user clicks the X icon, clicks away from the modal, or presses ESC
		*/
		handleClose() {
			this.cancelPublish();
		},

		/**
		cancel the unpublish modal
		*/
		cancelUnpublish() {
			// need a bit of time to hide the modal before we turn off the unpublished state, because the modal takes a little while to disappear
			setTimeout(() => {
				this.loading = false;
				this.unpublished = false;
				this.error = '';
			}, 300);
			this.hideUnpublishModal();
		}
	}
};
</script>
