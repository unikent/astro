<template>
<el-dialog title="Edit page settings" v-model="visible" :modal-append-to-body="false">
	<el-form :model="editForm">
		<el-form-item label="Page title">
			<el-input name="title" v-model="editForm.title" auto-complete="off"></el-input>
		</el-form-item>
		<el-form-item label="URL">
			<el-input name="slug" v-model="editForm.route.slug" auto-complete="off"><template slot="prepend">https://www.kent.ac.uk/my-site/</template></el-input>
		</el-form-item>
	</el-form>
	<span slot="footer" class="dialog-footer">
	<el-button @click="visible = false">Cancel</el-button>
	<el-button type="primary" @click="saveEdit">Save</el-button>
</span>
</el-dialog>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import { Definition } from 'classes/helpers';

export default {

	name: 'edit-page-modal',

	data() {
		return {
			editForm: {
				title: '',
				id: 1,
				page_id: 1,
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

		visible: {
			get() {
				this.editForm.title = this.editPageModal.parentId;
				return this.editPageModal.visible;
			},
			set(show) {
				if(!show) {
					this.hideEditPageModal();
				}
			}
		},
	},

	created() {
		//this.fetchSite();
	},

	methods: {
		...mapActions({
			//fetchSite: 'site/fetchSite',
			//createPage: 'site/createPage',
			hideEditPageModal: 'site/hideEditPageModal'
		}),

		saveEdit() {
			// TODO: when endpoint is ready, update this
			// this.updatePage({
			// 	title: this.currentPage.title,
			// 	id: this.currentPage.id,
			// 	page_id: this.currentPage.page_id,
			// 	layout_name: this.layout_name,
			// 	layout_version: this.layout_version,
			// 	route: {
			// 		slug: this.currentPage.slug,
			// 		parent_id: this.currentPage.parent_id
			// 	}
			// });
		}
	}
};
</script>
