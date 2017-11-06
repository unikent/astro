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
	v-loading.fullscreen.lock="loading"
	element-loading-text="Publishing your page..."
	class="publish-modal"
	@open="resetOptions"
	:close-on-press-escape="false"
	:close-on-click-modal="false"
	v-if="getSelectedPage"
>
	<div v-show="!published && error.message === ''">
		<p>You're about to publish the page <strong>{{ getSelectedPage.title }}</strong></p>
		<p>It will be published to the URL <el-tag type="gray">{{ renderedURL }}</el-tag></p>
		<div class="publish-modal__buttons">
			<span slot="footer" class="dialog-footer">
				<el-button @click="hidePublishModal">Cancel</el-button>
				<el-button type="danger" @click="publishPage">Publish now</el-button>
			</span>
		</div>
	</div>
	<div v-show="published">
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
				<el-button type="primary" @click="hidePublishModal">Close</el-button>
			</span>
		</div>
	</div>

	<div v-show="!published && error.message !== ''">
		<el-alert
			title="Sorry we had a problem publishing this page"
			type="error"
			:description="error.message"
			show-icon
			:closable=false
			>
		</el-alert>
		<el-collapse class="publish-modal__errors" v-show="error.techDetails !== ''">
			<el-collapse-item title="Still having problems?" name="1">
				<p>If you're having persistent problems publishing your page, contact us and let us know the following error message:</p>
				<el-tag type="gray">{{ error.techDetails }}</el-tag>
			</el-collapse-item>
		</el-collapse>
		<div class="publish-modal__buttons">
			<span slot="footer" class="dialog-footer">
				<el-button type="primary" @click="hidePublishModal">Close</el-button>
			</span>
		</div>
	</div>
</el-dialog>
</template>

<script>
import { mapState, mapMutations, mapGetters, mapActions } from 'vuex';

export default {
	name: 'publish-modal',

	data() {
		return {
			published: false,
			loading: false,
			error: {
				message: '',
				techDetails: ''
			}
		}
	},

	computed: {
		...mapState([
			'publishModal'
		]),

		...mapGetters([
			'siteDomain',
			'sitePath'
		]),

		...mapGetters('site', [
			'getPage'
		]),

		getPageIdOrPath() {
			if(this.publishModal.pagePath) {
				return { arrayPath: this.publishModal.pagePath };
			}
			else {
				return { id: this.$route.params.page_id };
			}
		},

		getSelectedPage() {
			return this.getPage(this.getPageIdOrPath);
		},

		// basically controls show/hide of the modal
		publishModalVisible: {
			get() {
				return this.publishModal.visible;
			},
			set(visible) {
				if(!visible) {
					this.hidePublishModal();
				}
			}
		},

		// frontend URL - so the user can view the page's url
		renderedURL() {
			return this.siteDomain + this.sitePath + this.getSelectedPage.path;
		}
	},

	methods: {
		...mapMutations([
			'showPublishModal',
			'hidePublishModal'
		]),

		...mapActions([
			'setPageStatusGlobally'
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
				.post('pages/' + this.getSelectedPage.id + '/publish', this.page)
				.then(() => {

					this.setPageStatusGlobally({
						...this.getPageIdOrPath,
						status: 'published'
					});

					this.loading = false;
					this.published = true;

					this.error.message = '';
				})
				.catch((error) => {
					if (error.config && error.response && error.response.data && error.response.data.errors && error.response.data.errors.length > 0) {
						error.response.data.errors.forEach((apiError, i) => {
							this.error.message += ` - ${apiError.details.id}\n`;
						});
						this.error.techDetails = error.config.method + ' ' + error.config.url + ' ' + error.response.status + ' (' + error.response.statusText + ')';
					}
					else {
						this.error.message = 'Network connection problem - you may not have a reliable connection to the internet.';
					}
					this.loading = false;
					this.published = false;
				});
		},

		resetOptions() {
			this.loading = false;
			this.published = false;
			this.error = {
				message: '',
				techDetails: ''
			};
		}
	}
};
</script>
