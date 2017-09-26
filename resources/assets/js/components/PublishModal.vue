/**
PublishModal.Vue

Controls the display of a modal when the user tried to publish a page

There is a single modal, but different messages can be displayed in it.
Using a single modal rather than a series of separate ones makes the transition between different states visually more fluid.

The 3 states are:

1. The initial state. User is given the option to publish or cancel.
2. A successful publish. User is shown a link to the newly-published page.
3. An error message. Details of the error are given in a slide-down, in case they need to contact with the problem they're having.

An element loading spinner is shown after the user hits 'Publish'.

*/
<template>
<el-dialog
	title="Publish"
	v-model="publishModalVisible"
	:modal-append-to-body="true"
	v-loading="loading"
	element-loading-text="Publishing your page..."
	class="publish-modal"
	:before-close="handleClose"
	:close-on-press-escape="false"
	:close-on-click-modal="false"
>
	<div :style="published===true || error!=='' ? 'display:none;': 'display:block;'">
		<p>You're about to publish the page <strong>{{ pageTitle }}</strong></p>
		<p>It will be published to the URL <el-tag type="gray">{{ renderedURL }}</el-tag></p>
		<div class="publish-modal__buttons">
			<span slot="footer" class="dialog-footer">
				<el-button @click="cancelPublish">Cancel</el-button>
				<el-button type="danger" @click="publishPage">Publish now</el-button>
			</span>
		</div>
	</div>
	<div :style="published===true ? 'display:block;': 'display:none;'">
		<el-alert
			title="Your page was published"
			type="success"
			show-icon
			:closable=false
			>
		</el-alert>
		<div class="publish-modal__message">View your new page now at <a :href="renderedURL" target="_blank">{{ renderedURL }}</a> (opens in a new tab)</div>
		<div class="publish-modal__buttons">
			<span slot="footer" class="dialog-footer">
				<el-button type="primary" @click="cancelPublish">Close</el-button>
			</span>
		</div>
	</div>
	<div :style="published===false && error!=='' ? 'display:block;': 'display:none;'">
		<el-alert
			title="Page not published"
			type="error"
			description="Sorry we had a problem publishing this page. It might be a connection problem, so try again in a bit."
			show-icon
			:closable=false
			>
		</el-alert>
		<el-collapse class="publish-modal__errors">
			<el-collapse-item title="Report errors" name="1">
				<p>If you're having persistent problems publishing your page, contact us and let us know the following error message:</p>
				<el-tag type="gray">{{ error }}</el-tag>
			</el-collapse-item>
		</el-collapse>
		<div class="publish-modal__buttons">
			<span slot="footer" class="dialog-footer">
				<el-button type="primary" @click="cancelPublish">Close</el-button>
			</span>
		</div>
	</div>
</el-dialog>
</template>

<script>
import { mapState, mapMutations } from 'vuex';


export default {
	name: 'publish-modal',

	data() {
		return {
			published: false,
			loading: false,
			error: ''
		}
	},

	computed: {
		...mapState([
			'publishModal'
		]),

		...mapState({
			pageTitle: state => state.page.pageTitle,
			pagePath: state => state.page.pagePath,
			pageSlug: state => state.page.pageSlug,
			siteDomain: state => state.page.siteDomain,
			sitePath: state => state.page.sitePath
		}),

		// basically controls show/hide of the modal
		publishModalVisible: {
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
		},
		// frontend URL - so the user can view their newly-published page
		renderedURL() {
			return this.siteDomain + this.sitePath + this.pagePath;
		}
	},

	methods: {
		...mapMutations([
			'showPublishModal',
			'hidePublishModal'
		]),

		/**
		publish the page
		*/
		publishPage() {
			// show the loading spinner first, in case of latency on publish
			// when the publish has finished ok, hide the spinner and show the published message in the modal
			// if there's a problem, show an error message
			this.loading = true;
			this.$api
				.post('pages/' + this.$route.params.page_id + '/publish', this.page)
				.then(() => {
					this.loading = false;
					this.published = true;
					this.error = '';
				})
				.catch((error) => {
					this.loading = false;
					this.published = false;
					this.error = error.config.method + ' ' + error.config.url + ' ' + error.response.status + ' (' + error.response.statusText + ')';
				});
		},

		/**
		called when the user clicks the X icon, clicks away from the modal, or presses ESC
		*/
		handleClose() {
			this.cancelPublish();
		},

		/**
		cancel the publish modal
		*/
		cancelPublish() {
			// need a bit of time to hide the modal before we turn off the published state, because the modal takes a little while to disappear
			setTimeout(() => {
				this.loading = false;
				this.published = false;
				this.error = '';
			}, 300);
			this.hidePublishModal();
		}
	}
};
</script>
