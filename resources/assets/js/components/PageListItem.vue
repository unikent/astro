<template>
<li :class="root ? '' : 'page-list__item'">

	<!-- Expand tree icon -->
	<div v-if="this.getDepth !== 0 && hasChildren" class="chevron" :class="{'open': open }" @click="toggle">
		<Icon :name="open ? 'minus' : 'plus'" width="10" height="10" />
	</div>
	<!-- End expand tree icon -->

	<div style="display: flex; align-items: center;">
		<div class="inner" style="flex: 1 0 auto;">
			{{ page.slug || page.path }}

			<!-- Page options dropdown -->
			<el-dropdown trigger="click" @command="handleCommand" size="small" class="options" style="float: right;">
				<el-button type="text" class="option-button">
					<i class="el-icon-more el-icon--right side-caret"></i>
				</el-button>

				<el-dropdown-menu slot="dropdown">
					<el-dropdown-item command="edit">Edit</el-dropdown-item>
					<el-dropdown-item command="move">Move</el-dropdown-item>
					<el-dropdown-item v-show="isParent && this.getDepth <= 2" command="openModal">Add page</el-dropdown-item>
					<el-dropdown-item v-show="this.getDepth !== 0" command="openRename">Rename</el-dropdown-item>
					<el-dropdown-item v-show="this.getDepth !== 0" command="remove" divided>Delete</el-dropdown-item>
				</el-dropdown-menu>
			</el-dropdown>
			<!-- End page options dropdown -->
		</div>
	</div>

	<!-- Create Page Modal -->
	<div id="create-form">
		<el-dialog title="Create Page" v-if="createFormVisible" :visible.sync="createFormVisible" :modal-append-to-body=false >
			<el-form :model="createForm">
				<el-form-item label="Page title">
					<el-input name="title" v-model="createForm.title" auto-complete="off"></el-input>
				</el-form-item>
				<el-select name="layout_name" v-model="createForm.layout_name" placeholder="Select">
					<el-option
							v-for="item in layouts"
							:key="item"
							:label="item"
							:value="item">
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
			<el-button @click="createFormVisible = false">Cancel</el-button>
			<el-button type="primary" @click="addChild">Confirm</el-button>
		</span>
		</el-dialog>
	</div>
	<!-- End create page modal -->

	<!-- Rename page modal -->
	<el-dialog title="Rename Page" :visible.sync="renameFormVisible" :modal-append-to-body=false>
		<el-form :model="page">
			<el-form-item label="Page title">
				<el-input name=slug v-model="page.slug" auto-complete="off"></el-input>
			</el-form-item>
		</el-form>
		<span slot="footer" class="dialog-footer">
			<el-button @click="renameFormVisible = false">Cancel</el-button>
			<el-button type="primary" @click="saveEdit">Confirm</el-button>
		</span>
	</el-dialog>
	<!-- End rename page modal-->

	<ul class="page-list__children" :class="{'collapsed': !open && page.depth !== 0}" v-if="isParent">
		<page-list-item
			v-for="child in page.children"
			:page="child"
			:key="child.path"
			:layouts="layouts"
		/>
	</ul>
</li>
</template>

<script>
import Vue from 'vue';
import { mapActions } from 'vuex';
import Icon from 'components/Icon';

/* global clearTimeout, setTimeout */
var timer = null

export default {
	name: 'page-list-item',

	props: ['page', 'site', 'editing', 'layouts', 'root'],

	components: {
		Icon
	},

	data() {
		return {
			open: false,
			createFormVisible: false,
			renameFormVisible: false,
			createForm: {
				title: 'Unnamed Page',
				layout_name: 'kent-homepage',
				layout_version: 1,
				route: {
					slug: 'testing',
					parent_id: this.page.id
				},
				options: {}
			}
		}
	},

	computed: {
		isParent() {
			return this.page.children.length;
		},

		hasChildren() {
			return this.page.children.length;
		},

		getDepth() {
			return this.page.depth;
		},

		leftPadding() {
			return {
				paddingLeft: (this.getDepth * 10) + (this.isParent ? 10 : 0) + 'px'
			}
		}
	},

	methods: {
		handleClose(done) {
			this.$confirm('Are you sure to close this dialog?')
				.then(() => {
					done();
				})
				.catch(() => {});
		},

		addDummyElement() {
			if(!this.hasChildren) {
				this.fakePage(this.page)
				this.open = true
			}
		},

		clearDummyElement() {
			if(this.hasChildren) {
				this.removeFakePage()
			}
		},

		toggle() {
			if(this.isParent) {
				this.open = !this.open;
			}
		},

		waitAndEdit() {
			clearTimeout(timer);
			timer = setTimeout(this.edit, 400);
		},

		edit() {
			clearTimeout(timer);
			this.$router.push(`/site/${this.site}/page/${this.page.id}`);
			this.$store.commit('changePage', this.page.title);
		},

		rename() {
			clearTimeout(timer);

			this.$bus.$emit('rename-page', this.page.id);

			var nameWidth = Math.max(50, this.$refs.name.offsetWidth);

			/* TODO: Move into component to avoid refs? */
			Vue.nextTick(() => {
				var input = this.$refs.input;
				input.style.width = (nameWidth + 4) + 'px';
				input.focus();
			});
		},

		saveEdit() {
			console.log(this.page)
			this.renameFormVisible = false
				this.updatePage({
					title: this.page.title,
					id: this.page.id,
					page_id: this.page.page_id,
					layout_name: this.layout_name,
					layout_version: this.layout_version,
					route: {
						slug: this.page.slug,
						parent_id: this.page.parent_id
					}
				});
		},

		remove() {
			this.$confirm(`Are you sure you want to delete "${this.page.title}"?`, 'Warning', {
				confirmButtonText: 'OK',
				cancelButtonText: 'Cancel',
				type: 'warning'
			}).then(() => {
				this.deletePage({
					id:this.page.page_id
				})
			});
		},

		addChild() {
			this.createPage(this.createForm);
			this.createFormVisible = false;
		},

		openModal() {
			this.createFormVisible = true
		},

		openRename() {
			this.renameFormVisible = true
		},

		handleCommand(command) {
			if(this[command]) {
				this[command]();
			}
		},

		move(parent) {
			let parent_id = parent._props.page.id;
			this.page.parent_id = parent_id;
			//this.saveEdit();
		},

		...mapActions({
			deletePage: 'site/deletePage',
			createPage: 'site/createPage',
			updatePage: 'site/updatePage',
			fakePage: 'site/fakePage',
			removeFakePage: 'site/removeFakePage'
		})
	}
};
</script>