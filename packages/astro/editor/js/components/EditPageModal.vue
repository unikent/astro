<template>
	<el-dialog title="Edit page settings" :visible.sync="visible" :modal-append-to-body="false">
		<el-form :model="editForm">
			<el-form-item label="Page title">
				<el-input name="title" v-model="editForm.title" auto-complete="off"></el-input>
			</el-form-item>
			<el-form-item label="Page Slug" v-if="editForm.editSlug">
				<el-input name="slug" v-model="editForm.slug" auto-complete="off"></el-input>
			</el-form-item>
			<div class="el-alert el-alert--error" v-if="editForm.errorMessage">
				<i class="el-alert__icon el-icon-circle-cross is-big"></i>
				<div class="el-alert__content">
					<span class="el-alert__title is-bold">{{editForm.errorMessage}}</span>
					<ul v-for="error in editForm.errorDetails" :key="error.id">
						<li class="el-alert__description">{{error}}</li>
					</ul>
				</div>
			</div>

		</el-form>
		<span slot="footer" class="dialog-footer">
	<el-button @click="visible = false">Cancel</el-button>
	<el-button type="primary" @click="saveEdit">Save</el-button>
</span>
	</el-dialog>
</template>

<script>
	import { mapMutations, mapState, mapActions, mapGetters } from 'vuex';

	export default {

		name: 'edit-page-modal',

		data() {
			return {
				editForm: {
					title: '',
					id: 0,
					slug: '',
					editSlug: false,
					errorMessage: '',
					errorDetails: []
				}
			};
		},

		computed: {
			...mapState('site', {
				pages: state => state.pages,
				editPageModal: state => state.editPageModal
			}),

			...mapGetters([
				'getPageTitle',
				'getPageSlug'
			]),

			...mapMutations([
				'savePageMeta'
			]),

			visible: {
				get() {
					const visible = this.editPageModal.visible;

					if(visible) {
						this.editForm.title = this.editPageModal.title;
						this.editForm.slug = this.editPageModal.slug;
						this.editForm.id = this.editPageModal.id;
						this.editForm.editSlug = this.editPageModal.editSlug;
						this.editForm.errorMessage = '';
						this.editForm.editDetails = [];
					}

					return visible;
				},
				set(show) {
					if(!show) {
						this.hideEditPageModal();
					}
				}
			}
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
				this.updatePageMeta(this.editForm)
					.then(() => {
						this.hideEditPageModal();
					})
					.catch((error) => {
						this.editForm.errorMessage = error.data.errors[0].message;
						this.editForm.errorDetails = error.data.errors[0].details;
					});
			}
		}
	};
</script>