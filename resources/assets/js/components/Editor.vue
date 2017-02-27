<style lang="scss">
.el-select-dropdown__item {
	.view-icon {
		font-size: 20px;
		width: 22px;
		text-align: center;
		float: left;

		i {
			vertical-align: top;
		}
	}
	.view-label {
		float: right;
		color: #8492a6;
		font-size: 13px;
	}
	&.selected {
		.view-label {
			color: #fff;
		}
	}
}
</style>

<template>
<div class="editor">
	<div class="editor-body">
		<div class="editor-wrapper">
			<iframe class="editor-content" :style="dimensions" :src="getUrl" frameborder="0"></iframe>
			<footer class="b-bar">

				<el-tooltip class="item" effect="dark" content="Switch preview mode" placement="top" style="vertical-align: middle;">
					<el-select placeholder="view" v-model="currentView" style="width: 105px;">
						<el-option v-for="(view, key) in views" :label="view.label" :value="key">
							<div class="view-icon">
								<i class="fa fa-desktop" :class="{[`fa-${key}`] : true}" aria-hidden="true"></i>
							</div>
							<span class="view-label">{{ view.label }}</span>
						</el-option>
					</el-select>
				</el-tooltip>

				<el-button-group style="margin-left: 10px; color: #1f2d3d;">
					<el-button><i class="fa fa-undo" aria-hidden="true"></i></el-button>
					<el-button><i class="fa fa-repeat" aria-hidden="true"></i></el-button>
				</el-button-group>

				<el-button style="float: right">Save</el-button>
			</footer>
		</div>
		<nav class="editor-nav editor-sidebar">
			<page-sidebar></page-sidebar>
		</nav>
		<aside class="editor-component-list editor-sidebar">
			<block-sidebar></block-sidebar>
		</aside>
	</div>
</div>
</template>

<script>
	import PageSidebar from './PageSidebar.vue';
	import BlockSidebar from './BlockSidebar.vue';

	export default {
		name: 'editor',

		components: {
			PageSidebar,
			BlockSidebar
		},

		data() {
			return {
				views: {
					'desktop': {
						label: 'Desktop',
						width: '100%',
						height: '100vh'
					},
					'tablet': {
						label: 'Tablet',
						width: '768px',
						height: '1024px'
					},
					'mobile': {
						label: 'Mobile',
						width: '320px',
						height: '568px'
					}
				},
				currentView: 'desktop'
			};
		},

		computed: {
			getUrl() {
				return `${window.Laravel.base}/preview`;
			},

			dimensions() {
				return {
					width: this.views[this.currentView].width,
					height: this.views[this.currentView].height
				};
			}
		}
	}
</script>