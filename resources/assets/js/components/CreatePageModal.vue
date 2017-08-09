<template>
<el-dialog title="Create Page" v-model="visible" :modal-append-to-body="false">
	<el-form :model="createForm">
		<el-form-item label="Page title">
			<el-input name="title" v-model="createForm.title" auto-complete="off"></el-input>
		</el-form-item>
		<el-select
			name="layout_name"
			v-model="createForm.layout_name"
			@change="getLayout"
		>
			<el-option
					v-for="item in layouts"
					:key="item.name"
					:label="item.label"
					:value="item.name">
			</el-option>
		</el-select>
		<el-form-item label="Layout Version">
			<el-input name="layout_version" v-model="createForm.layout_version" auto-complete="off"></el-input>
		</el-form-item>
		<el-form-item label="slug">
			<el-input name="slug" v-model="createForm.route.slug" auto-complete="off"></el-input>
		</el-form-item>
	</el-form>
	<span slot="footer" class="dialog-footer">
	<el-button @click="visible = false">Cancel</el-button>
	<el-button type="primary" @click="addChild">Confirm</el-button>
</span>
</el-dialog>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import { Definition } from 'classes/helpers';

export default {

	name: 'create-page-modal',

	data() {
		return {
			layouts: [
				{
					label: 'University homepage',
					name: 'kent-homepage'
				},
				{
					label: 'School homepage',
					name: 'school-homepage'
				},
				{
					label: 'School research page',
					name: 'school-research'
				},
				{
					label: 'School facilities page',
					name: 'school-facilities'
				},
				{
					label: 'School about page',
					name: 'school-about'
				}
			],
			createForm: {
				title: 'New page',
				layout_name: 'school-homepage',
				layout_version: 1,
				route: {
					slug: 'new-page',
					parent_id: 1
				},
				blocks: {},
				options: {}
			}
		};
	},

	computed: {
		...mapState('site', {
			pages: state => state.pages,
			pageModal: state => state.pageModal
		}),

		visible: {
			get() {
				this.createForm.route.parent_id = this.pageModal.parentId;
				return this.pageModal.visible;
			},
			set(show) {
				if(!show) {
					this.hidePageModal();
				}
			}
		},
	},

	created() {
		this.fetchSite();
		this.getLayout(this.createForm.layout_name);
	},

	methods: {
		...mapActions({
			fetchSite: 'site/fetchSite',
			createPage: 'site/createPage',
			hidePageModal: 'site/hidePageModal'
		}),

		addChild() {
			this.createPage(this.createForm);
			this.visible = false;
		},

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
		},

		getLayout(layoutName) {
			this.createForm.blocks = {};
			this.$api
				.get(`layout/${layoutName}/definition?include=region_definitions.block_definitions`)
				.then(({ data: json }) => {
					// go through our region definitions
					json.data.region_definitions.forEach((region) => {
						// if "default" blocks have been set and
						// we have some block definitions, try adding them
						if(region.default && region.block_definitions) {

							region.default.forEach((blockName) => {
								// see if this particular block definition exists
								const blockDefinition = region.block_definitions.find(
									(def) => def.name === blockName
								);

								if(blockDefinition) {
									if(!this.createForm.blocks[region.name]) {
										this.createForm.blocks[region.name] = [];
									}

									// add our empty block
									const length = this.createForm.blocks[region.name].push({
										definition_name: blockDefinition.name,
										definition_version: blockDefinition.version,
										fields: {}
									});

									// fill in the block's fields with their default values
									Definition.fillBlockFields(
										this.createForm.blocks[region.name][length - 1],
										blockDefinition
									)

									// add them to our create form (not directly
									// modifying the property so it can react to changes)
									this.createForm = {
										...this.createForm,
										blocks: this.createForm.blocks
									};
								}
							});
						}
					});
				});
		}
	}
};
</script>
