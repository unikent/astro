<template>
<el-dialog title="Edit page settings" v-model="visible" :modal-append-to-body="false">
	<el-form :model="editForm">
		<el-form-item label="Page title">
			<el-input name="title" v-model="editForm.title" auto-complete="off"></el-input>
		</el-form-item>
		<input type="hidden" name="id" :value="editForm.id">
	</el-form>
	<span slot="footer" class="dialog-footer">
	<el-button @click="visible = false">Cancel</el-button>
	<el-button type="primary" @click="saveEdit">Save</el-button>
</span>
</el-dialog>
</template>

<script>
import { mapMutations, mapState, mapActions } from 'vuex';
import { Definition } from 'classes/helpers';

export default {

	name: 'edit-page-modal',

	data() {
		return {
			editForm: {
				title: '',
				id: 1,
				route: {
					slug: '',
					parent_id: 1
				}
			}
		};
	},

	computed: {
		...mapState('site', {
			pages: state => state.pages,
			editPageModal: state => state.editPageModal
		}),

		...mapState({
			pageData: state => state.page.pageData
		}),

		...mapMutations([
			'savePageMeta'
		]),

		visible: {
			get() {
				this.editForm.title = this.editPageModal.title;
				this.editForm.slug = this.editPageModal.slug;
				this.editForm.id = this.editPageModal.id;
				this.editForm.page_id = this.editPageModal.page_id;
				return this.editPageModal.visible;
			},
			set(show) {
				if(!show) {
					this.hideEditPageModal();
				}
			}
		}
	},

	created() {

	},

	methods: {
		...mapActions({
			fetchPageMeta: 'site/fetchPageMeta',
			hideEditPageModal: 'site/hideEditPageModal',
			updatePageMeta: 'site/updatePageMeta'
		}),

		/**
		updates the data in the db
		hides the modal
		and updates the store to the new page meta
		*/
		saveEdit() {
			this.updatePageMeta(this.editForm);
			this.hideEditPageModal();
			this.$store.commit('savePageMeta', this.editForm);
		}
	}
};
</script>
