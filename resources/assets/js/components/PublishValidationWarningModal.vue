/**
PublishValidationWarningModal.Vue

Shows a warning message when there are validation errors, and the user tries to publish a page.

*/
<template>
<el-dialog
	title="Publish"
	v-model="publishValidationWarningModalVisible"
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

		<el-collapse class="publish-modal__errors">
			<el-collapse-item title="Issue list" name="1">
				<p>Here are the blocks on the page that have wrong or missing fields:</p>
				<template v-if="blocks" v-for="region in blocks">
					<ul class="validation-errors" v-if="region">
						<template v-for="block in region">
							<template v-if="errors.indexOf(block.id) !== -1">
								<li>
									<el-tag type="danger">{{ block.definition_name }}</el-tag>
								</li>
							</template>
						</template>
					</ul>
				</template>
			</el-collapse-item>
		</el-collapse>
		<div class="publish-modal__buttons">
			<span slot="footer" class="dialog-footer">
				<el-button type="primary" @click="openErrors">Close this message and open the error sidebar</el-button>
			</span>
		</div>
	</div>
</el-dialog>
</template>

<script>
import { mapState, mapMutations, mapGetters } from 'vuex';


export default {
	name: 'publish-validation-warning-modal',

	computed: {
		...mapState([
			'publishValidationWarningModal'
		]),

		...mapGetters([
			'getInvalidBlocks',
			'getBlocks',
			'getCurrentRegion'
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
		},

		errors() {
			return this.getInvalidBlocks();
		},

		blocks() {
			return this.getBlocks();
		},

		region() {
			return this.getCurrentRegion();
		}
	},

	methods: {
		...mapMutations([
			'showPublishValidationWarningModal',
			'hidePublishValidationWarningModal',
			'updateMenuActive',
			'updateMenuFlash'
		]),

		/**
		called when the user clicks the X icon, clicks away from the modal, or presses ESC
		*/
		handleClose() {
			this.cancel();
		},

		/**
		cancel the modal
		*/
		cancel() {
			this.hidePublishValidationWarningModal();
		},

		/**
		open the error listing in the sidebar and close the warning modal
		*/
		openErrors() {
			this.cancel();
			this.updateMenuActive('errors');
			this.updateMenuFlash('errors');
			setTimeout(() => {
				this.updateMenuFlash('');
			}, 1000);
		}
	}
};
</script>
