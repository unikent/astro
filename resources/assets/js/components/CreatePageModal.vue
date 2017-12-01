<template>
<el-dialog title="Create Page" :visible.sync="visible" :modal-append-to-body="false">
	<el-form :model="createForm">
		<el-form-item label="Page title">
			<el-input name="title" v-model="createForm.title" auto-complete="off"></el-input>
		</el-form-item>
		<el-form-item label="Layout">
			<el-select
				name="layout"
				class="w100"
				placeholder="Select"
				v-model="createForm.layout"
			>
				<el-option
						v-for="(layoutDefinition, layoutID) in layouts"
						:label="layoutDefinition.label + ' (v' + layoutDefinition.version + ')'"
						:value="layoutID"
						:key="layoutID"
				>
				</el-option>
			</el-select>
		</el-form-item>
		<el-form-item label="slug">
			<el-input
				name="slug"
				v-model="createForm.route.slug"
				v-bind:placeholder="suggestedSlug"
				auto-complete="off" @focus="setUserEditingSlug"></el-input>
		</el-form-item>
	</el-form>
	<span slot="footer" class="dialog-footer">
	<el-button @click="visible = false">Cancel</el-button>
	<el-button type="primary" @click="addChild" :disabled="disableSubmit">Confirm</el-button>
</span>
</el-dialog>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import { Definition } from 'classes/helpers';
import { slugify } from 'underscore.string';

export default {

	name: 'create-page-modal',

	data() {
		return {
			createForm: {
				title: 'New page',
				layout: '',
				route: {
					slug: '',
					parent_id: 1
				},
				blocks: {},
				options: {}
			},
			userEditingSlug: false
		};
	},

	computed: {
		...mapState('site', {
			pages: state => state.pages,
			pageModal: state => state.pageModal,
			layouts: state => state.layouts
		}),

		disableSubmit() {
			return this.createForm.layout === ''	 ||
				this.createForm.title === '' ||
				this.createForm.route.slug === '';
		},

		visible: {
			get() {
				/* eslint-disable camelcase */
				this.createForm.route.parent_id = this.pageModal.parentId;
				/* eslint-enable camelcase */
				return this.pageModal.visible;
			},
			set(show) {
				if(!show) {
					this.hidePageModal();
				}
			}
		},

		suggestedSlug() {
			return slugify(this.createForm.title);
		}
	},

	created() {
		this.fetchSite();
	},

	methods: {
		...mapActions({
			fetchSite: 'site/fetchSite',
			createPage: 'site/createPage',
			hidePageModal: 'site/hidePageModal'
		}),

		addChild() {
			// if the user has not edited the slug then use the suggested slug
			if(!this.userEditingSlug) {
				this.createForm.route.slug = this.suggestedSlug;
			}
			this.createPage({
				...this.createForm,
				layout: {
					name: this.layouts[this.createForm.layout].name,
					version: this.layouts[this.createForm.layout].version
				}
			});
			this.resetForm();
			this.visible = false;
		},

		setUserEditingSlug() {
			if (this.userEditingSlug ===  false) {
				this.userEditingSlug = true;
				this.createForm.route.slug = this.suggestedSlug;
			}
		},

		resetForm() {
			this.createForm = {
				title: 'New page',
				layout: '',
				route: {
					slug: '',
					parent_id: 1
				},
				blocks: {},
				options: {}
			}
		}
	}
};
</script>
