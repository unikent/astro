<template>
	<el-tooltip v-if="canUser('page.edit')" class="item" effect="dark" content="Switch edit mode" placement="top">
		<el-select
			placeholder="view"
			v-model="view"
			class="switch-view"
			:disabled="pageHasLayoutErrors"
		>
			<el-option v-for="(view, key) in views" :label="view.label" :value="key" :key="view.label">
				<div class="view-icon">
					<icon :name="view.icon" aria-hidden="true" width="20" height="20" />
				</div>
				<span class="view-label">{{ view.label }}</span>
			</el-option>
		</el-select>
	</el-tooltip>
</template>


<script>
import { mapState, mapMutations, mapGetters } from 'vuex';

import Icon from 'components/Icon';

export default {
	name: 'toolbar',

	components: {
		Icon
	},

	data() {
		return {
			fullscreenLoading: false
		}
	},

	computed: {
		...mapState([
			'currentView'
		]),

		...mapState({
			layoutErrors: state => state.page.layoutErrors
		}),

		...mapGetters([
			'canUser'
		]),

		view: {
			get() {
				return this.currentView;
			},
			set(value) {
				this.changeView(value);
			}
		},

		pageHasLayoutErrors() {
			return this.layoutErrors.length !== 0;
		}
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

	methods: {
		...mapMutations([
			'changeView'
		])
	}

};
</script>
