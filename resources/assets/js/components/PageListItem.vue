<template>
<div v-if="page.revision"
	:class="{
		'page-list__item': !root,
		'page-list__root': root && hasChildren && flatten,
		'page-list__first': root && !(hasChildren && flatten),
		'add-gutter-bottom': depth > 2
	}"
>
	<!-- Expand tree icon -->
	<div v-if="!root && hasChildren" class="page-list__item__expand" @click="toggle">
		<icon v-show="open" name="minus" :width="10" :height="10" />
		<icon v-show="!open" name="plus" :width="10" :height="10" />
	</div>
	<!-- End expand tree icon -->

	<div
		class="page-list__title"
		:class="{ 'page-list__title--selected': pageData.id === this.page.id }"
	>
		<span class="page-list__item__drag-handle">
			<icon v-if="!root && canUser('page.move')" name="arrow" :width="14" :height="14" />
		</span>

		<el-tooltip
			v-if="statuses[page.status]"
			:content="statuses[page.status].name"
		>
			<div
				class="page-list__status"
				:class="{[`page-list__status--${page.status}`]: true }"
			/>
		</el-tooltip>

		<span ref="name" class="page-list__text" @click="edit">
			{{ page.path === '/' ? 'Home page' : (page.title || page.slug) }}
		</span>

		<!-- Page options dropdown -->
		<el-dropdown trigger="click" @command="handleCommand" size="small" class="page-list__options">
			<el-button type="text" style="padding: 0;">
				<icon name="more-alt" :width="14" :height="14" class="page-list__option-icon" />
			</el-button>

			<el-dropdown-menu slot="dropdown">
				<el-dropdown-item
					command="openEditModal"
					v-if="canUser('page.edit')"
					:disabled="pageHasLayoutErrors"
				>
					Edit page settings
				</el-dropdown-item>

				<el-dropdown-item
					v-show="!root"
					:disabled="depth > 2 || pageHasLayoutErrors"
					command="openModal"
					v-if="canUser('page.add')"
				>
					Add subpage
				</el-dropdown-item>

				<!-- TODO: figure out nicest way of disallowing pages from being published if parent is new -->
				<el-dropdown-item
					command="publish"
					v-if="canUser('page.publish')"
					:disabled="page.status === 'published' || parentStatus === 'new' || pageHasLayoutErrors"
				>
					Publish
				</el-dropdown-item>

				<el-dropdown-item
					command="unpublish"
					v-if="canUser('page.unpublish')"
					:disabled="page.status === 'new'"
				>
					Unpublish
				</el-dropdown-item>

				<el-dropdown-item
					v-show="!root"
					command="remove"
					divided
					v-if="canUser('page.delete')"
				>
					Delete
				</el-dropdown-item>
			</el-dropdown-menu>
		</el-dropdown>
		<!-- End page options dropdown -->
	</div>

	<draggable
		v-model="children"
		:options="{
			group: 'pages',
			chosenClass: 'page-list__item--dragging',
			handle: '.page-list__item__drag-handle'
		}"
		@end="handleDragEnd"
		class="add-gutter"
		v-if="depth <= 2"
		:move="handleMove"
	>
		<template v-if="hasChildren">
			<page-list-item
				:class="{'page-list__item--collapsed': !open && depth !== 0}"
				v-for="(child, index) in page.children"
				:page="child"
				:site="site"
				:is-draggable="true"
				:key="child.id"
				:open-modal="openModal"
				:open-edit-modal="openEditModal"
				:path="`${path}.${index}`"
				:depth="depth + 1"
				:parent-status="page.status"
			/>
		</template>
	</draggable>
</div>
</template>

<script>
import { mapState, mapActions, mapMutations, mapGetters } from 'vuex';
import Draggable from 'vuedraggable';
import Icon from 'components/Icon';
import promptToSave from '../mixins/promptToSave';

export default {
	name: 'page-list-item',

	props: [
		'page',
		'on-add',
		'flatten',
		'open-modal',
		'open-edit-modal',
		'path',
		'depth',
		'parent-status'
	],

	components: {
		Icon,
		Draggable
	},

	mixins:[promptToSave],

	created() {
		this.statuses = {
			'new': {
				name: 'Unpublished',
				type: 'primary'
			},
			'draft': {
				name: 'Draft',
				type: 'warning'
			},
			'published': {
				name: 'Published',
				type: 'success'
			}
		};
	},

	data() {
		return {
			open: false
		}
	},

	computed: {

		...mapState('site', {
			site: state => state.site
		}),

		...mapState({
			pageData: state => state.page.pageData,
			layoutErrors: state => state.page.layoutErrors
		}),

		...mapGetters([
			'canUser'
		]),

		root() {
			return this.depth === 0;
		},

		hasChildren() {
			return this.page.children.length;
		},

		leftPadding() {
			return {
				paddingLeft: (this.depth * 10) + (this.hasChildren ? 10 : 0) + 'px'
			}
		},

		children: {
			get() {
				return this.page.children;
			},
			// don't set directly (use vuex instead)
			set() {}
		},

		/**
		 * Determines if the page this list item represents is being edited and has layout errors.
		 */
		pageHasLayoutErrors() {
			return this.pageData.id === this.page.id && this.layoutErrors.length !== 0;
		}
	},

	methods: {

		...mapMutations([
			'setLoaded',
			'updateMenuActive',
			'showUnpublishModal',
			'showPublishModal'
		]),

		...mapActions({
			movePage: 'site/movePage',
			deletePage: 'site/deletePage',
			updatePage: 'site/updatePage',
			handleSavePage: 'handleSavePage'
		}),

		handleDragEnd() {
			if(this.move) {
				this.movePage(this.move);
			}

			this.move = null;
		},

		handleMove(e) {
			const
				draggingPath = e.dragged.__vue__.path,
				parentPath = e.to.__vue__.$parent.path,
				// don't allow dragging into own or child's "list"
				allowDrag = !parentPath.startsWith(draggingPath),
				paths = {
					from: draggingPath,
					to: `${parentPath}.${e.draggedContext.futureIndex}`
				};

			if(allowDrag) {
				this.move = {
					fromPath: paths.from,
					toPath: paths.to
				};
			}

			// returning false cancels drag action
			return allowDrag;
		},

		handleClose(done) {
			this.$confirm('Are you sure to close this dialog?')
				.then(() => {
					done();
				})
				.catch(() => {});
		},

		openPage() {
			this.open = true;
		},

		toggle() {
			if(this.hasChildren) {
				this.open = !this.open;
			}
		},

		edit() {
			const pageId = this.page.id;

			if(Number.parseInt(this.$route.params.page_id) !== pageId) {
				/* prompt to save any unsaved changes before we switch to the new page */
				this.promptToSave(() => {
					this.setLoaded(false);
					this.$router.push(`/site/${this.site}/page/${pageId}`);
				});
			}
			else {
				this.$snackbar.open({
					message: `
						You are currently editing this page.
					`
				});
			}
		},

		remove() {
			this.$confirm(`Are you sure you want to delete "${this.page.revision.title}"?`, 'Warning', {
				confirmButtonText: 'OK',
				cancelButtonText: 'Cancel',
				type: 'warning'
			}).then(() => {
				this.deletePage({
					id:this.page.id
				})
			});
		},

		publish() {
			this.showPublishModal(this.path);
		},

		unpublish() {
			this.showUnpublishModal(this.path);
		},

		handleCommand(command) {
			if(this[command]) {
				this[command](this.page);
			}
		}

	}
};
</script>
