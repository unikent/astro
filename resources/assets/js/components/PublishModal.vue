<template>

<div>
<el-dialog
	title="Publish"
	v-model="publishModalVisible"
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


<el-dialog
	title="Published"
	v-model="publishedModalVisible"
	:modal-append-to-body="true"
	:before-close="handleClose"
>
	<el-form :model="form">
		Published!
		<a href="">My published page</a>
	</el-form>
	<span slot="footer" class="dialog-footer">
		<el-button @click="cancelPublish">Close</el-button>
	</span>
</el-dialog>
</div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';

export default {
	name: 'publish-modal',

	data() {
		return {
			form: {
				message: ''
			}
		}
	},

	computed: {
		...mapState([
			'publishModal',
			'publishedModal'
		]),

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

		publishedModalVisible: {
			get() {
				return this.publishedModal.visible;
			},
			set(value) {
				this.showPublishedModal();
			}
		}
	},

	methods: {
		...mapMutations([
			'showPublishModal',
			'hidePublishModal',
			'showPublishedModal',
			'hidePublishedModal'
		]),

		publishPage() {
			this.$api
				.post('pages/' + this.$route.params.page_id + '/publish', this.page)
				.then(() => {
					this.hidePublishModal();

					this.$alert('This is a message', 'Published', {
          confirmButtonText: 'OK',
		  message: `You have published your page to http://www.kent.ac.uk/my-site/my-page`
        });
					this.form.message = '';
				})
				.catch(() => {});
		},

		cancelPublish() {
			this.hidePublishModal();
			this.hidePublishedModal();
			this.form.message = '';
		},

		handleClose() {
			this.hidePublishModal();
			this.hidePublishedModal();
			this.form.message = '';
		}
	}
};
</script>
