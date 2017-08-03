<template>
<div>
	<back-bar :title="title" />

	<div v-if="pages" v-for="page in pages" class="page-list">
		<page-list-item
			:key="page.id"
			:page="page"
			:flatten="true"
			:open-modal="openModal"
			path="0"
			:depth="0"
		/>
	</div>

	<!-- TODO: move out into a container fo all modal windows -->
	<div id="create-form">
		<el-dialog title="Create Page" v-if="createFormVisible" :visible.sync="createFormVisible" :modal-append-to-body=false >
			<el-form :model="createForm">
				<el-form-item label="Page title">
					<el-input name="title" v-model="createForm.title" auto-complete="off"></el-input>
				</el-form-item>
				<el-select name="layout_name" v-model="createForm.layout.name" placeholder="Select">
					<el-option
							v-for="item in layouts"
							:key="item"
							:label="item"
							:value="item">
					</el-option>
				</el-select>
				<el-form-item label="Layout Version">
					<el-input name="layout_version" v-model="createForm.layout.version" auto-complete="off"></el-input>
				</el-form-item>
				<el-form-item label="slug">
					<el-input name="slug" v-model="createForm.slug" auto-complete="off"></el-input>
				</el-form-item>
			</el-form>
			<span slot="footer" class="dialog-footer">
			<el-button @click="createFormVisible = false">Cancel</el-button>
			<el-button type="primary" @click="addChild">Confirm</el-button>
		</span>
		</el-dialog>
	</div>

	<div class="b-bottom-bar">
		<el-button class="u-mla" @click="() => { this.openModal(this.$children[2]) }">+ Add Page</el-button>
	</div>
</div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import Draggable from 'vuedraggable';

import PageListItem from './PageListItem';
import BackBar from './BackBar';

export default {

	name: 'page-list',

	props: ['title'],

	components: {
		PageListItem,
		BackBar,
		Draggable
	},

	data() {
		return {
			loading: true,
			createFormVisible: false,
			settingsFormVisible: false,
			layouts: [],
			createForm: {
				title: 'Unnamed Page',
				layout: {
                    name: 'kent-homepage',
                    version: 1,
                },
				slug: 'testing',
				parent_id: 1,
				options: {}
			}
		};
	},

	computed: {
		...mapState('site', {
			pages: state => state.pages
		})
	},

	methods: {
		...mapActions({
			fetchSite: 'site/fetchSite',
			createPage: 'site/createPage'
		}),

		openModal(pageItem) {
			this.currentPage = pageItem;
			this.createFormVisible = true;
			this.createForm.parent_id = pageItem.page.draft.page_content_id;
		},

		addChild() {
			this.createPage(this.createForm);
			this.createFormVisible = false;
			this.currentPage.openPage();
			this.currentPage = null;
		},

		openRename() {
			this.renameFormVisible = true;
		},

		saveEdit() {
			this.renameFormVisible = false;
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
	},

	created() {
		this.fetchSite();
		this.$bus.$on('rename-page', (id) => this.edit = id);
	}
};
</script>