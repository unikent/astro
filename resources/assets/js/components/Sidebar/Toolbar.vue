<template>
<div class="toolbar">

	<el-tooltip class="item" effect="dark" content="Switch preview mode" placement="top">
		<el-select placeholder="view" v-model="currentView" class="switch-view">
			<el-option v-for="(view, key) in views" :label="view.label" :value="key" :key="view.label">
				<div class="view-icon">
					<Icon :name="view.icon" aria-hidden="true" width="20" height="20" />
				</div>
				<span class="view-label">{{ view.label }}</span>
			</el-option>
		</el-select>
	</el-tooltip>

	<el-button-group class="undo-redo">
		<el-button :disabled="!undoRedo.canUndo" @click="undo">
			<Icon name="undo" aria-hidden="true" width="14" height="14" class="ico" />
		</el-button>
		<el-button :disabled="!undoRedo.canUndo" @click="redo">
			<Icon name="redo" aria-hidden="true" width="14" height="14" class="ico" />
		</el-button>
	</el-button-group>

	<el-button class="save-button" @click="savePage">Save</el-button>

	<el-button class="publish-button" @click="publishPage">Publish</el-button>

	<div
		class="page-status"
		:class="{'page-status--is-published' : page.is_published}"
	>{{ page.is_published ? 'published' : 'draft'}}</div>

</div>
</template>


<script>
import { mapState } from 'vuex';
import Icon from 'components/Icon';
import { undoStackInstance } from 'plugins/undo-redo';

export default {
	name: 'toolbar',

	components: {
		Icon
	},

	computed: {
		...mapState([
			'preview',
			'displayIframeOverlay',
			'undoRedo'
		]),

		...mapState({
			page: state => state.page.pageData,
			pageLoaded: state => state.page.loaded
		})
	},

	created() {
		this.views = {
			desktop: {
				icon: 'desktop',
				label: 'Desktop',
				width: '100%',
				height: '100vh'
			},
			tablet: {
				icon: 'tablet',
				label: 'Tablet',
				width: '768px',
				height: '1024px'
			},
			mobile: {
				icon: 'mobile',
				label: 'Mobile',
				width: '320px',
				height: '568px'
			}
		};
	},

	data() {
		return {
			currentView: 'desktop'
		};
	},

	methods: {
		savePage() {
			this.$api
				.put(`page/${this.$route.params.site_id}`, this.page)
				.then(() => {
					this.$snackbar.open({
						message: 'Successfully saved page'
					})
				})
				.catch(() => {});
		},

		// TODO - this is just the same as save at the moment
		publishPage() {
			this.$api
				.put(`page/${this.$route.params.site_id}`, this.page)
				.then(() => {
					this.$snackbar.open({
						message: 'Successfully saved page'
					})
				})
				.catch(() => {});
		},

		undo() {
			return undoStackInstance.undo();
		},

		redo() {
			return undoStackInstance.redo();
		}
	}

};
</script>
