<template>
	<el-dialog title="Copy page settings" :visible.sync="visible" :modal-append-to-body="false">
		<el-form :model="copyForm">
			<el-form-item label="Page title">
				<el-input name="title" v-model="copyForm.title" auto-complete="off"></el-input>
			</el-form-item>
			<el-form-item label="Page Slug">
				<el-input name="slug" v-model="copyForm.slug" auto-complete="off"></el-input>
			</el-form-item>
			<div class="el-alert el-alert--error" v-if="copyForm.errorMessage">
				<i class="el-alert__icon el-icon-circle-cross is-big"></i>
				<div class="el-alert__content">
					<span class="el-alert__title is-bold">{{copyForm.errorMessage}}</span>
					<ul v-for="error in copyForm.errorDetails" :key="error.id">
						<li class="el-alert__description">{{error}}</li>
					</ul>
				</div>
			</div>

		</el-form>
		<span slot="footer" class="dialog-footer">
	<el-button @click="visible = false">Cancel</el-button>
	<el-button type="primary" @click="saveCopy">Save</el-button>
</span>
	</el-dialog>
</template>

<script>
	import { mapMutations, mapState, mapActions, mapGetters } from 'vuex';

	export default {

		name: 'copy-page-modal',

		data() {
			return {
				copyForm: {
					title: '',
					id: 0,
					slug: '',
					errorMessage: '',
					errorDetails: []
				}
			};
		},

		computed: {
			...mapState('site', {
				pages: state => state.pages,
				copyPageModal: state => state.copyPageModal
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
					const visible = this.copyPageModal.visible;

					if(visible) {
						this.copyForm.title = this.copyPageModal.title;
						this.copyForm.slug = this.copyPageModal.slug;
						this.copyForm.id = this.copyPageModal.id;
						this.copyForm.errorMessage = '';
						this.copyForm.copyDetails = [];
					}

					return visible;
				},
				set(show) {
					if(!show) {
						this.hideCopyPageModal();
					}
				}
			}
		},

		methods: {
			...mapActions({
				fetchPageMeta: 'site/fetchPageMeta',
				hideCopyPageModal: 'site/hideCopyPageModal',
				copyPage: 'site/copyPage'
			}),

			/**
			 updates the data in the db
			 hides the modal
			 and updates the store to the new page meta
			 */
			saveCopy() {
				this.copyPage(this.copyForm)
					.then(() => {
						this.hideCopyPageModal();
					})
					.catch((error) => {
						this.copyForm.errorMessage = error.data.errors[0].message;
						this.copyForm.errorDetails = error.data.errors[0].details;
					});
			}
		}
	};
</script>