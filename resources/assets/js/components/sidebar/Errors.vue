<template>
	<div class="sidebar__errors" :class="flash === 'errors' ? 'sidebar--flash' : ''">
		<back-bar :title="title" />

		<ul class="validation-errors">
			<li v-for="(block, blockId) in messages" class="block-errors">
				<el-alert
					:closable="false"
					type="error"
					class="error-alert"
					title=""
				>
					<i class="el-icon-warning"></i>
					<span class="el-alert__title is-bold">{{ block.label }}</span>
					<ol class="error-list">
						<li
							v-for="error in block.errors"
							class="error-list__item"
						>
							<el-button
								@click="scrollToErrors(blockId, error.field)"
								type="text"
								size="mini"
							>
								{{ error.label | removeBrackets }}
							</el-button>
						</li>
					</ol>
				</el-alert>
			</li>
		</ul>
	</div>
</template>

<script>
import { mapMutations } from 'vuex';
import BackBar from 'components/BackBar';

/* global document, setTimeout */

export default {
	name: 'errors',

	components: {
		BackBar
	},

	filters: {
		removeBrackets(str) {
			return str.replace(/\(.*\)/g, '');
		}
	},

	props: ['title'],

	computed: {
		errors() {
			return this.$store.state.errors.blocks;
		},

		regions() {
			return this.$store.state.page.pageData.blocks;
		},

		flash() {
			return this.$store.state.menu.flash;
		},

		messages() {
			return this.$store.getters.getAllBlockErrors;
		}
	},

	methods: {

		...mapMutations([
			'updateMenuActive'
		]),

		scrollToErrors(blockId, fieldPath) {
			this.$store.dispatch('changeBlock', this.errors[blockId].blockInfo);

			this.$nextTick(() =>
				this.$bus.$emit('error-sidebar:scroll-to-error', {
					blockId,
					fieldPath
				})
			);
		}
	}

};
</script>
