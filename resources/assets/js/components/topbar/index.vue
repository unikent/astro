<template>
	<top-bar>
		<div slot="title" v-if="pageTitle" class="top-bar__page-title">
			<div class="top-bar__title">
				{{ pageTitle }}
				<el-tag v-if="status.type" :type="status.type">{{ status.title }}</el-tag>
			</div>
			<span class="top-bar__url">
				<span v-if="publishStatus === 'draft' && !pageHasLayoutErrors">
					<a :href="draftPreviewURL" target="_blank">{{ renderedURL }}</a>
					<icon name="newwindow" aria-hidden="true" :width="12" :height="12" class="ico" />
				</span>
				<span v-else-if="publishStatus === 'published'">
					<a :href="publishedPreviewURL" target="_blank">{{ renderedURL }}</a>
					<icon name="newwindow" aria-hidden="true" :width="12" :height="12" class="ico" />
				</span>
				<span v-else>{{ renderedURL }}</span>
			</span>
		</div>
		<toolbar slot="tools" />
	</top-bar>
</template>

<script>
	import { mapState, mapGetters } from 'vuex';

	import TopBar from 'components/topbar/TopBar';
	import Icon from 'components/Icon';
	import Toolbar from 'components/topbar/Toolbar';

	export default {

		name: 'top-bar-wrapper',

		components: {
			TopBar,
			Icon,
			Toolbar
		},

		computed: {

			...mapState({
				layoutErrors: state => state.page.layoutErrors
			}),

			...mapGetters([
				'pageTitle',
				'publishStatus',
				'pagePath',
				'sitePath',
				'siteDomain',
				'publishedPreviewURL',
				'draftPreviewURL'
			]),

			renderedURL() {
				return this.siteDomain + this.sitePath + this.pagePath;
			},

			status() {
				switch(this.publishStatus) {
					case 'new':
						return {
							type: 'primary',
							title: 'Unpublished'
						};

					case 'draft':
						return {
							type: 'warning',
							title: 'Draft amendments'
						};

					case 'published':
						return {
							type: 'success',
							title: 'Published'
						};

					default:
						return {};

				}
			},

			pageHasLayoutErrors() {
				return this.layoutErrors.length !== 0;
			}
		}

	};
</script>