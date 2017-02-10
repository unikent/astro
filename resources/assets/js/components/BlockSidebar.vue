<template>
	<div>
		<h2>Sidey mc Bar</h2>

		<block-list v-if="mode == 'list'"></block-list>
		<block-options v-if="mode == 'options'"></block-options>
	</div>
</template>

<script>
	import BlockOptions from './BlockOptions.vue';
	import BlockList from './BlockList.vue';

	export default {
		name: 'BlockSidebar',

		components: {
			BlockOptions,
			BlockList
		},
		data() {
			return {
				mode: 'list'
			}
		},
		props: ['currentBlock'],
		methods: {
			editBlock: function(blockId){
				this.mode = 'edit';
				this.currentBlock = blockId;
			},
			exitEditBlock: function(){
				this.mode = 'list';
			}
		}
	}
</script>

<style lang="scss">

</style>