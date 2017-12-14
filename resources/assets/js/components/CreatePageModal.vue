<template>
<el-dialog title="Create Page" :visible.sync="visible" :modal-append-to-body="false">
	<el-form :model="createForm">
		<el-form-item label="Page title">
			<el-input name="title" v-model="title" auto-complete="off"></el-input>
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
				v-model="createForm.slug"
				auto-complete="off" @change="setUserEditingSlug"></el-input>
		</el-form-item>
	</el-form>
	<span slot="footer" class="dialog-footer">
	<el-button @click="visible = false">Cancel</el-button>
	<el-button type="primary" @click="addChild" :disabled="disableSubmit">Confirm</el-button>
</span>
</el-dialog>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex';
import { Definition } from 'classes/helpers';
import { slugify } from 'underscore.string';

export default {

	name: 'create-page-modal',

	data() {
		return {
			userEditingSlug: false,
			createForm: {
				title: 'New Page',
				layout: '',
				slug: 'new-page',
				parent_id: null,
			}
		};
	},

	computed: {
		...mapState('site', {
			pages: state => state.pages,
			pageModal: state => state.pageModal,
			allLayouts: state => state.layouts
		}),

		...mapGetters([
			'siteDefinition'
		]),

		title: {
			set(val) {
				this.createForm.title = val;
				if(!this.userEditingSlug) {
					this.createForm.slug = this.suggestedSlug;
				}
			},
			get() {
				return this.createForm.title;
			}
		},

		layouts() {
			if(this.siteDefinition){
				if(this.siteDefinition.availableLayouts !== void 0) {
					let available = {};
					this.siteDefinition.availableLayouts.forEach((definitionID) => {
						if(this.allLayouts[definitionID] !== void 0) {
							available[definitionID] = this.allLayouts[definitionID];
						}
					}, this);
					return available;
				}
				else {
					return this.allLayouts;
				}
			}
			else {
				return [];
			}
		},

		disableSubmit() {
			return this.createForm.layout === ''	 ||
				this.createForm.title === '' ||
				this.createForm.slug === '';
		},

		visible: {
			get() {
				/* eslint-disable camelcase */
				this.createForm.parent_id = this.pageModal.parentId;
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
			this.userEditingSlug = true;
		},

		resetForm() {
			this.userEditingSlug = false;
			this.createForm = {
				title: 'New page',
				layout: '',
				slug: 'new-page',
				parent_id: null
			}
		}
	}
};
</script>
