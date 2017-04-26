<template>
	<div>
		<block-list></block-list>
		<block-options class="block-options" :class="{'options-visible' : mode === 'edit'}"></block-options>
	</div>
</template>

<script>
import BlockOptions from './BlockOptions.vue';
import BlockList from './BlockList.vue';
import { mapState } from 'vuex';

export default {
	name: 'BlockSidebar',

	components: {
		BlockOptions,
		BlockList
	},

	computed: {
		mode() {
			return this.blockDef ? 'edit' : 'list';
		},
		...mapState([
			'blockDef'
		])
	},

	methods: {
		editBlock() {
			this.mode = 'edit';
		},

		exitEditBlock() {
			this.mode = 'list';
		}
	}
};
</script>