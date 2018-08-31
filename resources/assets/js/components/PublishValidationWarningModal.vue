/**
 *
 * Shows a warning message when there are validation errors, and the user tries to publish a page.
 *
 */
<template>
<el-dialog
	title="Publish"
	:visible.sync="publishValidationWarningModalVisible"
	:modal-append-to-body="true"
	:before-close="handleClose"
	:close-on-press-escape="false"
	:close-on-click-modal="false"
	class="publish-modal"
>
	<div>
		<el-alert
			title="Page not published"
			type="error"
			description="Sorry you have some validation issues with parts of this page. You can't publish the page till you've fixed these issues."
			show-icon
			:closable=false
			>
		</el-alert>
		<div class="publish-modal__buttons">
			<span slot="footer" class="dialog-footer">
				<el-button type="primary" @click="openErrors">Close this message and open the error sidebar</el-button>
			</span>
		</div>
	</div>
</el-dialog>
</template>

<script>
import { mapState, mapMutations } from 'vuex';

/* global setTimeout */

export default {
	name: 'publish-validation-warning-modal',

	computed: {
		...mapState([
			'publishValidationWarningModal'
		]),

		// basically controls show/hide of the modal
		publishValidationWarningModalVisible: {
			get() {
				return this.publishValidationWarningModal.visible;
			},
			set(value) {
				if(value) {
					this.showPublishValidationWarningModal();
				}
				else {
					this.hidePublishValidationWarningModal();
				}
			}
		}
	},

	methods: {
		...mapMutations([
			'showPublishValidationWarningModal',
			'hidePublishValidationWarningModal',
		]),

		/**
		 * Called when the user clicks the X icon, clicks away from the modal, or presses ESC
		*/
		handleClose() {
			this.cancel();
		},

		/**
		 * Closes the modal
		*/
		cancel() {
			this.hidePublishValidationWarningModal();
		},

		/**
		 * Open the error listing in the sidebar and close the warning modal
		*/
		openErrors() {
			this.cancel();
			this.$bus.$emit('sidebar:openErrors');
		}
	}
};
</script>
