<template>
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
			'publishModal'
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
		}
	},

	methods: {
		...mapMutations([
			'showPublishModal',
			'hidePublishModal'
		]),

		publishPage() {
			this.$api
				.post('pages/' + this.$route.params.page_id + '/publish', this.page)
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

		handleClose() {
			this.hidePublishModal();
			this.form.message = '';
		}
	}
};
</script>