<template>
	<div style="
		height: 100%;
		background-color: #f5f7fa;
	">
		<block-list></block-list>
		<block-options class="block-options" :class="{'options-visible' : mode === 'edit'}"></block-options>
	</div>
</template>

<script>
import BlockOptions from './BlockOptions';
import BlockList from './BlockList';
import { mapState } from 'vuex';

export default {
	name: 'BlockSidebar',

	components: {
		BlockOptions,
		BlockList
	},

	computed: {
		...mapState({
			currentBlockDefinition: state => state.definition.currentBlockDefinition
		}),

		mode() {
			return this.currentBlockDefinition ? 'edit' : 'list';
		}
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