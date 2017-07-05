<template>
<div>
	<li class="item"  @mouseover="addDummyElement"  @mouseleave="clearDummyElement" @click="clearDummyElement" v-if="page.is_active">

		<!-- Expand tree icon -->
		<svg v-if="this.getDepth !== 0 && hasChildren" class="chevron" :class="{'open': open}" @click="toggle" viewBox="0 0 100 100">
			<path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" fill="#444" class="arrow" transform="translate(100, 100) rotate(180)"></path>
		</svg>
		<!-- End expand tree icon -->

		{{ page.slug || page.path }}

		<!-- Page options dropdown -->
		<el-dropdown trigger="click" @command="handleCommand" size="small" class="options">
			<el-button class="option-button">
				<div class="cog">
					<svg viewBox="0 0 128 128">
						<path d="m55.5 0l-2.8 19.7c-4.2 1.1-8.2 2.8-12 5l-15.9-12-12.1 12.1 12 15.9c-2.2 3.8-3.9 7.8-4.9 12l-19.8 2.8v17l19.7 2.8c1.1 4.2 2.8 8.2 5 12l-12 15.7 12.1 12 15.9-12c3.8 2 7.8 4 12 5l2.8 20h17l2.8-20c4.2-1 8.2-3 12-5l15.7 12 12-12-12-15.7c2-3.8 4-7.8 5-12l20-2.8v-17l-20-2.8c-1-4.2-3-8.2-5-12l12-15.9-12-12.1-15.7 12c-3.8-2.2-7.8-3.9-12-4.9l-2.8-19.8h-17zm8.5 41a23 23 0 0 1 23 23 23 23 0 0 1 -23 23 23 23 0 0 1 -23 -23 23 23 0 0 1 23 -23z" fill="#8e8e8e"/>
					</svg>
				</div>
				<i class="el-icon-caret-bottom el-icon--right side-caret"></i>
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

		<ul :style="leftPadding" v-dragula="page.children" drake="first" :class="{'collapsed': !open && page.depth !== 0}" v-if="isParent">
			<page-list-item
				v-for="child in page.children"
				:page="child"
				:key="child.path"
				:layouts="layouts"
			/>
		</ul>
	</li>
</div>
</template>

<script>
import Vue from 'vue';
import { mapActions } from 'vuex'
/* global clearTimeout, setTimeout */
var timer = null

export default {
	name: 'page-list-item',

	props: ['page', 'site', 'editing', 'layouts'],

	data() {
		return {
			open: false,
			createFormVisible: false,
			renameFormVisible: false,
			createForm: {
				title: 'Unnamed Page',
				layout_name: 'default',
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
			return this.page.children
		},

		hasChildren() {
			let children = false
			this.page.children.forEach( child => {
				if (child.is_active){
					children = true
				}
			});
			return children
		},

		getDepth() {
			return this.page.depth;
		},

		leftPadding() {
			return {
				paddingLeft: (this.getDepth * 20) + (this.isParent ? 10 : 0) + 'px'
			}
		}
	},

	methods: {
		handleClose(done) {
			this.$confirm('Are you sure to close this dialog?')
				.then(_ => {
					done();
				})
				.catch(_ => {});
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