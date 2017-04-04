<style lang="scss" scoped>
.block-options {
	transition: transform .2s ease-in-out;
	transform: translateX(100%);
	position: absolute;
	top: 0;
	background-color: #eef1f6;
	height: calc(100vh - 50px);
	width: 100%;
	border-left: 1px solid #bcc8dc;
}
.options-visible {
	transform: translateX(-1px);
}
</style>

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
			editBlock: function(blockId) {
				this.mode = 'edit';
			},

			exitEditBlock: function() {
				this.mode = 'list';
			}
		}
	}
</script>